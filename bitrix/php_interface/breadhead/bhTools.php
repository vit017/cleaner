<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 17.04.15
 * Time: 18:12
 */

class bhTools{
	public static function dateFormat($date, $style = false){
		if ( !is_object($date) ){
			$date = new DateTime($date);
		}
		$months = self::months(true);
		switch($style){
			case 'title':
				$date_line = bhTools::convertDayNameLong($date->format("l")).', '.$date->format("d ");
				$date_line .= $months[(int)$date->format("m")];
				break;
			case 'list':
				$date_line = bhTools::convertDayName($date->format("l")).', '.$date->format("d ");
				$date_line .= $months[(int)$date->format("m")];
				break;
			case 'js':
				$date_line = $date->format('Y-m-d');
				break;
			case 'date':
				$date_line = $date->format('d.m.Y');
				break;
			case 'detail':
			case 'default':
				$date_line = $date->format("d ");
				$date_line .= $months[(int)$date->format("m")];
				$date_line .= $date->format(" Y");
				break;

		}
		return $date_line;
	}

	public static function setDuration($time, $duration){
		$start = $time;
		$finish = $time + $duration;
//		$start_f = $start.':00';
		$start_f = mb_ereg('\:\d{2}$', $start) ? $start : $start . ':00';
		$finish_f = $finish == ceil($finish) ? $finish.':00': floor($finish).':30';
		return array('START' => $start, 'FINISH' => $finish, 'START_FORMATED' => $start_f, 'FINISH_FORMATED' => $finish_f);
	}

	/**
	 *
	 * Склонение слов
	 * @param int $int
	 * @param array $expressions список формата ("ед.ч. им. падеж", "ед.ч. род. падеж", "мн.ч. род. падеж" )
	 * @return string
	 */
	public static function words($int, $expressions)
	{
		settype($int, "integer");
		$count = $int % 100;
		if ($count >= 5 && $count <= 20)
		{
			$result = $expressions[2];
		} else
		{
			$count = $count % 10;
			if ($count == 1)
			{
				$result = $expressions[0];
			} elseif ($count >= 2 && $count <= 4)
			{
				$result = $expressions[1];
			} else
			{
				$result = $expressions[2];
			}
		}

		return $result;
	}


