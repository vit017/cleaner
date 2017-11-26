<?
namespace Bitrix\Sale\Cashbox\AdminPage\Settings
{
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale\Internals\Input;
	use Bitrix\Sale\Cashbox;

	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
		die();

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/delivery/inputs.php");

	global $APPLICATION;

	$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
	if ($saleModulePermissions < "W")
		$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

	Loc::loadMessages(__FILE__);

	$result = '';
	// variable $cashbox must be defined in file, where this file is included
	if (isset($cashbox))
	{
		/** @var Cashbox\Cashbox $handler */
		$handler = $cashbox['HANDLER'];
		$cashboxSettings = $cashbox['SETTINGS'];
		if (class_exists($handler))
		{
			$settings = $handler::getSettings($cashbox['KKM_ID']);

			if ($settings)
			{
				foreach ($settings as $group => $block)
				{
					$result .= '<tr class="heading"><td colspan="2">'.$block['LABEL'].'</td></tr>';
					foreach ($block['ITEMS'] as $code => $item)
					{
						$value = null;
						if (isset($cashboxSettings[$group][$code]))
							$value = $cashboxSettings[$group][$code];

						if ($handler === '\Bitrix\Sale\Cashbox\CashboxBitrix' && $group === 'PAYMENT_TYPE')
						{
							/* hack is for difference between real values of payment cashbox's settings and user view (diff is '-1') */
							if ($value === null)
								$value = $item['VALUE'];

							$value++;
						}

						$result .= '<td width="45%" class="adm-detail-content-cell-l">'.$item['LABEL'].':</td><td width="55%" valign="top" class="adm-detail-content-cell-r">'.Input\Manager::getEditHtml('SETTINGS['.$group.']['.$code.']', $item, $value).'</td></tr>';
					}
				}
			}
		}
	}

	if ($result === '')
		$result = '<tr><td colspan="2">'.Loc::getMessage('SALE_CASHBOX_NO_SETTINGS').'</td></tr>';

	echo $result;
}