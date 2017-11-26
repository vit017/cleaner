<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 31.07.14
 * Time: 13:09
 */
CModule::IncludeModule('sale');
CModule::IncludeModule('main');
CModule::IncludeModule('iblock');
class bhCleaner{

	private $userID;
	private $orders;
	private $ordersWish;
	private $ordersBusy;
	private $ordersDone;
	private $ordersNotDone;

	public function __construct( $userId = false ){
		if ( !$userId ){
			global $USER;
			$userId = $USER->getID();
		}

		$this->userID = $userId;
		$this->getOrders();
	}

	private function getOrders(){
		$arFilter = array("PROPERTY_VAL_BY_CODE_Cleaner" => $this->userID);
		$arOrders = bhOrder::getList($arFilter);
		$this->ordersBusy = array_keys($arOrders);

		$arFilter = array("PROPERTY_VAL_BY_CODE_wish_cleaner" => $this->userID);
		$orders2 = bhOrder::getList($arFilter);
		foreach ($orders2 as $id=>$order){
			if ( !isset($arOrders[$id]) ){
				$arOrders[$id] = $order;
			}
		}
		$this->orders = $arOrders;
	}

	function getWishList(){
		$return  = array();
		$arOrders = array();
		foreach ($this->orders as $order) {
			if ( $order['STATUS_ID'] == "A" ) {
				$arOrders[$order['ID']] = $order['ID'];
			}
		}

		$this->ordersWish = $arOrders;

		if ( !empty($arOrders) ){
			$this->setProps($arOrders);
			$notBusy = $this->notBusyFilter($arOrders);
			ksort($notBusy);
			foreach($notBusy as $ids){
				foreach ($ids as $id){
					$return[$id] = $this->orders[$id];
					$return[$id]['ACTION'] = $this->getActions($id);
				}
			}
		}

		return $return;
	}

	function getWeekList(){
		$inWeek = new DateTime('+7 days');
		$now = new DateTime();

		$arOrders = bhOrder::getList(array("STATUS_ID" => "A"));
		$arProps = bhOrder::getProps(array_keys($arOrders));
		$canTake = array();
		foreach($arProps as $id=>$props){
            if (!$props['DATE']['VALUE'] && !$props['TIME']['VALUE']) continue;
			$date = new dateTime($props['DATE']['VALUE'].' '.$props['TIME']['VALUE'].':00');
			if ( bhTools::dateFormat($inWeek, 'js') < bhTools::dateFormat($date, 'js') || bhTools::dateFormat($now, 'js') > bhTools::dateFormat($date, 'js')){
				unset($arOrders[$id]);
			} elseif ( (isset($props['Cleaner']['VALUE']) && ($props['Cleaner']['VALUE'] != 0 && $props['Cleaner']['VALUE'] != '')) || !self::isFree($date) ){
				unset($arOrders[$id]);
			} else {
				$tStamp = $date->getTimestamp();
				$canTake[$tStamp][] = $id;
			};

		};
        ksort($canTake);
		$return = self::makeOrderList($canTake, $arProps, $arOrders);
		return $return;
	}

	function getDoneOrders(){
		$allMy = $this->ordersBusy;
		$this->setProps($allMy);

		$arDone = array();
		foreach ($allMy as $id){
			if ( $this->orders[$id]['STATUS_ID'] == 'F' ){
				$date = new DateTime($this->orders[$id]['PROPS']['DATE']['VALUE']);
				$tStamp = $date->getTimestamp();
				$arDone[$tStamp][] = $id;
				$arProps[$id] = $this->orders[$id]['PROPS'];
			}
		}

		$this->ordersDone = array_keys($arProps);
		krsort($arDone);

		$return = self::makeOrderList($arDone, $arProps, $this->orders);
		return $return;
	}

	private function makeOrderList($byStamp, $arProps, $arOrders){
		$return = array();
		foreach($byStamp as $ids){
			foreach($ids as $id){
				$date = $arProps[$id]['DATE']['VALUE'];
				$return[$date][$id] = $arOrders[$id];
				$return[$date][$id] ['PROPS'] = $arProps[$id];
				$return[$date][$id]['ACTION'] = $this->getActions($id);
			}
		}
		return $return;
	}

	function getDoneOrdersIds(){
		if ( !$this->ordersDone ){
			$this->getDoneOrders();
		}
		return $this->ordersDone;
	}

