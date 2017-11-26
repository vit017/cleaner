<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization;
use Bitrix\Sale\Result;
use Bitrix\Catalog;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxAtolFarm
 * @package Bitrix\Sale\Cashbox
 */
class CashboxAtolFarm extends Cashbox implements IPrintImmediately
{
	const TOKEN_OPTION_NAME = 'atol_access_token';
	const SERVICE_URL = 'https://online.atol.ru/possystem/v3';
	const RESPONSE_HTTP_CODE_401 = 401;
	const RESPONSE_HTTP_CODE_200 = 200;

	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		$data = $check->getDataForCheck();

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data['date_create'];

		$phone = \NormalizePhone($data['client_phone']);
		if (is_string($phone))
		{
			if ($phone[0] === '7')
				$phone = substr($phone, 1);
		}
		else
		{
			$phone = '';
		}

		$result = array(
			'timestamp' => $dateTime->format('d.m.Y H:i:s'),
			'external_id' => static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']),
			'service' => array(
				'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
				'callback_url' => $this->getCallbackUrl(),
				'payment_address' => $this->getValueFromSettings('SERVICE', 'P_ADDRESS'),
			),
			'receipt' => array(
				'attributes' => array(
					'email' => $data['client_email'] ?: '',
					'phone' => $phone,
					'sno' => $this->getValueFromSettings('TAX', 'SNO'),
				),
				'payments' => array(),
				'items' => array(),
				'total' => (float)$data['total_sum']
			)
		);

		foreach ($data['payments'] as $payment)
		{
			$result['receipt']['payments'][] = array(
				'type' => (int)$this->getValueFromSettings('PAYMENT_TYPE', $payment['is_cash']),
				'sum' => (float)$payment['sum']
			);
		}

		foreach ($data['items'] as $i => $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);