	/**
	 *
	 * возвращает русифицированные месяцы. Нумерация под функцию date()
	 * @return array
	 */
	public static function months($r = false)
	{
		static $months = array('', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь');
		static $months_r = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября','октября', 'ноября', 'декабря');
		if($r)
			return $months_r;
		return $months;
	}

	/**
	 *
	 * возвращает русифицированные дни недели. Нумерация под функцию date()
	 * @return array
	 */
	public static function weekdays()
	{
		static $days = array( 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
		return $days;
	}

	public static function convertDayName($day, $lang = 'en'){
		$days['ru'] = array( 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
		$days['en'] = array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
		$key = array_search($day, $days[$lang]);
		if($lang == 'en'){
			return $days['ru'][$key];
		}else{
			return $days['en'][$key];
		}
	}

	public static function convertDayNameLong($day, $lang = 'en'){
		$days['ru'] = array( 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
		$days['en'] = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
		$key = array_search($day, $days[$lang]);

		if($lang == 'en'){
			return $days['ru'][$key];
		}else{
			return $days['en'][$key];
		}
	}


	public static function setPriceType($change = false){
		global $USER;

		if ( $change ){
			$dbUser = CUser::getByID($USER->getId());
			$arUser = $dbUser->fetch();

			if(isset($_SESSION['CITY_ID']) && $_SESSION['CITY_ID'] != $arUser['PERSONAL_CITY']) {
				$_SESSION['BH_SAVE_DATE_TIME'] = false;
			}
			$_SESSION['CITY_ID'] = $arUser['PERSONAL_CITY'];
		}
		if ( isset($_SESSION['CATALOG_PRICE_TYPE']) && !$change ){
			return true;
		} else {
			$cpt = bhSettings::$cpt_msc;
			if ( isset($_SESSION['CITY_ID']) ){
				switch ($_SESSION['CITY_ID']){
					case bhSettings::$city_id_msc:
						$cpt = bhSettings::$cpt_msc;
						break;
					default:
						$cpt = bhSettings::$cpt_spb;
				}
				$_SESSION['CATALOG_PRICE_TYPE'] = $cpt;
				$_SESSION['HOUR_PRICE'] = bhSettings::$hour_price_spb;
			} else {
				$_SESSION['CITY_ID'] = bhSettings::$city_id_msc;
				$_SESSION['CATALOG_PRICE_TYPE'] = $cpt;
				$_SESSION['HOUR_PRICE'] = bhSettings::$hour_price_msc;
			}
			return true;
		}
	}

	public static function getPriceType(){
		if ( isset($_SESSION['CATALOG_PRICE_TYPE']) ){
			if ( in_array($_SESSION['CATALOG_PRICE_TYPE'], array(bhSettings::$cpt_spb, bhSettings::$cpt_msc))){
			} else {
				self::setPriceType();
			}
		} else {
			self::setPriceType();
		}
		return intVal($_SESSION['CATALOG_PRICE_TYPE']);
	}

	public static function updatePrices($basket_id = false, $catalog_price_type = false, $fuser_id = false){
		if(!CModule::IncludeModule('sale'))
			return;
		if(!CModule::IncludeModule('catalog'))
			return;

		$arFilter = array();
		if ( $basket_id > 0 ){
			$arFilter["ID"] = $basket_id;
		}
		if ( !$catalog_price_type ){
			$catalog_price_type = self::getPriceType();
		}
		if ( !$fuser_id ){
			$fuser_id = CSaleBasket::GetBasketUserID();
		}
		$arFilter["FUSER_ID"] = $fuser_id;

		$dbSetItems = CSaleBasket::GetList(
			array("ID" => "DESC"),
			$arFilter
		);

		while ($arItem = $dbSetItems->Fetch()) {
			$db_res = CPrice::GetList(
				array(),
				array(
					"PRODUCT_ID" => $arItem['PRODUCT_ID'],
					"CATALOG_GROUP_ID" => $catalog_price_type
				)
			);
			if ( $ar_res = $db_res->Fetch() ){
				CSaleBasket::Update($arItem['ID'], array("PRODUCT_PRICE_ID" => $ar_res["ID"], 'PRICE'=>$ar_res['PRICE']));
			}
		}
	}

	public static function setActiveOrders($number = false){
		if ( !$number ) {
			global $USER;
			$_SESSION['bh_active_orders'] = count(bhOrder::getNotDone($USER->getID()));
		} else {
			$_SESSION['bh_active_orders'] = intVal($number);
		}
	}

	public static function getActiveOrders(){
		if ( !isset($_SESSION['bh_active_orders']) || strlen($_SESSION['bh_active_orders']) == 0 ){
			self::setActiveOrders();
		}
		return $_SESSION['bh_active_orders'];
	}

	public static function setAvailOrders($number = false){
		if ( !$number ) {
			$cleaner = new bhCleaner();
			$arWishOrders = $cleaner->getWishList();
			$arOrders = $cleaner->getWeekList();
			$cWish = count($arWishOrders);
			$cOrders = 0;

			foreach ($arOrders as $day=>$orders){
				foreach ($orders as $order){
					if ( $cWish > 0 && isset($arWishOrders[$order['ID']]) ){
						continue 2;
					} else {
						$cOrders++;
					}
				}
			}

			$cAvail = $cOrders + $cWish;
			$_SESSION['bh_avail_orders'] = intVal($cAvail);
		} else {
			$_SESSION['bh_avail_orders'] = intVal($number);
		}
	}

	public static function getAvailOrders(){
		if ( !isset($_SESSION['bh_avail_orders']) || strlen($_SESSION['bh_avail_orders']) == 0 ){
			self::setAvailOrders();
		}
		return $_SESSION['bh_avail_orders'];
	}

	public static function isCleanerGroup($groupId){
		$cleaner = false;
		if ( !empty($groupId) ){
			foreach( $groupId as $arGroup ){
				if ( $arGroup['GROUP_ID'] == bhSettings::$cleaners_group){
					$cleaner = true;
					break;
				}
			}
		}
		return $cleaner;
	}

	public static function isCleaner(){
		global $USER;
		$arGroups = $USER->GetUserGroupArray();
		return self::isCleanerGroup($arGroups);
	}

	public static function formatUser($ID, $onlyActive = false){
		$return = array();
		$filter = '';
		if ( is_array($ID) ){
			$f=0;
			foreach ($ID as $i){
				if ( $f > 0 ) $filter .= ' | ';
				$filter .= $i;
				$f++;
			}
		} elseif ( $ID > 0 ) {
			$filter = $ID;
		} else
			return false;
		$db = CUser::getList($by='SORT', $o='DESC', array('ID' => $filter));
		while ($arUser = $db->fetch()){
			if ( $onlyActive && $arUser['ACTIVE'] != 'Y'){
				continue;
			}
			//get photo
			if ( isset($arUser['PERSONAL_PHOTO']) && $arUser['PERSONAL_PHOTO'] != ''){
				$arIcon = CFile::getFileArray($arUser['PERSONAL_PHOTO']);
				$arUser['PERSONAL_PHOTO'] = $arIcon['SRC'];
			} else {
				$arUser['PERSONAL_PHOTO'] = '/layout/assets/images/content/cleaner-unknown.png';
			}
			$return[$arUser['ID']] = array(
				'NAME' => $arUser['NAME'],
				'EMAIL' => $arUser['EMAIL'],
				'ID' => $arUser['ID'],
				'PERSONAL_PHOTO' => $arUser['PERSONAL_PHOTO'],
				'PERSONAL_PHONE' => $arUser['PERSONAL_PHONE'],
				'UF_RATING' => $arUser['UF_RATING']);
		}
		return $return;
	}

	public static function getCleaners(){
		$arCleaners = array();
		$db = CUser::GetList($b = "ID", $o = "DESC", array("GROUPS_ID"=>bhSettings::$cleaners_group),
			array('SELECT'=>array('ID', 'UF_RATING', 'PERSONAL_NOTES', 'EMAIL', 'NAME', 'LAST_NAME', 'PERSONAL_PHOTO', 'EMAIL')));
		while ($arCleaner = $db->Fetch()){
			$arCleaners[$arCleaner['ID']] = $arCleaner;
		}
		return $arCleaners;
	}

	//sms conform code functions
	public static function checkConfirm($phone, $code){
		if(strlen($_SESSION['PHONE_CONFIRM_CODE'])>0 && $_SESSION['PHONE_CONFIRM_CODE_SENT']=='Y'){
			if($phone == $_SESSION['PHONE_CONFIRM_NUMBER'] && $_SESSION['PHONE_CONFIRM_CODE'] == $code){
				return true;
			}
			else
				return false;
		}else
			return false;
	}

	public static function generateRandomString($length = 3) {
		$characters = '0123456789';//'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		//xmp($randomString);
		return $randomString;
	}

	public static function setConfirm($number, $sent, $code){
		if($code &&  $sent){
			if(bhTools::checkConfirm($number, $code)){
				unset($_SESSION['PHONE_CONFIRM_CODE_SENT']);
				$_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] = 'Y';
				return true;
			}else
				return false;
		}elseif($number){
			if ( $_SESSION['PHONE_CONFIRM_CODE_SENT']=='Y' && $_SESSION['PHONE_CONFIRM_NUMBER'] != $number )
				return false;
			else{
				bhTools::cancelConfirm();
				$_SESSION['PHONE_CONFIRM_NUMBER'] = $number;
				$_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] = 'Y';
				return true;
			}
		}else
			return false;
	}

	public static function cancelConfirm(){
		unset($_SESSION['PHONE_CONFIRM_CODE']);
		unset($_SESSION['PHONE_CONFIRM_CODE_SENT']);
		unset($_SESSION['PHONE_CONFIRM_CODE_CONFIRMED']);
		unset($_SESSION['PHONE_CONFIRM_NUMBER']);
	}

	public static function sendConfirmCode($phone){
		if(!$phone) return false;
		$string = bhTools::generateRandomString(3);
		//self::sendSms($phone, $string);

		$_SESSION['PHONE_CONFIRM_CODE'] = $string;
		$_SESSION['PHONE_CONFIRM_CODE_SENT'] = 'Y';
		$_SESSION['PHONE_CONFIRM_CODE_CONFIRMED'] = false;
		$_SESSION['PHONE_CONFIRM_NUMBER'] = $phone;
		self::sendSms($phone, 'Ваш код подтверждения MaxClean: '.$string);
		//echo 'CODE:'.$string;
	}

	public static function sendSms($phone, $string){
		if(!$phone) return false;

        return sendsms($phone, $string);
		//$string_formated = mb_convert_encoding($string , 'UTF-16', 'UTF-8');
//		$phone_formated = preg_replace('/[ -.()]/', '',$phone);

//		$sms = file_get_contents('https://intra.becar.ru/f8/spservice/request.php?dima-phone='.$phone.'&messagebody='.$string.'&MaxClean=');
		//self::execSms($phone_formated, $string_formated);
	}

	//END sms conform code functions

	private function execSms($phone_formated, $string){
		$smpphost = 'smpp04.a1smsmarket.ru';
		$smppport = 5000;
		$systemid = 'sm627155010';
		$password = "gXSv6WSr";
// $systemid = 'novativegar';
// $password = "begibyfa";
		$from = "GetTidy";
		declare(ticks = 1);
		$tx = new SMPP($smpphost,$smppport);
		$tx->system_type="";
		$tx->addr_npi = 0;

		$tx->bindTransceiver($systemid,$password);
		$tx->sendSMS($from, $phone_formated, $string);
	}

	//обрезка адреса
	public static function cutAddress($address){

		$address = mb_convert_encoding($address, mb_detect_encoding($address), 'UTF-8');
		$address = htmlspecialchars($address);

		$replace = "[-?\/?\d{1,}$]";
		$str = preg_replace($replace,'',$address);
		$replace = array(
			"[\,?\s?квартира?$]",
			"[\,?\s?Квартира?$]",
			"[-?\,?\s?кв.?$]",
			"[-?\,?\s?кВ.?$]",
			"[-?\,?\s?Кв.?$]",
			"[-?\,?\s?КВ.?$]"
		);
		$str = preg_replace($replace,'',trim($str));
		return $str;
	}

	public static function makeAddLine($services){
		$additional_line = '';
		$i = 0;
		if ( count($services) >1 ) {
			foreach ( $services as $service ) {
				if ( $service['QUANTITY'] > 0 ) {
                    if ( $additional_line == '' ){
                        $additional_line = '<br>';
                    }
					if ( $i > 0 ) {
						$additional_line .= "<br>";
					}
					$additional_line .= "&mdash; ".$service['NAME_FORMATED'];
					$i++;
				}
			}
		} else {
			foreach ( $services as $service ) {
				if ( $service['QUANTITY'] > 0 ) {
					$additional_line = $service['NAME_FORMATED'];
				}
			}
		};
		return $additional_line;
	}
	public static function makeAddLineLi($services){
		$additional_line = '';
		$i = 0;
        foreach ( $services as $service ) {
            if ( $service['QUANTITY'] > 0 ) {
                $additional_line .= "<li>" . $service['NAME_FORMATED'] . '</li>';
                $i++;
            }
        }
        return $additional_line;
	}
}