	function getNotDoneOrders(){
		$allMy = $this->ordersBusy;
		$this->setProps($allMy);

		$arDone = array();
		foreach ($allMy as $id){
			if ( $this->orders[$id]['STATUS_ID'] == 'A' ){
				$date = new DateTime($this->orders[$id]['PROPS']['DATE']['VALUE']);
				$tStamp = $date->getTimestamp();
				$arDone[$tStamp][] = $id;
				$arProps[$id] = $this->orders[$id]['PROPS'];
			}
		}

		$this->ordersNotDone = array_keys($arProps);
		ksort($arDone);

		$return = self::makeOrderList($arDone, $arProps, $this->orders);
		return $return;
	}

	function getNotDoneOrdersIds(){
		if ( !$this->ordersNotDone ){
			$this->getNotDoneOrders();
		}
		return $this->ordersNotDone;
	}

	private function setProps($ID){
		$arProps = bhOrder::getProps($ID);
		foreach ($arProps as $order => $props){
			$this->orders[$order]['PROPS'] = $props;
		}
	}

	private function notBusyFilter($IDs){
		$tmpByDate = array();
		$today = new DateTime();
		foreach ($IDs as $id){
			$arProps = $this->orders[$id]['PROPS'];
			$date = new dateTime($arProps['DATE']['VALUE'].' '.$arProps['TIME']['VALUE'].':00');
			if ( bhTools::dateFormat($today, 'js') > bhTools::dateFormat($date, 'js')){
				continue;
			}
			if ( isset($arProps['Cleaner']) && $arProps['Cleaner']['VALUE'] > 0 ){
				continue;
			} else {
				$tStamp = $date->getTimestamp();
				$tmpByDate[$tStamp][] = $id;
			}
		}
		return $tmpByDate;
	}

	private function isFree($date){
		$canTake = true;
		$this->setProps($this->ordersBusy);
		foreach($this->ordersBusy as $id){
			$arProps = $this->orders[$id]['PROPS'];
			if ( bhTools::dateFormat($date, 'js') !=  bhTools::dateFormat($arProps['DATE']['VALUE'], 'js'))
				continue;

			$times = bhSettings::$times;

			$start = $arProps['TIME']['VALUE'];
			$dur = $arProps['DURATION']['VALUE'];
			$finish = $start + $dur + bhSettings::$SaveConst;
			if ( in_array($start, $times) ){
				$i = array_search($start, $times);
				while ($times[$i] < $finish){
					unset($times[$i]);
					$i++;
					if (!isset($times[$i]))
						break;
				}
			} else{}

			if ( in_array($start, $times)  ){
				$i = array_search($start, $times);
				$j = 0;
				while ($times[$i] < $finish){
					if ( $j > 0 && $times[$i] != $j + 2 ){
						$canTake = false;
						break;
					} else{
						$canTake = true;
					}
					$j = $times[$i];
					$i++;
					if (!isset($times[$i]))
						break;
				}
			} else {
				$canTake = false;
			}
		}
		return $canTake;
	}

	private function getActions($id){
		$canCancel = false;
		$today = new dateTime('');
		$tomorrow = new dateTime('tomorrow');

		$arOrder = $this->orders[$id];
		$props = $arOrder['PROPS'];
		$date = new dateTime($props['DATE']['VALUE']);
		if ( bhTools::dateFormat($tomorrow, 'js') < bhTools::dateFormat($date, 'js') ){
			$canCancel = true;
		}

		$canTake = false;
		if ( $arOrder['STATUS_ID'] == 'A' && $arOrder['CANCEL'] != 'Y' ){
			if ( !isset($props['Cleaner']['VALUE']) || $props['Cleaner']['VALUE'] == '' ){
				$canTake = self::isFree($date);
			}
		}

		$canFinish = false;
		if ( bhTools::dateFormat($today, 'js') >= bhTools::dateFormat($date, 'js') ){
			if ( $today->format('H') > $props['TIME']['VALUE'] && $arOrder['STATUS_ID'] == 'A'){
				$canFinish = true;
			}
		}

		$canEdit = false;
		if ( $arOrder['PAYED'] != 'Y' ){
			$canEdit = true;
		}

		$deny = false;
		if ( !isset($props['Cleaner']['VALUE']) || $props['Cleaner']['VALUE'] != $this->userID){
			$deny = true;
		}

		return array(
			'TAKE' => $canTake ?'Y':'N',
			'FINISH' => $canFinish ?'Y':'N',
			'EDIT' => $canEdit ?'Y':'N',
			'CANCEL' => $canCancel ?'Y':'N',
			'DENY' => $deny ? 'Y':'N'
		);
	}


