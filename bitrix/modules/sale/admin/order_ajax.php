<?
namespace Bitrix\Sale\AdminPage;

/**
 * Bitrix Framework
 * @global \CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web;
use Bitrix\Sale;
use Bitrix\Sale\Result;
use Bitrix\Sale\Provider;
use Bitrix\Sale\Helpers\Admin;
use Bitrix\Main\SystemException;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Sale\UserMessageException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\Services\Company;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Cashbox\Internals\CashboxCheckTable;
use Bitrix\Currency\CurrencyManager;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER;
$arResult = array();
$result = new \Bitrix\Main\Entity\Result();
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(!isset($_REQUEST["action"]))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "REQUEST[action] not defined!"));
}
elseif($saleModulePermissions == "D" || !check_bitrix_sessid())
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED2")));
	$result->setData(array("SYSTEM_ERROR" => "Access denied!"));
}
elseif(!\Bitrix\Main\Loader::includeModule('sale'))
{
	$result->addError(new EntityError(Loc::getMessage("SALE_OA_ERROR_HAPPENED")));
	$result->setData(array("SYSTEM_ERROR" => "Error! Can't include module \"Sale\"!"));
}
else
{
	if($result->isSuccess())
	{
		$processor = new AjaxProcessor($USER->GetID(), $_REQUEST);
		$result = $processor->processRequest();
	}
}

if($result->isSuccess())
{
	$arResult["RESULT"] = "OK";

	if ($result->hasWarnings())
	{
		$arResult["WARNING"] = implode("\n", $result->getWarningMessages());
		$arResult["WARNINGS"] = array();

		foreach($result->getWarningMessages() as $warning)
		{
			$arResult["WARNINGS"][] = $warning;
		}
	}
}
else
{
	$arResult["RESULT"] = "ERROR";
	$arResult["ERROR"] = implode("\n", $result->getErrorMessages());
	$arResult["ERRORS"] = array();

	foreach($result->getErrorMessages() as $error)
		$arResult["ERRORS"][] = $error;
}

$data = $result->getData();
if(is_array($data))
	$arResult = array_merge($arResult, $data);
unset($data);

$arResult = AjaxProcessor::convertEncodingArray($arResult, SITE_CHARSET, 'UTF-8');

header('Content-Type: application/json');

echo json_encode($arResult);
\CMain::FinalActions();
die();

/**
 * Class AjaxProcessor
 * @package Bitrix\Sale\AdminPage
 * Class helper for processing ajax requests
 */
class AjaxProcessor
{
	protected $userId;
	/** @var \Bitrix\Sale\Result $result*/
	protected $result;
	protected $request;
	/** @var \Bitrix\Sale\Order $order  */
	protected $order = null;
	protected $formDataChanged = false;

	public function __construct($userId, array $request)
	{
		$this->userId = $userId;
		$this->result = new Result();
		$this->request = $request;
	}

	/**
	 * @return Result
	 * @throws SystemException
	 */
	public function processRequest()
	{
		if(!isset($this->request['action']))
			throw new SystemException("Undefined \"action\"");

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/helpers/admin/orderedit.php");

		global $APPLICATION;

		if(strtolower(SITE_CHARSET) != 'utf-8')
			$this->request = $APPLICATION->ConvertCharsetArray($this->request, 'utf-8', SITE_CHARSET);

		try
		{
			call_user_func(
				array($this, $this->request['action']."Action")
			);

			/* Caution!
			 * You must update $this->request by fresh data,
			 * or you will refresh and receive old data!
			 */
			if(
				isset($this->request["refreshOrderData"])
				&& $this->request["refreshOrderData"] == "Y"
				&& $this->request['action'] != "refreshOrderData"
			)
				$this->refreshOrderDataAction();
		}
		catch(UserMessageException $e)
		{
			$this->addResultError($e->getMessage());
		}

		return $this->result;
	}

	/**
	 * @param $message
	 */
	public function addResultError($message)
	{
		$this->result->addError(new EntityError($message));
	}

	/**
	 * @param $message
	 */
	public function addResultWarning($message)
	{
		$this->result->addWarning(new Sale\ResultWarning($message));
	}

	protected function addResultData($dataKey, $data)
	{
		if(strlen($dataKey) <= 0)
			$this->result->addData($data);
		else
			$this->result->addData(array($dataKey => $data));
	}

	/* * * * * * requests actions handlers * * * * * * * * */

	protected function getProductIdBySkuPropsAction()
	{
		if(!$this->request["skuProps"] || !is_array($this->request["skuProps"])) throw new ArgumentNullException("skuProps");
		if(!$this->request["productId"] || intval($this->request["productId"]) <= 0) throw new ArgumentNullException("productId");
		if(!$this->request["iBlockId"] || intval($this->request["iBlockId"]) <= 0) throw new ArgumentNullException("iBlockId");
		if(!$this->request["skuOrder"] || !is_array($this->request["skuOrder"])) throw new ArgumentNullException("skuOrder");
		if(!$this->request["changedSkuId"] || intval($this->request["changedSkuId"]) <= 0) throw new ArgumentNullException("changedSkuId");

		$offerId = Admin\SkuProps::getProductId(
			$this->request["skuProps"],
			$this->request["productId"],
			$this->request["skuOrder"],
			$this->request["changedSkuId"]
		);

		$this->addResultData("OFFER_ID", $offerId);
	}

	protected function addProductToBasketAction()
	{
		global $APPLICATION, $USER;

		if(!$this->request["formData"]) throw new ArgumentNullException("formatData");
		if(!$this->request["quantity"]) throw new ArgumentNullException("quantity");
		if(!$this->request["productId"]) throw new ArgumentNullException("productId");


		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P' && (isset($this->request["orderId"]) && $this->request["orderId"] > 0))
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$resOrder = Sale\Internals\OrderTable::getList(array(
															   'filter' => array(
																   '=ID' => intval($this->request["orderId"]),
															   ),
															   'select' => array(
																   'RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'
															   )
														   ));
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesView = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowView = in_array($orderData['STATUS_ID'], $allowedStatusesView);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowView)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$productId = isset($this->request['productId']) ? intval($this->request['productId']) : 0;
		$quantity = isset($this->request['quantity']) ? floatval($this->request['quantity']) : 1;
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();
		$customPrice = isset($this->request['customPrice']) ? $this->request['customPrice'] : false;
		$siteId = isset($this->request["formData"]["SITE_ID"]) ? $this->request["formData"]["SITE_ID"] : SITE_ID;
		$userId = intval($this->request["formData"]["USER_ID"]);

		$alreadyInBasketCode = "";
		$productParams = array();

		if(isset($this->request["formData"]["PRODUCT"]) && is_array($this->request["formData"]["PRODUCT"]))
		{
			foreach($this->request["formData"]["PRODUCT"] as $basketCode => &$params)
			{
				if(!isset($params["MODULE"]) || $params["MODULE"] != "catalog")
					continue;

				if(!isset($params["OFFER_ID"]) || $params["OFFER_ID"] != $productId)
					continue;

				$params["QUANTITY"] += $quantity;
				$this->request["ADD_QUANTITY_INSTEAD_ONLY"] = "Y";
				$alreadyInBasketCode = $basketCode;
				$productParams = $params;
				break;
			}
		}

		if(empty($productParams))
		{
			$productParams = Admin\Blocks\OrderBasket::getProductsData(
				array($productId),
				$siteId,
				$columns,
				$userId
			);

			$productParams[$productId]["QUANTITY"] = $quantity;
			$providerData = \Bitrix\Sale\Helpers\Admin\Product::getProviderData($productParams, $siteId, $userId);
			$productParams = $productParams[$productId];

			if(!empty($providerData))
			{
				$productParams = array_merge($productParams, current($providerData));
				$productParams["PROVIDER_DATA"] = serialize(current($providerData));
			}

			if($customPrice !== false)
			{
				$productParams["CUSTOM_PRICE"] = "Y";
				$productParams["PRICE"] = $customPrice;
			}
		}