			$result['receipt']['items'][] = array(
				'name' => $item['name'],
				'price' => (float)$item['price'],
				'sum' => (float)$item['sum'],
				'quantity' => $item['quantity'],
				'tax' => ($vat !== null) ? $vat : 'none'
			);
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private function getCallbackUrl()
	{
		$context = Main\Application::getInstance()->getContext();
		$scheme = $context->getRequest()->isHttps() ? 'https' : 'http';
		$server = $context->getServer();
		$domain = $server->getServerName();

		if (preg_match('/^(?<domain>.+):(?<port>\d+)$/', $domain, $matches))
		{
			$domain = $matches['domain'];
			$port   = $matches['port'];
		}
		else
		{
			$port = $server->getServerPort();
		}
		$port = in_array($port, array(80, 443)) ? '' : ':'.$port;

		return sprintf('%s://%s%s/bitrix/tools/sale_farm_check_print.php', $scheme, $domain, $port);
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_TITLE');
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function extractCheckData(array $data)
	{
		$result = array();

		if (!$data['uuid'])
			return $result;

		$checkInfo = CheckManager::getCheckInfoByExternalUuid($data['uuid']);

		if ($data['error'])
		{
			$errorType = static::getErrorType($data['error']['code']);

			$result['ERROR'] = array(
				'CODE' => $data['error']['code'],
				'MESSAGE' => $data['error']['text'],
				'TYPE' => ($errorType === Errors\Error::TYPE) ? Errors\Error::TYPE : Errors\Warning::TYPE
			);
		}

		$result['ID'] = $checkInfo['ID'];
		$result['CHECK_TYPE'] = $checkInfo['TYPE'];
		$result['LINK_PARAMS'] = array(
			Check::PARAM_REG_NUMBER_KKT => $data['payload']['ecr_registration_number'],
			Check::PARAM_FISCAL_DOC_ATTR => $data['payload']['fiscal_document_attribute'],
			Check::PARAM_FISCAL_DOC_NUMBER => $data['payload']['fiscal_document_number'],
			Check::PARAM_FISCAL_RECEIPT_NUMBER => $data['payload']['fiscal_receipt_number'],
			Check::PARAM_FN_NUMBER => $data['payload']['fn_number'],
			Check::PARAM_SHIFT_NUMBER => $data['payload']['shift_number'],
			Check::PARAM_DOC_SUM => $data['payload']['total'],
			Check::PARAM_DOC_TIME => $data['payload']['receipt_datetime'],
		);

		return $result;
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function buildZReportQuery($id)
	{
		return array();
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function extractZReportData(array $data)
	{
		return array();
	}

	/**
	 * @return array
	 */
	private function getCheckTypeMap()
	{
		return array(
			SellCheck::getType() => 'sell',
			SellReturnCashCheck::getType() => 'sell_refund',
			SellReturnCheck::getType() => 'sell_refund'
		);
	}

	/**
	 * @param $checkType
	 * @param $token
	 * @return string
	 */
	private function createUrlOperation($checkType, $token)
	{
		$groupCode = $this->getField('NUMBER_KKM');

		return static::SERVICE_URL.'/'.$groupCode.'/'.$checkType.'?tokenid='.$token;
	}

	/**
	 * @param Check $check
	 * @return Result
	 */
	public function printImmediately(Check $check)
	{
		$printResult = new Result();

		$token = $this->getAccessToken();
		if ($token === '')
		{
			$token = $this->requestAccessToken();
			if ($token === '')
			{
				$printResult->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_REQUEST_TOKEN_ERROR')));
				return $printResult;
			}
		}

		$checkQuery = static::buildCheckQuery($check);
		$validateResult = $this->validate($checkQuery);
		if (!$validateResult->isSuccess())
		{
			return $validateResult;
		}

		$checkTypeMap = $this->getCheckTypeMap();
		$checkType = $checkTypeMap[$check::getType()];

		$url = $this->createUrlOperation($checkType, $token);

		$result = $this->send($url, $checkQuery);
		if (!$result->isSuccess())
			return $result;

		$response = $result->getData();
		if ($response['http_code'] === static::RESPONSE_HTTP_CODE_401)
		{
			$token = $this->requestAccessToken();
			if ($token === '')
			{
				$printResult->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_REQUEST_TOKEN_ERROR')));
				return $printResult;
			}

			$url = $this->createUrlOperation($checkType, $token);
			$result = $this->send($url, $checkQuery);
			if (!$result->isSuccess())
				return $result;

			$response = $result->getData();
		}

		if ($response['http_code'] === static::RESPONSE_HTTP_CODE_200)
		{
			if ($response['uuid'])
			{
				$printResult->setData(array('UUID' => $response['uuid']));
			}
			else
			{
				$printResult->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_CHECK_REG_ERROR')));
			}
		}
		else
		{
			if (isset($response['error']['text']))
			{
				$printResult->addError(new Main\Error($response['error']['text']));
			}
			else
			{
				$printResult->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_CHECK_REG_ERROR')));
			}
		}

		return $printResult;
	}

	/**
	 * @param array $checkData
	 * @return Result
	 */
	private function validate(array $checkData)
	{
		$result = new Result();

		if ($checkData['receipt']['attributes']['email'] === '' && $checkData['receipt']['attributes']['phone'] === '')
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_ERR_EMPTY_PHONE_EMAIL')));
		}

		return $result;
	}

	/**
	 * @param $url
	 * @param array $data
	 * @return Result
	 */
	private function send($url, array $data)
	{
		$result = new Result();

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_USERAGENT, "1C-Bitrix");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encode($data));
		$content = curl_exec($ch);

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($content !== false)
		{
			try
			{
				$response = $this->decode($content);
				if (!is_array($response))
					$response = array();

				$response['http_code'] = $httpCode;
				$result->addData($response);
			}
			catch (Main\ArgumentException $e)
			{
				$result->addError(new Main\Error($e->getMessage()));
			}
		}
		else
		{
			$error = curl_error($ch);
			$result->addError(new Main\Error($error));
		}

		curl_close($ch);

		return $result;
	}