    public static function addToOrder($cleanerID, $orderId){
        $orderId = intVal($orderId);
		return bhOrder::setProp($orderId, 'Cleaner', $cleanerID);
    }


    public static function removeFromOrder($propId, $orderId){
        $orderId = intVal($orderId);
        $props = bhOrder::getProps($orderId);
        if ( isset($props['Cleaner']) && $props['Cleaner']['VALUE'] != '' ){
            return CSaleOrderPropsValue::Update($propId, array('VALUE'=>''));
        }else{
            return false;
        }
    }

	public static function addCleanerToOrderPropValues($arFields){
		$cleanerID = $arFields['ID'];

		if ($arFields['PERSONAL_CITY']==617)
			$cleanerName = 'СПб '.trim($arFields['NAME'].' '.$arFields['LAST_NAME']);
		else
			$cleanerName = 'Мск '.trim($arFields['NAME'].' '.$arFields['LAST_NAME']);


		global $APPLICATION;
		$arPropIDs = bhOrder::getOrderPropIDsByCode('Cleaner');
		if ( !empty($arPropIDs) ){
			$arVariant = bhOrder::getOrderPropVariants($arPropIDs);
			if ( !empty($arVariant) ){
				foreach ($arVariant as $propID => $fields){
					if ( !isset($fields[$cleanerID]) ){
						$arFields = array(
							"ORDER_PROPS_ID" => $propID,
							"VALUE" => $cleanerID,
							"NAME" => $cleanerName,
							"SORT" => count($fields)*100+100,
						);
						if (!CSaleOrderPropsVariant::Add($arFields)){
							$APPLICATION->throwException("Ошибка при добавления клинера в свойства заказа для типа пользователя ".$arPropIDs[$propID]);
							return false;
						}
					}
				}
			}
		}
		return true;
	}


