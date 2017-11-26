<?
/**
 * Company developer: ALTASIB
 * Developer: Andrew N. Popov
 * Site: http://www.altasib.ru
 * E-mail: dev@altasib.ru
 * @copyright (c) 2006-2016 ALTASIB
 */
?>
<?
Class ALX_GeoIP
{
	const MID = "altasib_geoip";
	const ipgeobase = "http://ipgeobase.ru:7020/geo/";
	const geoip_top = "http://geoip.elib.ru/cgi-bin/getdata.pl";

	function GetAddr($ip)
	{
		if(!function_exists('curl_init'))
		{
			ShowError("Error! cURL not installed!");
			return;
		}

		global $APPLICATION;

		$last_ip = $APPLICATION->get_cookie("LAST_IP");

		if(empty($ip))
		{
			if(!empty($_SERVER["HTTP_X_REAL_IP"]))
			{
				$ip = $_SERVER["HTTP_X_REAL_IP"];
			}
			else
			{
				$ip = $_SERVER["REMOTE_ADDR"];
			}
		}

		if(!is_array($_SESSION["GEOIP"]) || $ip != $last_ip)
		{
			$bSetCookie = COption::GetOptionString(self::MID, "set_cookie", "Y")==="Y";
			if($bSetCookie)
			{
				$strData = $APPLICATION->get_cookie("GEOIP");
			}

			if(($ip == $last_ip) && $strData)
			{
				$arData = unserialize($strData);
			}
			else
			{
				$arData = ALX_GeoIP::GetGeoData($ip);
				if(!$arData) return false;

				$strData = serialize($arData);

				if($bSetCookie)
				{
					$APPLICATION->set_cookie("GEOIP", $strData, time()+30000000);
				}
				$APPLICATION->set_cookie("LAST_IP", $ip, time()+30000000);
			}
			$_SESSION["GEOIP"] = $arData;
		}
		return $_SESSION["GEOIP"];
	}


	function ParseXML($text)
	{
		if(strlen($text) > 0)
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");
			$objXML = new CDataXML();
			$res = $objXML->LoadString($text);
			if($res !== false)
			{
				$arRes = $objXML->GetArray();
			}
		}

		$arRes = current($arRes);
		$arRes = $arRes["#"];
		$arRes = current($arRes);

		$ar = Array();

		foreach($arRes as $key => $arVal)
		{
			foreach($arVal["#"] as $title => $Tval)
			{
				$ar[$key][$title] = $Tval["0"]["#"];
			}
		}
		return ($ar[0]);
	}

	function GetGeoData($ip)
	{
		if(ALX_GeoIP::InitBots())
			return false;

		if(!$arData = ALX_GeoIP::GetGeoDataIpgeobase_ru($ip))
		{
			if(!$arData = ALX_GeoIP::GetGeoDataGeoip_Elib_ru($ip))
			{
				return false;
			}
		}
		return $arData;
	}

	function GetGeoDataIpgeobase_ru($ip)
	{
		if(empty($ip))
			return;

		if(!function_exists('curl_init'))
		{
			if(!$text = file_get_contents(self::ipgeobase.'?ip='.$ip))
				return false;
		}
		else
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, self::ipgeobase."?ip=".$ip);
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);

			$text = curl_exec($ch);

			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			curl_close($ch);

			if($errno)
				return false;
		}

		$text = iconv("windows-1251", SITE_CHARSET, $text);

		$arData = ALX_GeoIP::ParseXML($text);
		return ($arData);
	}

	function GetGeoDataGeoip_Elib_ru($ip)
	{
		if(empty($ip))
			return;

		$siteCode = COption::GetOptionString(self::MID, SITE_ID."_site_code", "");

		$strUrl = self::geoip_top.'?ip='.$ip.'&hex=3ffd';
		if(!empty($siteCode))
			$strUrl .= "&sid=".$siteCode;

		if(!function_exists('curl_init'))
		{
			if(!$text = file_get_contents($strUrl))
				return false;
		}
		else
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $strUrl);
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);

			$text = curl_exec($ch);

			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			curl_close($ch);

			if($errno)
				return false;
		}

		$text = iconv("UTF-8", SITE_CHARSET, $text);

		$arData_ = ALX_GeoIP::ParseXML($text);
		if(isset($arData_["Error"]))
			return false;

		$arData = Array(
			"inetnum" => $ip,
			"country" => $arData_["Country"],
			"city" => $arData_["Town"],
			"region" => $arData_["Region"],
			"district" => "",
			"lat" => $arData_["Lat"],
			"lng" => $arData_["Lon"]
		);

		return ($arData);
	}

	function InitBots()
	{
		$bots = array(
			'rambler', 'googlebot', 'ia_archiver', 'Wget', 'WebAlta', 'MJ12bot', 'aport', 'yahoo', 'msnbot', 'mail.ru', 
			'alexa.com', 'Baiduspider', 'Speedy Spider', 'abot', 'Indy Library'
		);

		foreach($bots as $bot)
		{
			if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
			{
				return $bot;
			}
		}
		return false;
	}

}
?>