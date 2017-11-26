<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


class CSaleExportCML2 extends CSaleExport
{
	/**
	 * @param array $arFilter
	 * @return array
	 */
	protected static function prepareFilter($arFilter=array())
	{
		return $arFilter;
	}

	/**
	 * @param array $arOrder
	 */
	protected static function saveExportParams(array $arOrder)
	{
	}
}

ob_start();

$options = array();

if (!empty($runtimeFields) && is_array($runtimeFields))
{
	$options['RUNTIME'] = $runtimeFields;
}
CSaleExportCML2::ExportOrders2Xml($arFilter, 0, "", false, 0, false, $options);

$contents = ob_get_contents();
ob_end_clean();

$str = (function_exists("mb_strlen")? mb_strlen($contents, 'latin1'): strlen($contents));
if(toUpper(LANG_CHARSET) != "WINDOWS-1251")
	$contents = $APPLICATION->ConvertCharset($contents, LANG_CHARSET, "windows-1251");


header('Pragma: public');
header('Cache-control: private');
header('Accept-Ranges: bytes');
header("Content-Type: application/xml; charset=windows-1251");
header("Content-Length: ".$str);
header("Content-Disposition: attachment; filename=orders.xml");

echo $contents;
?>