	/**
	 * @param int $modelId
	 * @return array
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = array(
			'AUTH' => array(
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_AUTH'),
				'ITEMS' => array(
					'LOGIN' => array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_AUTH_LOGIN_LABEL')
					),
					'PASS' => array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_AUTH_PASS_LABEL')
					),
				)
			),
			'SERVICE' => array(
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_SERVICE'),
				'ITEMS' => array(
					'INN' => array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_SERVICE_INN_LABEL')
					),
					'P_ADDRESS' => array(
						'TYPE' => 'STRING',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_SERVICE_P_ADDRESS_LABEL')
					),
				)
			)
		);

		$settings['PAYMENT_TYPE'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_P_TYPE'),
			'ITEMS' => array()
		);

		$systemPaymentType = array('Y' => 0, 'N' => 1, 'A' => 1);
		foreach ($systemPaymentType as $type => $value)
		{
			$settings['PAYMENT_TYPE']['ITEMS'][$type] = array(
				'TYPE' => 'STRING',
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_P_TYPE_LABEL_'.$type),
				'VALUE' => $value
			);
		}

		if (Main\Loader::includeModule('catalog'))
		{
			$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
			$vatList = $dbRes->fetchAll();
			if ($vatList)
			{
				$settings['VAT'] = array(
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_VAT'),
					'ITEMS' => array()
				);

				$settings['VAT']['ITEMS']['NOT_VAT'] = array(
					'TYPE' => 'STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_SETTINGS_VAT_LABEL_NOT_VAT'),
					'VALUE' => 'none'
				);

				$defaultVat = array(0 => 'vat0', 10 => 'vat10', 18 => 'vat18');
				foreach ($vatList as $vat)
				{
					$value = '';
					if (isset($defaultVat[(int)$vat['RATE']]))
						$value = $defaultVat[(int)$vat['RATE']];

					$settings['VAT']['ITEMS'][(int)$vat['ID']] = array(
						'TYPE' => 'STRING',
						'LABEL' => $vat['NAME'].' ['.(int)$vat['RATE'].'%]',
						'VALUE' => $value
					);
				}
			}
		}

		$settings['TAX'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_SNO'),
			'ITEMS' => array(
				'SNO' => array(
					'TYPE' => 'ENUM',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_SNO_LABEL'),
					'VALUE' => 'osn',
					'OPTIONS' => array(
						'osn' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SNO_OSN'),
						'usn_income' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SNO_UI'),
						'usn_income_outcome' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SNO_UIO'),
						'envd' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SNO_ENVD'),
						'esn' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SNO_ESN'),
						'patent' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SNO_PATENT')
					)
				)
			)
		);

		return $settings;
	}

	/**
	 * @param $data
	 * @return Result
	 */
	public static function validateSettings($data)
	{
		$result = new Result();

		if (empty($data['NUMBER_KKM']))
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_VALIDATE_E_NUMBER_KKM')));
		}

		if (empty($data['SETTINGS']['SERVICE']['INN']))
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_VALIDATE_E_INN')));
		}

		if (empty($data['SETTINGS']['SERVICE']['P_ADDRESS']))
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_VALIDATE_E_ADDRESS')));
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private function getAccessToken()
	{
		return Main\Config\Option::get('sale', static::TOKEN_OPTION_NAME, '');
	}

	/**
	 * @param $token
	 */
	private function setToken($token)
	{
		Main\Config\Option::set('sale', static::TOKEN_OPTION_NAME, $token);
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	private function encode(array $data)
	{
		return Main\Web\Json::encode($data);
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	private function decode($data)
	{
		return Main\Web\Json::decode($data);
	}

	/**
	 * @return string
	 */
	private function requestAccessToken()
	{
		$url = static::SERVICE_URL.'/getToken';
		$data = array(
			'login' => $this->getValueFromSettings('AUTH', 'LOGIN'),
			'pass' => $this->getValueFromSettings('AUTH', 'PASS')
		);

		$result = $this->send($url, $data);
		if ($result->isSuccess())
		{
			$response = $result->getData();
			$this->setToken($response['token']);

			return $response['token'];
		}

		return '';
	}

	/**
	 * @param $errorCode
	 * @throws Main\NotImplementedException
	 * @return int
	 */
	protected static function getErrorType($errorCode)
	{
		return Errors\Error::TYPE;
	}
}