	public static function deleteCleanerFromOrderPropValues($cleanerID){
		global $APPLICATION;
		$db = CSaleOrder::getList(array(), array('STATUS_ID'=>'A','PROPERTY_VAL_BY_CODE' => $cleanerID));
		if($order = $db->fetch()){
			$APPLICATION->throwException("Для данного клинера остались заказы");
			return false;
		}

		$arPropIDs = bhOrder::getOrderPropIDsByCode('Cleaner');

		if ( !empty($arPropIDs) ){
			$arVariant = bhOrder::getOrderPropVariants(array_keys($arPropIDs));
			if ( !empty($arVariant) ){
				foreach ($arVariant as $propID => $fields) {
					if ( isset($fields[$cleanerID]) ) {
						$toDelete = $fields[$cleanerID];
						if ( !CSaleOrderPropsVariant::Delete($toDelete['ID']) ) {
							$APPLICATION->throwException("Ошибка при удалении клинера в свойствах заказа для типа пользователя " . $arPropIDs[$propID]);
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	public static function sendNotice($orderID, $cleanerID){
		if ( $cleanerID > 0 ){
			$text = 'Вас хотят вызвать в заказе '.$orderID;
			self::send($cleanerID, $text);
		} else {
			return false;
		}
	}

	public static function sendCancel($orderID, $cleanerID){
		if ( $cleanerID > 0 ){
			$text = 'Заказ '.$orderID.' отменен.';
			self::send($cleanerID, $text);
		} else {
			return false;
		}
	}

	public static function sendNoticeTake($orderID, $cleanerID){
//		if ( $cleanerID > 0 ){
//			$arOrder = CSaleOrder::getByID($orderID);
//			$arItems = bhBasket::getItemsByOrderId($orderID);
//
//			$basketPrice = 0;
//			$dbE = CIBlockElement::GetList(array(),array('IBLOCK_ID' => bhSettings::$IBlock_catalog,
//				'ID'=>array_keys($arItems)), false,false,
//				array('NAME', 'ID', 'PROPERTY_'.bhSettings::$catalog_mustBe, 'PROPERTY_'.bhSettings::$catalog_verb,
//					'PROPERTY_'.bhSettings::$catalog_duration, 'PROPERTY_SET_QUANTITY', 'PROPERTY_WORD'));
//			while($arElem = $dbE->getNextElement()){
//				$arProps = $arElem->getProperties();
//				$id = $arElem->fields['ID'];
//				if ( strlen($arProps[bhSettings::$catalog_mustBe]['VALUE']) > 0){
//					$props[bhSettings::$catalog_mustBe] = $arProps[bhSettings::$catalog_mustBe]['VALUE'];
//				}
//				if ( strlen($arProps[bhSettings::$catalog_verb]['VALUE']) > 0){
//					$props[bhSettings::$catalog_verb] = $arProps[bhSettings::$catalog_verb]['VALUE'];
//				}
//				if ( strlen($arProps[bhSettings::$catalog_duration]['VALUE']) > 0){
//					$props[bhSettings::$catalog_duration] = $arProps[bhSettings::$catalog_duration]['VALUE'];
//				}
//				if ( strlen($arProps['SET_QUANTITY']['VALUE']) > 0){
//					$props['SET_QUANTITY'] = $arProps['SET_QUANTITY']['VALUE'];
//				}
//				if ( strlen($arProps['WORD']['VALUE']) > 0){
//					$props['WORD'] = $arProps['WORD']['VALUE'];
//				}
//				$arItems[$id]['PROPERTIES'] = $props;
//				$basketPrice += ($arItems[$id]['PRICE'] + $arItems[$id]['DISCOUNT_PRICE'])*$arItems[$id]['QUANTITY'];
//			}
//
//			$basketF = bhBasket::getBasketFormated($arOrder['FUSER_ID'], 1, false, $arItems, true);
//
//			$mail_line= '';
//			foreach($basketF['MAIN'] as $arBasketItem ){
//				if ( $arBasketItem['QUANTITY'] > 0 ){
//					$mail_line .= $arBasketItem["NAME"].'м2';
//				}
//			};
//
//			$additional_line = trim(str_replace(array('<br>', '&mdash; ', '&nbsp;'), array(', ', ''), bhTools::makeAddLine($basketF['ADDITIONAL'])));
//
//			$arProps = bhOrder::getProps($orderID);
//
//			$date = new DateTime($arProps['DATE']['VALUE']);
//			$month = bhTools::months(true);
//			$date_line = $date->format("d ");
//			$date_line .= $month[(int)$date->format("m")];
//			$date_line .= $date->format(" Y");
//
//			if ( $arOrder["PAY_SYSTEM_ID"]==1 ){
//				$pay_line = 'оплата наличными';
//			}elseif ( $arOrder["PAY_SYSTEM_ID"]==2 ){
//				$pay_line = 'оплата картой';
//			}
//			$reward = round($basketPrice*bhSettings::$reward, -1);
//
//			$text = "Заказ ".$orderID.", ".$date_line.", ".$arProps['TIME']['VALUE'].":00
//".$arProps["PERSONAL_STREET"]["VALUE"]." ".$mail_line." ".$additional_line.", ".$arProps['PERSONAL_PHONE']['VALUE'].", ".$arProps['NAME']['VALUE'].", ".round($arOrder['PRICE'], -1)." Р (~".round((float)$arProps['DURATION']['VALUE'], 1)." ч), ".$pay_line."
//Вознаграждение: ".$reward." Р";
//			$arCleaner = bhTools::formatUser($cleanerID);
//
//			$arCleaner = $arCleaner[$cleanerID];
//			//$text = 'Ты взял заказ '.$orderID;
//			//self::send($cleanerID, $text);
//			bhTools::sendSms($arCleaner['PERSONAL_PHONE'], $text);
//			//send to USER
//			$text = 'Вам назначен клинер - '.$arCleaner["NAME"].'';
//			bhTools::sendSms($arProps['PERSONAL_PHONE']['VALUE'], $text);
//		} else {
//			return false;
//		}
	}

	private static function send($cleanerID, $text){
		$db = CUser::getByID($cleanerID);
		if ( $arCleaner = $db->fetch() ){
			$phone = trim($arCleaner['PERSONAL_PHONE']);
		}

		if ( strlen($phone) > 0){
			bhTools::sendSms($phone, $text);
		}
	}
}