		if(
			isset($this->request["replaceBasketCode"])
			&& strlen($this->request["replaceBasketCode"]) > 0
			&& isset($this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]])
		)
		{
			$this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]] = $productParams;
			$this->request["formData"]["PRODUCT"][$this->request["replaceBasketCode"]]["REPLACED"] = "Y";

			if(strlen($alreadyInBasketCode) > 0)
			{
				unset($this->request["formData"]["PRODUCT"][$alreadyInBasketCode]);
				$this->request["formData"]["ALREADY_IN_BASKET_CODE"] = $alreadyInBasketCode;
			}
		}
		elseif(strlen($alreadyInBasketCode) <= 0)
		{
			$this->request["formData"]["PRODUCT"]["new"] = $productParams;
		}

		$this->formDataChanged = true;
	}

	protected function cancelOrderAction()
	{
		global $APPLICATION, $USER;
		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$canceled = isset($this->request['canceled']) ? $this->request['canceled'] : "N";
		$comment = isset($this->request['comment']) ? trim($this->request['comment']) : "";
		$errors = array();

		if(!\CSaleOrder::CanUserCancelOrder($orderId, $USER->GetUserGroupArray(), $this->userId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER'));

		/** @var Sale\Order $saleOrder*/
		if(!$saleOrder = Sale\Order::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($saleOrder->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$state = $saleOrder->getField("CANCELED");

		if($state != $canceled)
			throw new UserMessageException(
				$state == "Y" ? Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER_ALREADY') : Loc::getMessage('SALE_OA_ERROR_CANCEL_ORDER_NOT_YET')
			);

		/** @var \Bitrix\Sale\Result $res */
		$res = $saleOrder->setField("CANCELED", $canceled == "Y" ? "N" : "Y");

		if(!$res->isSuccess())
			$errors = array_merge($errors, $res->getErrorMessages());

		$saleOrder->setField("REASON_CANCELED", $canceled == "N" ? $comment : "");

		$res = $saleOrder->save();
		if(!$res->isSuccess())
		{
			$errors = array_merge($errors, $res->getErrorMessages());
		}

		$canceled = $saleOrder->getField("CANCELED");
		$this->addResultData("CANCELED", $canceled);

		if($canceled == "Y")
		{
			$userInfo = Admin\Blocks\OrderStatus::getUserInfo($saleOrder->getField("EMP_CANCELED_ID"));
			$this->addResultData("DATE_CANCELED", $saleOrder->getField("DATE_CANCELED")->toString());
			$this->addResultData("EMP_CANCELED_ID", $saleOrder->getField("EMP_CANCELED_ID"));
			$this->addResultData("EMP_CANCELED_NAME", $userInfo["NAME"]." (".$userInfo["LOGIN"].")");
		}

		if (!empty($errors))
		{
			throw new UserMessageException(implode("<br>\n", $errors));
		}
	}

	protected function saveCommentsAction()
	{
		global $APPLICATION, $USER;
		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new SystemException("Wrong order id!");

		if(!isset($this->request['comments']))
			throw new SystemException("Can't find the comments content!");

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = Sale\Internals\OrderTable::getList(array(
															   'filter' => array(
																   '=ID' => intval($this->request["orderId"]),
															   ),
															   'select' => array(
																   'RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'
															   )
														   ));
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($orderData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$res = Sale\Internals\OrderTable::update(
			$this->request['orderId'],
			array(
				"COMMENTS" => $this->request['comments'],
				"DATE_UPDATE" => new DateTime()
		));

		if(!$res->isSuccess())
			$this->addResultError(join("\n", $res->getErrorMessages()));

		$CBXSanitizer = new \CBXSanitizer;
		$CBXSanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_MIDDLE);
		$this->addResultData("COMMENTS", $CBXSanitizer->SanitizeHtml($this->request['comments']));
	}

	protected function saveStatusAction()
	{
		global $USER, $APPLICATION;
		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new SystemException("Wrong order id!");

		if(!isset($this->request['statusId']) || strlen($this->request['statusId']) <= 0)
			throw new SystemException("Wrong status id!");


		/** @var Sale\Order $order */
		$order = Sale\Order::load($this->request['orderId']);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": \"".$this->request['orderId']."\"");

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$statusesList = \Bitrix\Sale\OrderStatus::getAllowedUserStatuses(
			$this->userId,
			$order->getField('STATUS_ID')
		);

		if(array_key_exists($this->request['statusId'], $statusesList))
		{
			$res = $order->setField("STATUS_ID", $this->request['statusId']);

			if(!$res->isSuccess())
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));

			$res = $order->save();

			if(!$res->isSuccess())
				throw new UserMessageException(implode("<br>\n", $res->getErrorMessages()));
		}
	}

	protected function getOrderFieldsAction()
	{
		if(!isset($this->request['demandFields']) || !array($this->request['demandFields']) || empty($this->request['demandFields']))
			throw new SystemException("Demand fields is empty!");

		$this->addResultData(
			"RESULT_FIELDS",
			$this->getDemandedFields(
				$this->request['demandFields'],
				$this->request['givenFields']
			)
		);
	}

	protected function refreshOrderDataAction()
	{
		global $APPLICATION, $USER;
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$additional = isset($this->request["additional"]) ? $this->request["additional"] : array();

		//delete product from basket
		if(!empty($additional["operation"]) && $additional["operation"] == "PRODUCT_DELETE" && !empty($additional["basketCode"]))
		{
			if(!empty($formData['PRODUCT'][$additional["basketCode"]]))
				unset($formData['PRODUCT'][$additional["basketCode"]]);
		}

		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = null;

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = null;
		$opResults = new Result();

		//Use or not data from form and don't refresh data from provider
		Admin\OrderEdit::$isTrustProductFormData = (!empty($additional["operation"]) && $additional["operation"] == "DATA_ACTUALIZE") ? false : true;
		$order = $this->getOrder($formData, $opResults);
		$isStartField = $order->isStartField();

		if($order->getId() > 0)
		{
			$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

			if ($saleModulePermissions == 'P')
			{
				$isUserResponsible = false;
				$isAllowCompany = false;

				$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

				if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
				{
					throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
				}
			}

			$order = Admin\OrderEdit::editOrderByFormData($formData, $order, $this->userId, false, array(), $opResults);

			if(!$order)
			{
				if(!$opResults->isSuccess())
				{
					$this->addFilteredErrors($opResults);
					return;
				}
			}
		}

		if($order->getId() <= 0)
		{
			if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
			{
				$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
				$res->getErrorMessages();
				$data = $res->getData();
				$shipment = array_shift($data['SHIPMENT']);
				if (!$shipment->isCustomPrice())
				{
					$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);
					if ($calcResult->isSuccess())
						$shipment->setField('BASE_PRICE_DELIVERY', $calcResult->getPrice());
				}
			}

			if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
			{
				$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT'], true);
				$res->getErrorMessages();
				$data = $res->getData();
				$payment = array_shift($data['PAYMENT']);
			}
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $order->hasMeaningfulField();

			/** @var Result $r */
			$r = $order->doFinalAction($hasMeaningfulFields);
		}

		$result['PAYABLE'] = $order->getPrice() - $order->getSumPaid();
		$result["BASE_PRICE"] = Admin\Blocks\OrderBasket::getBasePrice($order);

		$data = $this->result->getData();
		if ($shipment)
		{
			$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);
			if ($calcResult->isSuccess())
			{
				if ($shipment->isCustomPrice())
					$result["CALCULATED_PRICE"] = $calcResult->getPrice();
				else
					$shipment->setField("BASE_PRICE_DELIVERY", $calcResult->getPrice());
			}
			elseif (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
			{
				$result['DELIVERY_ERROR'] = implode("\n", $calcResult->getErrorMessages());
			}

			if (!isset($data['SHIPMENT_DATA']['DELIVERY_SERVICE_LIST']))
			{
				$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList();
				$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
				$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);
				if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
				{
					foreach ($deliveryService as $delivery)
					{
						if ($shipment->getDeliveryId() == $delivery['ID'] && $delivery['RESTRICTED'] != Sale\Services\PaySystem\Restrictions\Manager::SEVERITY_NONE)
							$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					}
				}
			}
			if (!isset($data['SHIPMENT_DATA']['PROFILES']))
			{
				if ($shipment->getDeliveryId())
				{
					$service = Sale\Delivery\Services\Manager::getObjectById($shipment->getDeliveryId());

					if($service)
					{
						$parentService = $service->getParentService();
						if ($parentService && $parentService->canHasProfiles())
						{
							$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($parentService->getId());
							$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
							$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles);
							if (!isset($data['SHIPMENT_DATA']['DELIVERY_ERROR']))
							{
								foreach ($profiles as $profile)
								{
									if ($shipment->getDeliveryId() == $profile['ID'] && $profile['RESTRICTED'] == Sale\Delivery\Restrictions\Manager::SEVERITY_SOFT)
										$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
								}
							}
						}
					}
				}
			}

			if (!isset($data['SHIPMENT_DATA']['EXTRA_SERVICES']))
			{
				$extraServiceManager = new Sale\Delivery\ExtraServices\Manager($shipment->getDeliveryId(), $order->getCurrency());
				$deliveryExtraService = $shipment->getExtraServices();

				if ($deliveryExtraService)
					$extraServiceManager->setValues($deliveryExtraService);

				$extraService = $extraServiceManager->getItems();

				if ($extraService)
					$result["EXTRA_SERVICES"] = Admin\Blocks\OrderShipment::getExtraServiceEditControl($extraService, 1, false, $shipment);
			}

			$companies = Company\Manager::getListWithRestrictions($shipment, Company\Restrictions\Manager::MODE_MANAGER);
			$result['SHIPMENT_COMPANY_ID'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($companies, $shipment->getField('COMPANY_ID'));
		}

		if ($payment)
		{
			$paySystemList = Admin\Blocks\OrderPayment::getPaySystemList($payment);

			if (isset($paySystemList[$payment->getPaymentSystemId()]['RESTRICTED']))
				$result['PAYSYSTEM_ERROR'] = Loc::getMessage('SALE_OA_ERROR_PAYSYSTEM_SERVICE');

			$result['PAY_SYSTEM_LIST'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($paySystemList, '', false);
			$result['PRICE_COD'] = $this->updatePriceCodAction($payment);

			$companies = Company\Manager::getListWithRestrictions($payment, Company\Restrictions\Manager::MODE_MANAGER);
			$result['PAYMENT_COMPANY_ID'] = Admin\OrderEdit::makeSelectHtmlBodyWithRestricted($companies, $payment->getField('COMPANY_ID'));
		}
		$orderBasket = new Admin\Blocks\OrderBasket($order,"", $this->request["formData"]["BASKET_PREFIX"]);
		$basketPrepareParams = array();

		if((
			!empty($additional["operation"]) && $additional["operation"] == "PRODUCT_ADD")
			|| ($this->request["action"] == "addProductToBasket"
				&& (!isset($this->request["ADD_QUANTITY_INSTEAD_ONLY"])
					|| $this->request["ADD_QUANTITY_INSTEAD_ONLY"] != "Y"
				)
			)
		)
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = false;
			$basketPrepareParams["ADDED_PRODUCTS"] = array($this->request["productId"]);
		}
		else
		{
			$basketPrepareParams["SKIP_SKU_INFO"] = true;
			$basketPrepareParams["ADDED_PRODUCTS"] = array();
		}

		$result["BASKET"] = $orderBasket->prepareData($basketPrepareParams);
		$result["BASKET"]["LIGHT"] = "Y";
		// collect info about changed fields
		if($basketPrepareParams["SKIP_SKU_INFO"] && !empty($formData["PRODUCT"]) && is_array($formData["PRODUCT"]))
		{
			//prices
			$result["BASKET"]["PRICES_UPDATED"] = array();
			$errors = array();
			$PRECISE = 0.005;

			foreach($formData["PRODUCT"] as $basketCode => $itemParams)
			{
				if($basketCode == "new")
					continue;

				if(!isset($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) || !isset($itemParams["PRICE"]))
				{
					$errors[] = "Product price with basket code \"".$basketCode."\" not found.";
					continue;
				}

				if(abs(floatval($result["BASKET"]["ITEMS"][$basketCode]["PRICE"]) - floatval($itemParams["PRICE"])) >= $PRECISE)
					$result["BASKET"]["PRICES_UPDATED"][$basketCode] = $result["BASKET"]["ITEMS"][$basketCode]["PRICE"];
			}

			if(!empty($errors))
				$this->addResultData("ERROR_PRICE_COMPARING", $errors);

		}

		$resData = $opResults->getData();

		if(!empty($resData["NEW_ITEM_BASKET_CODE"]))
			$result["BASKET"]["NEW_ITEM_BASKET_CODE"] = $resData["NEW_ITEM_BASKET_CODE"];

		$result['RELATED_PROPS'] = Admin\Blocks\OrderBuyer::getRelPropData($order);
		if ($result['RELATED_PROPS'])
		{
			$profileId = isset($formData["BUYER_PROFILE_ID"]) ? intval($formData["BUYER_PROFILE_ID"]) : 0;
			$profile = Admin\Blocks\OrderBuyer::getProfileParams($order->getUserId(), $profileId);
			if ($result['RELATED_PROPS']['properties'])
			{
				foreach ($result['RELATED_PROPS']['properties'] as $key => $property)
				{
					if (!isset($formData['PROPERTIES'][$property['ID']]))
					{
						if ($property['TYPE'] === 'ENUM')
						{
							$result['RELATED_PROPS']['properties'][$key]['VALUE'] = explode(',', $profile[$property['ID']]);
						}
						else
						{
							$result['RELATED_PROPS']['properties'][$key]['VALUE'] = array($profile[$property['ID']]);
						}
					}
				}
			}
		}

		$result["DISCOUNTS_LIST"] = Admin\OrderEdit::getOrderedDiscounts($order, false);

		if ($order->getBasket())
			$result['BASE_PRICE_DELIVERY'] = $result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['BASE_PRICE'];
		else
			$result['BASE_PRICE_DELIVERY'] = $order->getDeliveryPrice();

		$result['BASE_PRICE_DELIVERY'] = Sale\PriceMaths::roundByFormatCurrency($result['BASE_PRICE_DELIVERY'], $order->getCurrency());
		$result['DELIVERY_PRICE_DISCOUNT'] = Sale\PriceMaths::roundByFormatCurrency($result["DISCOUNTS_LIST"]['PRICES']['DELIVERY']['PRICE'], $order->getCurrency());
		$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order, false);
		$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);
		$result["DELIVERY_DISCOUNT"] = $result["TOTAL_PRICES"]["DELIVERY_DISCOUNT"];

		$result = array_merge($result, $order->getFieldValues());

		if(!isset($result["PRICE"]))
			$result["PRICE"] = 0;

		/* DEMANDED */
		if(isset($additional["demandFields"]) && is_array($additional["demandFields"]))
		{
			if(isset($additional["givenFields"]) && is_array($additional["givenFields"]))
				$result=array_merge($result, $additional["givenFields"]);

			$demanded = $this->getDemandedFields($additional["demandFields"], $result, $order);
			$result = array_merge($result, $demanded);
		}

		$this->addFilteredErrors($opResults);
		$this->addResultData("ORDER_DATA", $result);
	}

	/* We don't show all errors during forming order via ajax requests */
	protected function addFilteredErrors(Result $opResults)
	{
		if(!$opResults->isSuccess())
		{
			foreach($opResults->getErrors() as $error)
			{
				if($error->getCode() == "CATALOG_QUANTITY_NOT_ENOGH"
					|| $error->getCode() == "SALE_ORDER_SYSTEM_SHIPMENT_LESS_QUANTITY"
					|| $error->getCode() == "CATALOG_NO_QUANTITY_PRODUCT"
					|| $error->getCode() == "SALE_SHIPMENT_SYSTEM_QUANTITY_ERROR"
					|| $error->getCode() == "SALE_BASKET_AVAILABLE_QUANTITY"
					|| $error->getCode() == "SALE_BASKET_ITEM_WRONG_AVAILABLE_QUANTITY"
					|| $error->getCode() == "SALE_BASKET_ITEM_REMOVE_IMPOSSIBLE_BECAUSE_SHIPPED"
				)
					$this->addResultError($error->getMessage());
			}
		}
	}

	protected function changeResponsibleUserAction()
	{
		if(!isset($this->request['userId']) || intval($this->request['userId']) <= 0)
			throw new ArgumentNullException("userId");

		$siteId = strlen($this->request['siteId']) > 0 ? $this->request['siteId'] : "";
		global $USER;

		$dateResponsible = new \Bitrix\Main\Type\DateTime();
		$this->addResultData("RESPONSIBLE", Admin\OrderEdit::getUserName($this->request['userId'], $siteId));
		$this->addResultData("EMP_RESPONSIBLE", Admin\OrderEdit::getUserName($USER->GetID(), $siteId));
		$this->addResultData("DATE_RESPONSIBLE", $dateResponsible->toString());
	}

	protected function updatePaymentStatusAction()
	{
		global $APPLICATION, $USER;

		if(!isset($this->request['orderId']) || intval($this->request['orderId']) <= 0)
			throw new ArgumentNullException("orderId");

		if(!isset($this->request['paymentId']) || intval($this->request['paymentId']) <= 0)
			throw new ArgumentNullException("paymentId");

		$fields = array();
		$orderStatusId = '';
		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($this->request['orderId']);

		/** @var \Bitrix\Sale\Payment $payment */
		$payment = $order->getPaymentCollection()->getItemById($this->request['paymentId']);
		$hasErrors = false;

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$payment->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		if ($this->request['method'] == 'save')
		{
			if ($payment->getField('IS_RETURN') == 'Y')
			{
				$res = $payment->setReturn('N');
				if (!$res->isSuccess())
				{
					$this->addResultError(join("\n", $res->getErrorMessages()));
					$hasErrors = true;
				}
				elseif ($res->hasWarnings())
				{
					$this->addResultWarning(join("\n", $res->getWarningMessages()));
					$hasErrors = true;
				}
			}
			else
			{
				$res = $payment->setPaid('Y');
				if (!$res->isSuccess())
				{
					$this->addResultError(join("\n", $res->getErrorMessages()));
					$hasErrors = true;
				}
				elseif ($res->hasWarnings())
				{
					$this->addResultWarning(join("\n", $res->getWarningMessages()));
				}
			}

			if (!$hasErrors)
			{
				foreach ($this->request['data'] as $key => $value)
				{
					$newKey = substr($key, 0, strripos($key, '_'));
					if (strpos($newKey, 'PAY_VOUCHER') !== false)
						$fields[$newKey] = $value;
					if ($newKey == 'ORDER_STATUS_ID')
						$orderStatusId = $value;
				}
				try
				{
					$fields['PAY_VOUCHER_DATE'] = new \Bitrix\Main\Type\Date($fields['PAY_VOUCHER_DATE']);
				}
				catch (ObjectException $exception)
				{
					$this->addResultError(Loc::getMessage('SALE_OA_ERROR_INCORRECT_DATE'));
					return;
				}

			}
		}
		else
		{
			foreach ($this->request['data'] as $key => $value)
			{
				$newKey = substr($key, 0, strripos($key, '_'));
				if (strpos($newKey, 'PAY_RETURN') !== false)
					$fields[$newKey] = $value;
			}

			if (isset($fields['PAY_RETURN_OPERATION_ID']))
			{
				/** @var Result $refResult */
				$refResult = $payment->setReturn($fields['PAY_RETURN_OPERATION_ID']);
				if (!$refResult->isSuccess())
				{
					$this->addResultError(join("\n", $refResult->getErrorMessages()));
					return;
				}
				elseif ($refResult->hasWarnings())
				{
					$this->addResultWarning(join("\n", $refResult->getWarningMessages()));
					return;
				}

				unset($fields['PAY_RETURN_OPERATION_ID']);
			}
			else
			{
				$res = $payment->setPaid('N');
				if (!$res->isSuccess())
				{
					$this->addResultError(join("\n", $res->getErrorMessages()));
				}
				elseif ($res->hasWarnings())
				{
					$this->addResultWarning(join("\n", $res->getWarningMessages()));
				}
			}
			try
			{
				$fields['PAY_RETURN_DATE'] = new Date($fields['PAY_RETURN_DATE']);
			}
			catch (ObjectException $exception)
			{
				$this->addResultError(Loc::getMessage('SALE_OA_ERROR_INCORRECT_DATE'));
				return;
			}
		}

		if (!$hasErrors)
		{
			$saveResult = $payment->setFields($fields);
			if ($saveResult->isSuccess())
			{
				if (!empty($orderStatusId))
				{
					if ($USER && $USER->isAuthorized())
						$statusesList = Sale\OrderStatus::getAllowedUserStatuses($USER->getID(), $order->getField('STATUS_ID'));
					else
						$statusesList = Sale\OrderStatus::getAllStatuses();

					if ($order->getField('STATUS_ID') != $orderStatusId && array_key_exists($orderStatusId, $statusesList))
					{
						/** @var Result $res */
						$res = $order->setField('STATUS_ID', $orderStatusId);
						if (!$res->isSuccess())
						{
							$this->addResultError(join("\n", $res->getErrorMessages()));
							return;
						}
					}
				}

				$result = $order->save();
				if ($result->isSuccess())
				{
					$preparedData = Admin\Blocks\OrderFinanceInfo::prepareData($order);
					$preparedData["PAYMENT_PAID_".$payment->getId()] = $payment->isPaid() ? "Y" : "N";

					$shipmentCollection = $order->getShipmentCollection();
					if ($shipmentCollection)
					{
						/** @var \Bitrix\Sale\Shipment $shipment */
						foreach ($shipmentCollection as $shipment)
						{
							if (!$shipment->isSystem())
							{
								$preparedData['DEDUCTED_'.$shipment->getId()] = $shipment->getField('DEDUCTED');
								$preparedData['ALLOW_DELIVERY_'.$shipment->getId()] = $shipment->getField('ALLOW_DELIVERY');

								$preparedStatusList = array();
								$statusList = Admin\Blocks\OrderShipmentStatus::getShipmentStatusList($shipment->getField('STATUS_ID'));
								foreach ($statusList as $id => $name)
								{
									if ($shipment->getField('STATUS_ID') === $id)
										continue;

									$preparedStatusList[] = array(
										'ID' => $id,
										'NAME' => htmlspecialcharsbx($name)
									);
								}

								$preparedData['SHIPMENT_STATUS_LIST_'.$shipment->getId()] = $preparedStatusList;
								$preparedData['SHIPMENT_STATUS_'.$shipment->getId()] = array('id' => $shipment->getField('STATUS_ID'), 'name' => htmlspecialcharsbx($statusList[$shipment->getField('STATUS_ID')]));
							}
						}
					}

					if ($result->hasWarnings())
					{
						$this->addResultWarning(join("\n", $result->getWarningMessages()));
					}

					$this->addResultData(
						"RESULT",
						$preparedData
					);

					$markerListHtml = Admin\Blocks\OrderMarker::getView($order->getId(), $payment->getId());

					if (!empty($markerListHtml))
					{
						$this->addResultData('MARKERS', $markerListHtml);
					}
				}
				else
				{
					$this->addResultError(join("\n", $result->getErrorMessages()));
					return;
				}
			}
			else
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}
		}
	}

	protected function deletePaymentAction()
	{
		global $USER, $APPLICATION;
		$orderId = $this->request['orderId'];
		$paymentId = $this->request['paymentId'];

		if ($orderId <= 0 || $paymentId <=0)
			throw new ArgumentNullException("paymentId or orderId");

		/** @var Sale\Order $order */
		$order = Sale\Order::load($orderId);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($paymentId);

		if (!$payment)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_PAYMENT').": ".$paymentId);

		$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
		if(!in_array($order->getField("STATUS_ID"), $allowedStatusesUpdate))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_DELETE_PAYMENT_PERMISSION').': '.$paymentId);
		}

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());


			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$payment->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$delResult = $payment->delete();

		if ($delResult->isSuccess())
		{
			$result = $order->save();
			if ($result->isSuccess())
				$this->addResultData("RESULT", "OK");
			else
				throw new UserMessageException(join("\n", $result->getErrorMessages()));
		}
		else
		{
			throw new UserMessageException(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function deleteShipmentAction()
	{
		global $USER, $APPLICATION;
		$orderId = $this->request['order_id'];
		$shipmentId = $this->request['shipment_id'];

		if ($orderId <= 0 || $shipmentId <= 0)
			throw new UserMessageException('Error');

		/** @var Sale\Order $order */
		$order = Sale\Order::load($orderId);

		if (!$order)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$shipmentCollection = $order->getShipmentCollection();
		$shipmentItem = $shipmentCollection->getItemById($shipmentId);

		if (!$shipmentItem)
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_SHIPMENT').': '.$shipmentId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());


			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$shipmentItem->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($shipmentItem->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($shipmentItem->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}
		
		$allowedStatusesDelete = Sale\DeliveryStatus::getStatusesUserCanDoOperations($USER->GetID(), array('delete'));
		if(!in_array($shipmentItem->getField("STATUS_ID"), $allowedStatusesDelete))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_DELETE_SHIPMENT_PERMISSION').': '.$shipmentId);
		}

		$delResult = $shipmentItem->delete();

		if ($delResult->isSuccess())
		{
			$saveResult = $order->save();
			if ($saveResult->isSuccess())
			{
				$result["DELIVERY_PRICE"] = $shipmentCollection->getBasePriceDelivery();
				$result["DELIVERY_PRICE_DISCOUNT"] = $shipmentCollection->getPriceDelivery();
				$result['PRICE'] = $order->getPrice();
				$result['PAYABLE'] = $result['PRICE'] - $order->getSumPaid();

				$orderBasket = new Admin\Blocks\OrderBasket($order);
				$result["TOTAL_PRICES"] = Admin\OrderEdit::getTotalPrices($order, $orderBasket, false);

				$this->addResultData("RESULT", $result);
			}
			elseif ($saveResult->hasWarnings())
			{
				$this->addResultWarning(join("\n", $saveResult->getWarningMessages()));
			}
			else
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}
		}
		else
		{
			$this->addResultError(join("\n", $delResult->getErrorMessages()));
		}
	}

	protected function saveBasketVisibleColumnsAction()
	{
		$columns = isset($this->request['columns']) ? $this->request['columns'] : array();
		$idPrefix = isset($this->request['idPrefix']) ? $this->request['idPrefix'] : "";

		if(\CUserOptions::SetOption($idPrefix."order_basket_table", "table_columns", array("columns" => implode(",", $columns))))
			$this->addResultData("RESULT", "OK");
		else
			$this->addResultError("Can't save columns!");
	}

	protected function updateShipmentStatusAction()
	{
		global $USER, $APPLICATION;
		$shipmentId = $this->request['shipmentId'];
		$orderId = $this->request['orderId'];
		$field = $this->request['field'];
		$index = $this->request['index'];
		$newStatus = $this->request['status'];

		/** @var \Bitrix\Sale\Order $order */
		$order = \Bitrix\Sale\Order::load($orderId);

		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $order->getShipmentCollection()->getItemById($shipmentId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
				$shipment->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($shipment->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($shipment->getField('STATUS_ID'), $allowedStatusesUpdate);


			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$setResult = $shipment->setField($field, $newStatus);

		if ($setResult->isSuccess())
		{
			if ($setResult->hasWarnings())
			{
				$this->addResultWarning(join("\n", $setResult->getWarningMessages()));
			}

			$saveResult = $order->save();
			if (!$saveResult->isSuccess())
			{
				$this->addResultError(join("\n", $saveResult->getErrorMessages()));
			}
			elseif ($saveResult->hasWarnings())
			{
				$this->addResultWarning(join("\n", $saveResult->getWarningMessages()));
			}
		}
		else
		{
			$serResultMessage = $setResult->getErrorMessages();
			if (!empty($serResultMessage))
				$this->addResultError(join("\n", $serResultMessage));
			else
				$this->addResultError(Loc::getMessage('SALE_OA_SHIPMENT_STATUS_ERROR'));
		}

		if($shipment)
		{
			$preparedStatusList = array();
			$statusList = Admin\Blocks\OrderShipmentStatus::getShipmentStatusList($shipment->getField('STATUS_ID'));
			foreach ($statusList as $id => $name)
			{
				if ($shipment->getField('STATUS_ID') === $id)
					continue;

				$preparedStatusList[] = array(
					'ID' => $id,
					'NAME' => htmlspecialcharsbx($name)
				);
			}

			$result = array(
				'DEDUCTED_'.$shipment->getId() => $shipment->getField('DEDUCTED'),
				'ALLOW_DELIVERY_'.$shipment->getId() => $shipment->getField('ALLOW_DELIVERY'),
				'SHIPMENT_STATUS_LIST_'.$shipment->getId() => $preparedStatusList,
				'SHIPMENT_STATUS_'.$shipment->getId() => array('id' => $shipment->getField('STATUS_ID'), 'name' => htmlspecialcharsbx($statusList[$shipment->getField('STATUS_ID')]))
			);

			$this->addResultData("RESULT", $result);

			$markerListHtml = Admin\Blocks\OrderMarker::getView($order->getId(), $shipment->getId());

			if (!empty($markerListHtml))
			{
				$this->addResultData('MARKERS', $markerListHtml);
			}
		}
	}

	protected function createNewPaymentAction()
	{
		global $APPLICATION, $USER;
		$formData = $this->request['formData'];
		$index = $this->request['index'];

		$order = $this->getOrder($formData);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}


		if(isset($formData['SHIPMENT']) && is_array($formData['SHIPMENT']))
		{
			$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
			$res->getErrorMessages();
		}

		if(isset($formData['PAYMENT']) && is_array($formData['PAYMENT']))
		{
			$res = Admin\Blocks\OrderPayment::updateData($order, $formData['PAYMENT']);
			$res->getErrorMessages();
		}

		$payment = $order->getPaymentCollection()->createItem();
		$this->addResultData("PAYMENT", Sale\Helpers\Admin\Blocks\OrderPayment::getEdit($payment, $index));
	}

	protected function getProductEditDialogHtmlAction()
	{
		$currency = isset($this->request['currency']) ? $this->request['currency'] : array();
		$objName = isset($this->request['objName']) ? $this->request['objName'] : "";
		$this->addResultData(
			'DIALOG_CONTENT',
			Admin\Blocks\OrderBasket::getProductEditDialogHtml(
				$currency,
				$objName
			)
		);
	}

	protected function changeDeliveryServiceAction()
	{
		global $APPLICATION, $USER;
		$result = array();
		$profiles = array();
		$index = $this->request['index'];
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$formData['ID'] = $formData['order_id'];
		$deliveryId = intval($formData['SHIPMENT'][$index]['DELIVERY_ID']);

		if ($deliveryId <= 0)
			return;

		Admin\OrderEdit::$isTrustProductFormData = true;
		$order = $this->getOrder($formData);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P' && (isset($this->request["orderId"]) && intval($this->request["orderId"]) > 0))
		{
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$resShipment = Sale\Internals\ShipmentTable::getList(array(
																	 'filter' => array(
																		 '=ORDER_ID' => intval($this->request["orderId"]),
																		 '=ID' => intval($formData['SHIPMENT'][$index]['SHIPMENT_ID']),
																	 ),
																	 'select' => array(
																		 'RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'
																	 )
																 ));
			if ($shipmentData = $resShipment->fetch())
			{
				if ($shipmentData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($shipmentData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}
			}

			$isAllowUpdate = in_array($shipmentData['STATUS_ID'], $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		/** @var  \Bitrix\Sale\Delivery\Services\Base $service */
		$service = Sale\Delivery\Services\Manager::getObjectById($deliveryId);
		if ($service && $service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::getDeliveryServiceProfiles($deliveryId);
			if (!isset($formData['SHIPMENT'][$index]['PROFILE']))
			{
				reset($profiles);
				$initProfile = current($profiles);
				$deliveryId = $initProfile['ID'];
				$formData['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
				$this->request["formData"]['SHIPMENT'][$index]['PROFILE'] = $initProfile['ID'];
			}
			else
			{
				$deliveryId = $formData['SHIPMENT'][$index]['PROFILE'];
			}
		}

		$res = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);
		$data = $res->getData();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = array_shift($data['SHIPMENT']);

		if ($service->canHasProfiles())
		{
			$profiles = Admin\Blocks\OrderShipment::checkProfilesRestriction($profiles, $shipment);
			$result["PROFILES"] = Admin\Blocks\OrderShipment::getProfileEditControl($profiles, $index, $shipment->getDeliveryId());

			foreach ($profiles as $profile)
			{
				if ($formData['SHIPMENT'][$index]['PROFILE'] == $profile['ID'] && $profile['RESTRICTED'] == Sale\Delivery\Restrictions\Manager::SEVERITY_SOFT)
				{
					$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
					break;
				}
			}
		}

		$deliveryService = Admin\Blocks\OrderShipment::getDeliveryServiceList($shipment);
		$deliveryServiceTree = Admin\Blocks\OrderShipment::makeDeliveryServiceTree($deliveryService);
		$result['DELIVERY_SERVICE_LIST'] = Admin\Blocks\OrderShipment::getTemplate($deliveryServiceTree);

		foreach ($deliveryService as $delivery)
		{
			if ($deliveryId == $delivery['ID'] && $delivery['RESTRICTED'] != Sale\Delivery\Restrictions\Manager::SEVERITY_NONE)
			{
				$result['DELIVERY_ERROR'] = Loc::getMessage('SALE_OA_ERROR_DELIVERY_SERVICE');
				break;
			}
		}

		$storeMap = Admin\Blocks\OrderShipment::getMap($shipment->getDeliveryId(), $index);
		if ($storeMap)
			$result['MAP'] = $storeMap;

		$extraServiceManager = new Sale\Delivery\ExtraServices\Manager($deliveryId);
		$extraServiceManager->setOperationCurrency($order->getCurrency());
		$deliveryExtraService = $shipment->getExtraServices();

		if ($deliveryExtraService)
			$extraServiceManager->setValues($deliveryExtraService);

		$extraService = $extraServiceManager->getItems();

		if ($extraService)
			$result["EXTRA_SERVICES"] = Admin\Blocks\OrderShipment::getExtraServiceEditControl($extraService, $index, false, $shipment);

		$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);

		if ($calcResult->isSuccess())
		{
			$result["CALCULATED_PRICE"] = $calcResult->getPrice();
			if ($shipment->getField('CUSTOM_PRICE_DELIVERY') != 'Y')
			{
				$shipment->setField('PRICE_DELIVERY', $calcResult->getPrice());
				$this->request['formData']['SHIPMENT'][$index]['PRICE_DELIVERY'] = $calcResult->getPrice();
			}
		}
		else
		{
			$result['DELIVERY_ERROR'] = implode("\n", $calcResult->getErrorMessages());
		}

		$this->addResultData("SHIPMENT_DATA", $result);

		$this->formDataChanged = true;
	}

	protected function getDefaultDeliveryPriceAction()
	{
		global $APPLICATION, $USER;
		$formData = isset($this->request["formData"]) ? $this->request["formData"] : array();
		$formData['ID'] = $formData['order_id'];

		$order = $this->getOrder($formData);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$allowedStatusesView = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowView = in_array($order->getField('STATUS_ID'), $allowedStatusesView);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowView)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$result = Admin\Blocks\OrderShipment::updateData($order, $formData['SHIPMENT']);

		$data = $result->getData();
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = array_shift($data['SHIPMENT']);
		$calcResult = Admin\Blocks\OrderShipment::calculateDeliveryPrice($shipment);

		if ($calcResult->isSuccess())
			$this->addResultData("RESULT", array("CALCULATED_PRICE" => $calcResult->getPrice()));
		else
			$this->addResultError(implode("\n", $result->getErrorMessages()));
	}

	protected function checkProductBarcodeAction()
	{
		if(!\Bitrix\Main\Loader::includeModule("catalog"))
			throw new UserMessageException("ERROR");
		$basketItem = null;
		$result = false;

		$barcode = $this->request['barcode'];
		$basketId = $this->request['basketId'];
		$orderId = $this->request['orderId'];
		$storeId = $this->request['storeId'];

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			$basket = $order->getBasket();
			if ($basket)
				$basketItem = $basket->getItemById($basketId);
		}

		if ($basketItem)
		{
			$params = array(
				'BARCODE' => $barcode,
				'STORE_ID' => $storeId
			);
			$result = Provider::checkProductBarcode($basketItem, $params);
		}

		if ($result)
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function deleteCouponAction()
	{
		global $APPLICATION, $USER;
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = Sale\Internals\OrderTable::getList(array(
				'filter' => array(
					'=ID' => intval($this->request["orderId"]),
				),
				'select' => array(
					'RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'
				)
			));
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($orderData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		if(Sale\DiscountCouponsManager::delete($this->request["coupon"]))
			$this->addResultData('RESULT', 'OK');
		else
			$this->addResultError('ERROR');
	}

	protected function addCouponsAction()
	{
		global $APPLICATION, $USER;
		if(!isset($this->request["userId"])) throw new ArgumentNullException("userId");
		if(!isset($this->request["coupon"])) throw new ArgumentNullException("coupon");
		if(!isset($this->request["orderId"])) throw new ArgumentNullException("orderId");

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = Sale\Internals\OrderTable::getList(array(
															   'filter' => array(
																   '=ID' => intval($this->request["orderId"]),
															   ),
															   'select' => array(
																   'RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'
															   )
														   ));
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$isAllowUpdate = in_array($orderData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		Admin\OrderEdit::initCouponsData($this->request["userId"], $this->request["orderId"]);

		if(strlen($this->request["coupon"]) > 0)
		{
			$coupons = explode(",", $this->request["coupon"]);

			if(is_array($coupons) && count($coupons) > 0)
				foreach($coupons as $coupon)
					if(strlen($coupon) > 0)
						Sale\DiscountCouponsManager::add($coupon);
		}

		$this->addResultData('RESULT', 'OK');
	}

	protected function getProductIdByBarcodeAction()
	{
		\Bitrix\Main\Loader::includeModule('catalog');

		$barcode = $this->request['barcode'];

		if(strlen($barcode) > 0)
		{
			$rsBarCode = \CCatalogStoreBarCode::getList(array(), array("BARCODE" => $barcode), false, false, array('PRODUCT_ID'));
			$arBarCode = $rsBarCode->Fetch();
		}

		$this->addResultData(
			'RESULT',
			array(
				"PRODUCT_ID" => isset($arBarCode["PRODUCT_ID"]) ? intval($arBarCode["PRODUCT_ID"]) : 0
			)
		);
	}

	/* * * * * * * accessory methods * * * * * * * */

	protected function getDemandedFields(array $demandedFields, array $incomingFields, \Bitrix\Sale\Order $order = null)
	{
		$result = array();
		$userId = isset($incomingFields["USER_ID"]) && intval($incomingFields["USER_ID"]) > 0 ? intval($incomingFields["USER_ID"])  : 0;
		$currency = isset($incomingFields["CURRENCY"]) ? trim($incomingFields["CURRENCY"]) : "";
		$personTypeId = isset($incomingFields['PERSON_TYPE_ID']) ? intval($incomingFields['PERSON_TYPE_ID']) : 0;
		$siteId = !empty($incomingFields["SITE_ID"]) ? trim($incomingFields["SITE_ID"])  : SITE_ID;
		$orderId = isset($incomingFields["ID"]) ? intval($incomingFields["ID"]) : 0;
		$buyerIdChanged = isset($incomingFields["BUYER_ID_CHANGED"]) && $incomingFields["BUYER_ID_CHANGED"] == 'Y' ? true : false;

		if($buyerIdChanged)
		{
			Admin\OrderEdit::$needUpdateNewProductPrice = true;
			Admin\OrderEdit::$isBuyerIdChanged = true;
		}

		if($order === null && intval($orderId) > 0)
			$order = \Bitrix\Sale\Order::load($orderId);

		foreach($demandedFields as $demandedField)
		{
			switch($demandedField)
			{
				case "BUYER_USER_NAME":

					$siteId = (bool)$order ? $order->getSiteId() : "";
					$result["BUYER_USER_NAME"] = intval($userId) > 0 ? Admin\OrderEdit::getUserName(intval($userId), $siteId) : "";
					break;

				case "PROPERTIES":

					if($userId > 0)
					{
						$profileId = isset($incomingFields["BUYER_PROFILE_ID"]) ? intval($incomingFields["BUYER_PROFILE_ID"]) : 0;
						$result["PROPERTIES"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getProfileParams($userId, $profileId);
					}
					else
					{
						$porder = Sale\Order::create($siteId);
						$porder->setPersonTypeId($personTypeId);
						$result["PROPERTIES"] = array();

						/** @val \Bitrix\Sale\PropertyValue $prop */
						foreach(\Bitrix\Sale\PropertyValue::loadForOrder($porder) as $prop)
						{
							$p = $prop->getProperty();
							$result["PROPERTIES"][$prop->getPropertyId()] = !empty($p["DEFAULT_VALUE"]) ? $p["DEFAULT_VALUE"] : "";
						}
					}
					break;

				case "BUYER_PROFILES_LIST":

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$result["BUYER_PROFILES_LIST"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getBuyerProfilesList($userId, $personTypeId);
					break;

				case "BUYER_PROFILES_DATA":

					$result["BUYER_PROFILES_DATA"] = \Bitrix\Sale\Helpers\Admin\Blocks\OrderBuyer::getUserProfiles($userId, $personTypeId);
					break;

				case "BUYER_BUDGET":
					$res = \CSaleUserAccount::getList(
						array(),
						array(
							'USER_ID' => $userId,
							'CURRENCY' => $currency,
							'LOCKED' => 'N'
						),
						false,
						false,
						array(
							'CURRENT_BUDGET'
						)
					);

					if($userAccount = $res->Fetch())
						$result["BUYER_BUDGET"] = $userAccount['CURRENT_BUDGET'];
					else
						$result["BUYER_BUDGET"] = 0;

					break;
				case "PROPERTIES_ARRAY":

					if(!$order)
						throw new \Bitrix\Main\SystemException("Can't init order");

					if(intval($personTypeId)<=0)
						throw new \Bitrix\Main\ArgumentNullException("personTypeId");

					$order->setPersonTypeId($personTypeId);

					$result["PROPERTIES_ARRAY"] = $order->loadPropertyCollection()->getArray();
					break;

				case "PRODUCT":
					$result["PRODUCT"] = array();
					break;

				case "COUPONS":
					if(!$userId)
						throw new \Bitrix\Main\ArgumentNullException("userId");

					$result["COUPONS"] = Admin\OrderEdit::getCouponsData();

					break;

				case "COUPONS_LIST":

					$result["COUPONS_LIST"] = Admin\OrderEdit::getCouponList($order);

					break;

				default:
					throw new \Bitrix\Main\SystemException("Field: \"".$demandedField."\" is unknown!");
			}
		}

		return $result;
	}

	/**
	 * @param $formData
	 * @return Sale\Order
	 * @throws ArgumentNullException
	 * @throws UserMessageException
	 */
	protected function getOrder(array $formData, Result &$result = null)
	{
		$formData["ID"] = (!isset($formData["ID"]) ? 0 : (int)$formData["ID"]);

		if($this->order !== null  && !$this->formDataChanged && $this->order->getId() == $formData["ID"])
			return $this->order;

		if(!$result)
			$result = new Result();

		$currentUserId = 0;
		$oldUserId = null;

		if ($formData["ID"] > 0)
		{
			if ((int)$formData["USER_ID"] > 0)
				$currentUserId = (int)$formData["USER_ID"];
			if ((int)$formData["OLD_USER_ID"] > 0)
				$oldUserId = (int)$formData["OLD_USER_ID"];
		}
		else
		{
			if (isset($formData["USER_ID"]))
				$currentUserId = (int)$formData["USER_ID"];
			if (isset($formData["OLD_USER_ID"]))
				$oldUserId = (int)$formData["OLD_USER_ID"];
		}

		Admin\OrderEdit::initCouponsData($currentUserId, $formData["ID"], $oldUserId);
		unset($oldUserId, $currentUserId);

		if($formData["ID"] > 0)
		{
			$this->order = Sale\Order::load($formData["ID"]);

			if(!$this->order)
				throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$formData["ID"]);
		}
		else
		{
			$this->order = Admin\OrderEdit::createOrderFromForm($formData, $this->userId, false, array(), $result);

			if(!$this->order)
			{
				$this->addFilteredErrors($result);
				throw new UserMessageException;
			}
		}

		$this->formDataChanged = false;
		return $this->order;
	}

	public static function convertEncodingArray($arData, $charsetFrom, $charsetTo, &$errorMessage = "")
	{
		if (!is_array($arData))
		{
			if (is_string($arData))
			{
				$arData = Encoding::convertEncoding($arData, $charsetFrom, $charsetTo, $errorMessage);
			}
		}
		else
		{
			foreach ($arData as $key => $value)
			{
				$s = '';

				$newKey = Encoding::convertEncoding($key, $charsetFrom, $charsetTo, $s);
				$arData[$newKey] = Encoding::convertEncodingArray($value, $charsetFrom, $charsetTo, $s);

				if($newKey != $key)
					unset($arData[$key]);

				if($s!=='')
				{
					$errorMessage .= ($errorMessage == "" ? "" : "\n").$s;
				}
			}
		}

		return $arData;
	}

	protected function updatePaySystemInfoAction()
	{
		global $APPLICATION, $USER;

		if ($this->request["orderId"])
			$orderId = $this->request["orderId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_ORDER_ID_WRONG'));

		if ($this->request["paymentId"])
			$paymentId = $this->request["paymentId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_PAYMENT_ID_WRONG'));

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $order->getPaymentCollection();

			/** @var \Bitrix\Sale\Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);

			if ($payment)
			{
				$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

				if ($saleModulePermissions == 'P')
				{
					$isUserResponsible = false;
					$isAllowCompany = false;
					$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
					$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

					if ($order->getField('RESPONSIBLE_ID') == $USER->GetID()
						|| $payment->getField('RESPONSIBLE_ID') == $USER->GetID())
					{
						$isUserResponsible = true;
					}

					if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
					{
						$isAllowCompany = true;
					}

					$isAllowUpdate = in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);

					if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
					{
						throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
					}
				}

				/** @var Sale\PaySystem\Service $service */
				$service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
				if ($service->isCheckable())
				{
					$res = $service->check($payment);
					if (!$res->isSuccess())
						$this->addResultError(join('\n', $res->getErrorMessages()));
				}
			}
		}
	}

	protected function saveTrackingNumberAction()
	{
		global $APPLICATION, $USER;
		$trackingNumber = '';

		if ($this->request["orderId"])
			$orderId = $this->request["orderId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_ORDER_ID_WRONG'));

		if ($this->request["shipmentId"])
			$shipmentId = $this->request["shipmentId"];
		else
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_SHIPMENT_ID_WRONG'));

		if ($this->request['trackingNumber'])
			$trackingNumber = $this->request['trackingNumber'];

		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);
		if ($order)
		{
			/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
			$shipmentCollection = $order->getShipmentCollection();

			/** @var \Bitrix\Sale\Payment $payment */
			$shipment = $shipmentCollection->getItemById($shipmentId);

			if ($shipment)
			{
				$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

				if ($saleModulePermissions == 'P')
				{
					$isUserResponsible = false;
					$isAllowCompany = false;

					$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
					$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

					if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
					{
						$isUserResponsible = true;
					}

					if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($shipment->getField('COMPANY_ID'), $userCompanyList))
					{
						$isAllowCompany = true;
					}

					$isAllowUpdate = in_array($shipment->getField('STATUS_ID'), $allowedStatusesUpdate);

					if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
					{
						throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
					}
				}

				$result = $shipment->setField('TRACKING_NUMBER', $trackingNumber);
				if ($result->isSuccess())
				{
					$result = $order->save();
					if (!$result->isSuccess())
					{
						if ($result->hasWarnings())
						{
							$this->addResultWarning(join(', ', $result->getWarningMessages()));
						}

						$messages = join(', ', $result->getErrorMessages());
						$this->addResultError($messages);
					}
				}
			}
		}
	}

	protected function refreshTrackingStatusAction()
	{
		global $APPLICATION, $USER;
		$shipmentId = !empty($this->request["shipmentId"]) && intval($this->request["shipmentId"]) > 0 ? intval($this->request["shipmentId"]) : 0;
		$trackingNumber = !empty($this->request["trackingNumber"]) && strlen($this->request["trackingNumber"]) > 0 ? $this->request["trackingNumber"] : '';

		if($shipmentId <= 0)
			throw new ArgumentNullException('shipmentId');

		if(strlen($trackingNumber) <= 0)
			return;

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$isAllowUpdate = false;

			$resOrder = Sale\Internals\OrderTable::getList(array(
															   'filter' => array(
																   '=ID' => intval($this->request["orderId"]),
															   ),
															   'select' => array(
																   'RESPONSIBLE_ID', 'COMPANY_ID', 'STATUS_ID'
															   )
														   ));
			if ($orderData = $resOrder->fetch())
			{
				$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

				if ($orderData['RESPONSIBLE_ID'] == $USER->GetID())
				{
					$isUserResponsible = true;
				}

				if (in_array($orderData['COMPANY_ID'], $userCompanyList))
				{
					$isAllowCompany = true;
				}

				$resShipment = Sale\Internals\ShipmentTable::getList(array(
																		 'filter' => array(
																			 '=ORDER_ID' => intval($this->request["orderId"]),
																			 '=ID' => intval($this->request["shipmentId"]),
																		 ),
																		 'select' => array(
																			 'RESPONSIBLE_ID', 'COMPANY_ID'
																		 )
																	 ));
				if ($shipmentData = $resShipment->fetch())
				{
					if ($shipmentData['RESPONSIBLE_ID'] == $USER->GetID())
					{
						$isUserResponsible = true;
					}

					if (in_array($shipmentData['COMPANY_ID'], $userCompanyList))
					{
						$isAllowCompany = true;
					}
				}

				$isAllowUpdate = in_array($shipmentData['STATUS_ID'], $allowedStatusesUpdate);
			}

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$manager = Sale\Delivery\Tracking\Manager::getInstance();
		$result = $manager->getStatusByShipmentId($shipmentId, $trackingNumber);

		if($result->isSuccess())
		{
			$this->addResultData(
				'TRACKING_STATUS',
				Sale\Delivery\Tracking\Manager::getStatusName($result->status)
			);
			$this->addResultData('TRACKING_DESCRIPTION', $result->description);

			$this->addResultData(
				'TRACKING_LAST_CHANGE',
				\Bitrix\Main\Type\DateTime::createFromTimestamp(
					$result->lastChangeTimestamp
				)->toString()
			);

			$res = $manager->updateShipment($shipmentId, $result);

			if(!$res->isSuccess())
				$this->addResultError(implode(", ", $res->getErrorMessages()));
		}
		else
		{
			$this->addResultError(implode("\n", $result->getErrorMessages()));
		}
	}

	protected function unmarkOrderAction()
	{
		global $APPLICATION, $USER;
		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;

		if(!\CSaleOrder::CanUserMarkOrder($orderId, $USER->GetUserGroupArray(), $this->userId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_UNMARK_RIGHTS'));

		/** @var  Sale\Order $saleOrder*/
		if(!$saleOrder = Sale\Order::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($saleOrder->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($saleOrder->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			$isAllowUpdate = in_array($saleOrder->getField('STATUS_ID'), $allowedStatusesUpdate);

			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		/** @var \Bitrix\Sale\Result $res */
		$res = $saleOrder->setField("MARKED", "N");

		$errors = array();
		$warnings = array();

		if(!$res->isSuccess())
			$errors = $res->getErrorMessages();

		$res = $saleOrder->save();
		if(!$res->isSuccess())
		{
			$errors = array_merge($errors, $res->getErrorMessages());
		}
		elseif ($res->hasWarnings())
		{
			$warnings = array_merge($warnings, $res->getWarningMessages());
		}

		if (!empty($errors))
		{
			$this->addResultError($errors);
		}

		if (!empty($warnings))
		{
			$this->addResultWarning($warnings);
		}

	}

	protected function updatePriceCodAction($payment = null)
	{
		global $APPLICATION, $USER;
		if ($payment === null)
		{
			if ($this->request["paySystemId"] !== null)
				$paySystemId = $this->request["paySystemId"];
			else
				throw new ArgumentNullException('paymentId');

			if ($this->request["orderId"] !== null)
				$orderId = $this->request["orderId"];
			else
				throw new ArgumentNullException('orderId');

			if ($this->request["paymentId"] !== null)
				$paymentId = $this->request["paymentId"];
			else
				throw new ArgumentNullException('paymentId');

			if ($orderId > 0)
			{
				/** @var \Bitrix\Sale\Order $order */
				$order = Sale\Order::load($orderId);
				if ($order)
				{
					/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
					$paymentCollection = $order->getPaymentCollection();
					if ($paymentCollection)
					{
						/** @var \Bitrix\Sale\Payment $payment */
						if ($paymentId > 0)
						{
							$payment = $paymentCollection->getItemById($paymentId);
							if ($payment)
							{
								$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

								if ($saleModulePermissions == 'P')
								{
									$isUserResponsible = false;
									$isAllowCompany = false;

									$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

									$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

									if ($order->getField('RESPONSIBLE_ID') == $USER->GetID() ||
										$payment->getField('RESPONSIBLE_ID') == $USER->GetID())
									{
										$isUserResponsible = true;
									}

									if (in_array($order->getField('COMPANY_ID'), $userCompanyList) || in_array($payment->getField('COMPANY_ID'), $userCompanyList))
									{
										$isAllowCompany = true;
									}

									$isAllowUpdate = in_array($payment->getField('STATUS_ID'), $allowedStatusesUpdate);

									if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowUpdate)
									{
										throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
									}
								}

								$payment->setField('PAY_SYSTEM_ID', $paySystemId);
							}
						}
						else
						{
							$payment = $paymentCollection->createItem(Sale\PaySystem\Manager::getObjectById($paySystemId));
							$price = floatval($this->request["price"]);
							$payment->setField('SUM', $price);
						}
					}
				}
			}
		}
		$priceCod = 0;

		if ($payment && $payment->getPaymentSystemId() > 0)
		{
			$service = Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
			if ($service !== null)
			{
				$priceCod = $service->getPaymentPrice($payment);
				$this->addResultData('PRICE_COD', $priceCod);
			}
		}
		return $priceCod;
	}

	protected function getOrderTailsAction()
	{
		global $APPLICATION, $USER;
		$orderId = isset($this->request["orderId"]) ? $this->request["orderId"] : array();
		$formType = isset($this->request["formType"]) && $this->request["formType"] == "edit" ? "edit" : "view";
		$idPrefix = isset($this->request["idPrefix"]) ? trim($this->request["idPrefix"]) : "";

		$result = array();
		/** @var \Bitrix\Sale\Order $order */
		$order = Sale\Order::load($orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$allowedStatusesView = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('view'));
			$allowedStatusesUpdate = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));

			$isAllowView = in_array($order->getField('STATUS_ID'), $allowedStatusesView) || in_array($order->getField('STATUS_ID'), $allowedStatusesUpdate);
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());

			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}


			if ((!$isUserResponsible && !$isAllowCompany) || !$isAllowView)
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		$orderBasket = new Admin\Blocks\OrderBasket(
			$order,
			"",
			$idPrefix,
			true,
			($formType == 'edit' ? Admin\Blocks\OrderBasket::EDIT_MODE : Admin\Blocks\OrderBasket::VIEW_MODE)
			);
		Admin\OrderEdit::initCouponsData($order->getUserId(), $orderId, null);
		$result["DISCOUNTS_LIST"] = Admin\OrderEdit::getOrderedDiscounts($order, false);
		$result["BASKET"] = $orderBasket->prepareData(
			array("DISCOUNTS" => $result["DISCOUNTS_LIST"])
		);
		$result["ANALYSIS"] = Admin\Blocks\OrderAnalysis::getView($order, $orderBasket);
		$result["SHIPMENTS"] = "";
		Admin\Blocks\OrderShipment::setBackUrl($_SERVER['HTTP_REFERER']);

		$httpReferrer = new Web\Uri($_SERVER['HTTP_REFERER']);
		Admin\Blocks\OrderShipment::setBackUrl($httpReferrer->getPathQuery());

		$shipments = $order->getShipmentCollection();
		$index = 0;

		/** @var \Bitrix\Sale\Shipment  $shipment*/
		foreach ($shipments as $shipment)
		{
			if(!$shipment->isSystem())
			{
				$result["SHIPMENTS"] .= Admin\Blocks\OrderShipment::getView(
					$shipment,
					$index++,
					$formType == 'edit' ? 'edit' : ''
				);
			}
		}

		$this->addResultData("", $result);
	}


	protected function fixMarkerAction()
	{
		global $APPLICATION, $USER;

		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$markerId = isset($this->request['markerId']) ? intval($this->request['markerId']) : 0;
		$entityId = isset($this->request['entityId']) ? intval($this->request['entityId']) : 0;
		$forEntity = isset($this->request['forEntity']) && $this->request['forEntity'] == 'Y' ? true : false;

		$allowedStatusesMark = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('mark'));

		/** @var  \Bitrix\Sale\Order $order*/
		if(!$order = Sale\Order::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());
			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			if ((!$isUserResponsible && !$isAllowCompany))
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		if(!in_array($order->getField("STATUS_ID"), $allowedStatusesMark))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_UNMARK_RIGHTS'));
		}

		$errors = array();
		$warnings = array();

		$r = Sale\EntityMarker::tryFixErrorsByOrder($order, $markerId);
		if(!$r->isSuccess())
		{
			$errors = $r->getErrorMessages();
		}
		else
		{
			if (($resultList = $r->getData()) && !empty($resultList) && is_array($resultList))
			{
				if (!empty($resultList['LIST']) && array_key_exists($markerId, $resultList['LIST']) && $resultList['LIST'][$markerId] === false)
				{
					if (!empty($resultList['ERRORS']) && !empty($resultList['ERRORS'][$markerId]) && is_array($resultList['ERRORS'][$markerId]))
					{
						$warnings = array_merge($warnings, $resultList['ERRORS'][$markerId]);
					}
				}
			}
			
		}

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$errors = array_merge($errors, $r->getErrorMessages());
		}
		elseif ($r->hasWarnings())
		{
			$warnings = array_merge($warnings, $r->getWarningMessages());
		}

		if (!empty($errors))
		{
			foreach ($errors as $error)
			{
				$this->addResultError($error);
			}
		}
		else
		{
			if (!empty($warnings))
			{
				foreach ($warnings as $warning)
				{
					$this->addResultWarning($warning);
				}
			}

			if ($forEntity)
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getViewForEntity($orderId, $entityId);
			}
			else
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getView($orderId);
			}

			if (!empty($markerListHtml))
			{
				$this->addResultData('MARKERS', $markerListHtml);
			}
		}

	}
	
	
	protected function deleteMarkerAction()
	{
		global $APPLICATION, $USER;

		$orderId = isset($this->request['orderId']) ? intval($this->request['orderId']) : 0;
		$markerId = isset($this->request['markerId']) ? intval($this->request['markerId']) : 0;
		$entityId = isset($this->request['entityId']) ? intval($this->request['entityId']) : 0;
		$forEntity = isset($this->request['forEntity']) && $this->request['forEntity'] == 'Y' ? true : false;

		$allowedStatusesMark = Sale\OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('mark'));

		/** @var  \Bitrix\Sale\Order $saleOrder*/
		if(!$order = Sale\Order::load($orderId))
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_LOAD_ORDER').": ".$orderId);


		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if ($saleModulePermissions == 'P')
		{
			$isUserResponsible = false;
			$isAllowCompany = false;

			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());
			if ($order->getField('RESPONSIBLE_ID') == $USER->GetID())
			{
				$isUserResponsible = true;
			}

			if (in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$isAllowCompany = true;
			}

			if ((!$isUserResponsible && !$isAllowCompany))
			{
				throw new UserMessageException(Loc::getMessage('SALE_OA_PERMISSION'));
			}
		}

		if(!in_array($order->getField("STATUS_ID"), $allowedStatusesMark))
		{
			throw new UserMessageException(Loc::getMessage('SALE_OA_ERROR_UNMARK_RIGHTS'));
		}

		$errors = array();
		$warnings = array();

		$r = Sale\EntityMarker::delete($markerId);
		if(!$r->isSuccess())
		{
			$errors = $r->getErrorMessages();
		}
		else
		{
			if ($forEntity)
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getViewForEntity($orderId, $entityId);
			}
			else
			{
				$markerListHtml = Admin\Blocks\OrderMarker::getView($orderId);
			}
			if (!empty($markerListHtml))
			{
				$this->addResultData('MARKERS', $markerListHtml);
			}
		}

		$r = $order->save();
		if(!$r->isSuccess())
		{
			$errors = array_merge($errors, $r->getErrorMessages());
		}
		elseif ($r->hasWarnings())
		{
			$warnings = array_merge($warnings, $r->getWarningMessages());
		}

		if (!empty($errors))
		{
			foreach ($errors as $error)
			{
				$this->addResultError($error);
			}
		}

		if (!empty($warnings))
		{
			foreach ($warnings as $warning)
			{
				$this->addResultWarning($warning);
			}
		}
	}

	/**
	 * Create HTML for create check window
	 *
	 * @throws ArgumentNullException
	 */
	protected function addCheckPaymentAction()
	{
		$paymentId = $this->request['paymentId'];
		if ((int)$paymentId <= 0)
		{
			$this->addResultError("Wrong payment id");
			return ;
		}

		$paymentData = Sale\Payment::getList(
			array(
				"filter" => array("ID" => (int)$paymentId)
			)
		);
		$typeList = Cashbox\CheckManager::getCheckTypeMap();

		$payment = $paymentData->fetch();
		$order = Sale\Order::load($payment['ORDER_ID']);
		$shipmentCollection = $order->getShipmentCollection();

		$resultHtml = "<form>
							<div class=\"adm-info-message\">
									".Loc::getMessage("SALE_CASHBOX_SELECT_MESSAGE")."
							</div>
							<table>
								<tr>
									<input type='hidden' name='action' value='saveCheck'>
									<td>
										<label for='checkTypeSelect'>".Loc::getMessage("SALE_CASHBOX_SELECT_TYPE")."</label>
									</td>
									<td>
									
									
									<select class=\"sale-discount-bus-select\" name='CHECK[TYPE]' id='checkTypeSelect'>";
		foreach ($typeList as $id => $typeClass)
		{
			if (class_exists($typeClass))
			{
				$type = $typeClass::getName();
				$resultHtml .= "<option value='".$id."'>".$type."</option>";
			}
		}
		$resultHtml .= "		</select>
								</td>
							</tr>
							<tr>";
		$resultHtml .= "		<td><label for='checkShipmentSelect'>".Loc::getMessage("SALE_CASHBOX_SELECT_SHIPMENT")."</label></td>
								<td><select class=\"sale-discount-bus-select\" name='CHECK[SHIPMENT]' id='checkShipmentSelect'>";

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem())
			{
				continue;
			}

			$shipmentData = $shipment->getFieldValues();
			$resultHtml .= "<option value='".$shipmentData['ID']."'>"."[".$shipmentData['ID']."] ".$shipmentData['DELIVERY_NAME']."</option>";
		}
		$resultHtml .= "</select></td></tr></table>
				<input type='hidden' id='checkOrderId' value='".$payment['ORDER_ID']."'>
				<input type='hidden' id='checkPaymentId' value='".$payment['ID']."'>
			</form>";

		$this->addResultData("HTML", $resultHtml);
	}

	/**
	 * @throws ArgumentNullException
	 */
	protected function addCheckOrderAction()
	{
		global $APPLICATION, $USER;

		$orderId = (int)$this->request['orderId'];
		if (!$orderId && $this->request['returnHtml'])
		{
			$typeOptions = '';
			$typeList = Cashbox\CheckManager::getCheckTypeMap();
			foreach ($typeList as $id => $typeClass)
			{
				if (class_exists($typeClass))
				{
					$type = $typeClass::getName();
					$typeOptions .= "<option value='$id'>$type</option>";
				}
			}
			$windowHTML = "<form>
								<div class=\"adm-info-message\">".Loc::getMessage('CASHBOX_ADD_CHECK_TITLE')."</div>
								<table>
									<tr>
										<td><label for='checkInputOrder'>".Loc::getMessage('CASHBOX_ADD_CHECK_INPUT_ORDER').":</label></td>
										<td><input type='text' class=\"sale-discount-bus-select\" name='CHECK[ORDER]' id='checkInputOrder'></td>
									</tr>
									<tr>
										<td><label for='checkSelectPayment'>".Loc::getMessage('CASHBOX_ADD_CHECK_SELECT_PAYMENT').":</label></td>
										<td>
											<select class=\"sale-discount-bus-select\" name='CHECK[PAYMENT]' id='checkSelectPayment' disabled>
												<option value=''>".Loc::getMessage('CASHBOX_ADD_CHECK_NOT_SELECTED')."</option>
											</select>
										</td>
									</tr>
									<tr>
										<td><label for='checkSelectShipment'>".Loc::getMessage('CASHBOX_ADD_CHECK_SELECT_SHIPMENT').":</label></td>
										<td>
											<select class=\"sale-discount-bus-select\" name='CHECK[SHIPMENT]' id='checkSelectShipment' disabled>
												<option value=''>".Loc::getMessage('CASHBOX_ADD_CHECK_NOT_SELECTED')."</option>
											</select>
										</td>
									</tr>
									<tr>
										<td><label for='checkSelectType'>".Loc::getMessage('CASHBOX_ADD_CHECK_SELECT_TYPE').":</label></td>
										<td>
											<select class=\"sale-discount-bus-select\" name='CHECK[TYPE]' id='checkSelectType'>"
												.$typeOptions.
											"</select>
										</td>
									</tr>
								</table>
							</form>";
							
			$this->addResultData("HTML", $windowHTML);
		}
		else
		{
			$resultData = array(
				'PAYMENT' => array(),
				'SHIPMENT' => array()
			);

			if ($orderId <= 0)
			{
				$this->addResultData("ORDER_DATA", $resultData);
				return;
			}

			$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

			$orderData = Sale\Internals\OrderTable::getList(
				array(
					"filter" => array("=ID" => $orderId),
					"select" => array(
						"USER_ID", "COMPANY_ID",
						"PAYMENT_PAY_SYSTEM_NAME" => "PAYMENT.PAY_SYSTEM_NAME",
						"PAYMENT_ID" => "PAYMENT.ID",
						"PAYMENT_CURRENCY" => "PAYMENT.CURRENCY",
						"PAYMENT_SUM" => "PAYMENT.SUM"
					)
				)
			);

			$userId = $USER->GetID();
			$userCompanyList = Sale\Services\Company\Manager::getUserCompanyList($userId);

			while ($order = $orderData->fetch())
			{
				if ($saleModulePermissions == 'P')
				{
					if ($order["USER_ID"] !== $userId && !in_array($order["COMPANY_ID"], $userCompanyList))
					{
						$this->addResultData("ORDER_DATA", $resultData);
						return;
					}
				}

				$paymentId = $order['PAYMENT_ID'];
				$paySystemName = $order['PAYMENT_PAY_SYSTEM_NAME'];
				$sum = SaleFormatCurrency($order['PAYMENT_SUM'], $order['PAYMENT_CURRENCY']);
				$resultData['PAYMENT'][] = array(
					'ID' => $paymentId,
					'NAME' => "[$paymentId] $paySystemName - $sum"
				);
			}

			$shipmentData = Sale\Shipment::getList(
				array(
					"filter" => array("=ORDER_ID" => $orderId, "!SYSTEM" => "Y"),
					"select" => array("DELIVERY_NAME", "ID")
				)
			);
			while ($shipment = $shipmentData->fetch())
			{
				$id = (int)$shipment['ID'];
				$name = htmlspecialcharsbx($shipment['DELIVERY_NAME']);
				$resultData['SHIPMENT'][] = array(
					'ID' => $id,
					'NAME' => "[$id] $name"
				);
			}

			$this->addResultData("ORDER_DATA", $resultData);
		}
	}

	/**
	 * Add new check
	 *
	 * @throws ArgumentNullException
	 */
	protected function saveCheckAction()
	{
		global $APPLICATION, $USER;
		
		$typeId = $this->request['typeId'];
		$shipmentId = (int)$this->request['shipmentId'];
		$orderId = (int)$this->request['orderId'];
		$paymentId = (int)$this->request['paymentId'];
		$entityList = array();

		if ($orderId <= 0)
		{
			$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_ORDER_ID'));
			return;
		}

		$order = Sale\Order::load($orderId);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
		
		if ($saleModulePermissions == 'P')
		{
			$userId = $USER->GetID();
			$userCompanyList = Sale\Services\Company\Manager::getUserCompanyList($userId);
			if ($order->getUserId() !== $userId && !in_array($order->getField('COMPANY_ID'), $userCompanyList))
			{
				$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_ORDER_ID'));
				return;
			}
		}
		
		if (!$paymentId)
		{
			$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_EMPTY_PAYMENT'));
			return;
		}

		$paymentCollection = $order->getPaymentCollection();
		$payment = $paymentCollection->getItemById($paymentId);

		if ($payment)
		{
			$entityList[] = $payment;
		}
		else
		{
			$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_PAYMENT_ID'));
			return;
		}

		if (!$shipmentId)
		{
			$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_EMPTY_SHIPMENT'));
			return;
		}

		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->getItemById($shipmentId);
		if ($shipment)
		{
			$entityList[] = $shipment;
		}
		else
		{
			$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_SHIPMENT_ID'));
			return;
		}

		$typeList = Cashbox\CheckManager::getCheckTypeMap();

		if (strlen($typeId) <= 0 || !(in_array($typeId, array_keys($typeList))))
		{
			$this->addResultError(Loc::getMessage('CASHBOX_CREATE_CHECK_ERROR_ORDER_ID'));
			return;
		}
			
		$addResult = Cashbox\CheckManager::addByType($entityList, $typeId);
		if (!$addResult->isSuccess())
			$this->addResultError(implode("\n", $addResult->getErrorMessages()));

		$checkData = Cashbox\CheckManager::collectInfo(
			array(
				"PAYMENT_ID" => $paymentId
			)
		);

		$htmlCheckList = Sale\Helpers\Admin\Blocks\OrderPayment::buildCheckHtml($checkData);

		$this->addResultData("CHECK_LIST_HTML", $htmlCheckList);
	}

	/**
	 * @param array $filter
	 *
	 * @return array
	 */
	private function calculateCheckSum($filter = array())
	{
		$result = array();
		$defaultSum = SaleFormatCurrency( 0, CurrencyManager::getBaseCurrency());

		$checkData = CashboxCheckTable::getList(
			array(
				'select' => array('CHECK_SUM', 'CURRENCY', 'TYPE'),
				'filter' => $filter,
				'group' => array('TYPE'),
				'runtime' => array(
					new \Bitrix\Main\Entity\ExpressionField(
						'CHECK_SUM',
						'SUM(%s)',
						array('SUM')
					)
				)
			)
		);

		while($data = $checkData->fetch())
		{
			if ($data['TYPE'] == 'sellreturn' || $data['TYPE'] == 'prepaymentreturn')
			{
				$result['RETURN_SUM'] += $data['CHECK_SUM'];
			}
			else
			{
				$result['SUM'] += $data['CHECK_SUM'];
			}
			if (empty($result['CURRENCY']))
			{
				$result['CURRENCY'] = $data['CURRENCY'];
			}
		}

		if (isset($result['SUM']) && isset($result['CURRENCY']))
		{
			$result['FORMATED_SUM'] = SaleFormatCurrency($result['SUM'], $result['CURRENCY']);
		}
		else
		{
			$result['FORMATED_SUM'] = $defaultSum;
		}

		if (isset($result['RETURN_SUM']) && isset($result['CURRENCY']))
		{
			$result['FORMATED_RETURN_SUM'] = SaleFormatCurrency($result['RETURN_SUM'], $result['CURRENCY']);
		}
		else
		{
			$result['FORMATED_RETURN_SUM'] = $defaultSum;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected function loadCashboxCheckInfoAction()
	{
		$result = array();
		$cashboxId = (int)$this->request['cashboxId'];

		if ($cashboxId <= 0)
		{
			$defaulValue = SaleFormatCurrency( 0, CurrencyManager::getBaseCurrency());
			$result['CUMULATIVE']['FORMATED_SUM'] = $defaulValue;
			$result['CASHLESS']['FORMATED_SUM'] = $defaulValue;
			$result['CASH']['FORMATED_SUM'] = $defaulValue;

			$this->addResultData("FRAME", $result);
			
			return;
		}

		$today = new Date();
		$result['CASHLESS'] = $this->calculateCheckSum(array(
			'PAYMENT.PAY_SYSTEM.IS_CASH' => 'N',
			'>DATE_CREATE' => $today,
			'CASHBOX_ID' => $cashboxId
		));

		$result['CASH'] = $this->calculateCheckSum(array(
			'PAYMENT.PAY_SYSTEM.IS_CASH' => 'Y',
			'>DATE_CREATE' => $today,
			'CASHBOX_ID' => $cashboxId
		));

		$zreportData = Cashbox\Internals\CashboxZReportTable::getList(
			array(
				'limit' => 1,
				'select' => array('CUMULATIVE_SUM', 'CURRENCY'),
				'filter' => array('CASHBOX_ID' => $cashboxId),
				'order'=> array('DATE_CREATE' => 'DESC')
			)
		);

		$result['CUMULATIVE'] = $zreportData->fetch();

		if (isset($result['CUMULATIVE']['CUMULATIVE_SUM']) && isset($result['CUMULATIVE']['CURRENCY']))
		{
			$result['CUMULATIVE']['FORMATED_SUM'] = SaleFormatCurrency($result['CUMULATIVE']['CUMULATIVE_SUM'], $result['CUMULATIVE']['CURRENCY']);
		}
		else
		{
			$result['CUMULATIVE']['FORMATED_SUM'] = SaleFormatCurrency( 0, CurrencyManager::getBaseCurrency());;
		}

		$this->addResultData(null, $result);
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function addZReportAction()
	{
		$cashboxId = (int)$this->request['cashboxId'];

		$cashboxData = Cashbox\Internals\CashboxTable::getList(
			array(
				'filter' => array(
					'=ID' => $cashboxId,
					'USE_OFFLINE' => 'N',
					'!%HANDLER' => '\\Bitrix\\Sale\\Cashbox\\Cashbox1C'
				)
			)
		);

		if ($cashbox = $cashboxData->fetch())
		{
			Cashbox\ReportManager::addZReport($cashboxId);
		}
		else
		{
			$this->addResultError(Loc::getMessage('CASHBOX_ADD_ZREPORT_WRONG_CHECKBOX'));
		}
	}
}