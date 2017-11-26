<?php
CModule::IncludeModule('sale');
class bhStatistics{

	private $default_start = '2013-01-21';
	private $default_end = '2015-03-25';
	public $start;
	public $end;
	public $arUsers;
	public $usersNew;

	public $idsActiveOrders = array();

	public $idsPayed = array();
	public $idsCanceled = array();

	public $arOrders = array();

	public $cAllSumm = 0;
	public $payedUsersByUser = array();
	public $payedNewUser = array();
	public $payedNewUsersByUser = array();

	public $canceledNewUser = array();
	public $idsActiveNewOrders = array();
	public $idsOrderUsersNew = array();

	public $idsLoyalUsers = array();
	public $idsLoyalNewUsers = array();
	public $idsLoyalActiveUsers = array();
	public $idsLoyalActiveNewUsers = array();

	public $arByStamp;
	public $average = 0;
	public $idsOrders = array();

	public function __construct( $dates = array() ){

		$this->setPeriod($dates);
		$this->getUsers();
		$this->active = new DateTime('-45 days');
	}

	private function setPeriod($dates){
		$start = $dates['FROM'];
		$end = $dates['TO'];
		if ( strlen($start) > 0 ) {
			$start = new DateTime($start);
		}
		else {
			$start = new DateTime($this->default_start);
		}

		if ( strlen($end) > 0 ) {
			$end = new DateTime($end. ' 23:59:59');
		}
		else {
			$end = new DateTime($this->default_end . ' 23:59:59');
		}
		$this->start = $start;
		$this->end = $end;
	}

	private function getUsers(){
		//filter users
		$arExcept = array();
		$db = CUser::GetList($b = "ID", $o = "DESC",
			array("GROUPS_ID" => array(bhSettings::$testers_group, bhSettings::$cleaners_group)),
			array('SELECT' => array('ID')));
		while ( $arUser = $db->Fetch() ) {
			$arExcept[$arUser['ID']] = $arUser['EMAIL'];
		}
//xmp($arExcept);
		//get USERS
		$arUsers = array();
		$db = CUser::GetList($b = "ID", $o = "ASC", array('ACTIVE' => 'Y'), array('SELECT' => array('ID')));
		while ( $arUser = $db->Fetch() ) {
			if ( !isset($arExcept[$arUser['ID']]) ) {
				$arUsers[$arUser['ID']] = $arUser['EMAIL'];            //all registered users
				$reg = new DateTime($arUser['DATE_REGISTER']);

				if ( $reg->format('Y-m-d') >= $this->start->format('Y-m-d') && $reg->format('Y-m-d') <= $this->end->format('Y-m-d') ) {
					$this->usersNew[$arUser['ID']] = $arUser['ID'];
				}
			}
		}
		$this->arUsers = $arUsers;
	}
	// get ORDERS by period and users list
	public function getOrdersForPeriod($users){
		if (!$users){
			$users = array_keys($this->arUsers);
		}
		$filterDate = array(
			'DATE_FROM' => $this->start->format('d.m.Y H:i:s'),
			'DATE_TO' => $this->end->format('d.m.Y H:i:s'),
			'USER_ID' => $users
		);

		$arOrders = array();
		$idsCanceled = $canceledNewUser = array();
		$payedUsersByUser = $idsPayed = array();
		$payedNewUser = $payedNewUsersByUser = array();
		$idsActiveOrders = $idsActiveNewOrders = array();
		$idsOrderUsersNew = array();
		$db = CSaleOrder::getList(array(), $filterDate, false, false, array('ID', 'USER_ID', 'PAYED', 'CANCELED', 'DATE_INSERT', 'DISCOUNT_VALUE', 'PRICE', 'STATUS_ID'));
		while ( $order = $db->fetch() ) {
			$arOrders[$order['ID']] = $order;

			$isNew = isset($this->usersNew[$order['USER_ID']])?true:false;

			if ( $order['CANCELED'] == 'Y' || $order['STATUS_ID'] == 'M' || $order['STATUS_ID'] == 'C') {
				$idsCanceled[] = $order['ID'];
				if ( $isNew ) {
					$canceledNewUser[$order['ID']] = $order['USER_ID'];
				}
			} elseif ( $order['PAYED'] == 'Y' ) { //clients
				$idsPayed[] = $order['ID'];
				$payedUsersByUser[$order['USER_ID']] = $order['USER_ID'];
				if ( $isNew ) {
					$payedNewUser[$order['ID']] = $order['USER_ID'];
					$payedNewUsersByUser[$order['USER_ID']][] = $order['ID'];
				}
			} else {
				if ( $order['STATUS_ID'] == 'N' || $order['STATUS_ID'] == 'A') {
					$idsActiveOrders[] = $order['ID'];
					if ( $isNew ) {
						$idsActiveNewOrders[$order['ID']] = $order['USER_ID'];
					}
				}
			}
			if ( $isNew ) {
				$this->idsOrderUsersNew[$order['ID']] = $order['ID'];
			}
		}
		$this->arOrders = $arOrders;
		$this->idsCanceled = $idsCanceled;
		$this->canceledNewUser = $canceledNewUser;
		$this->idsPayed = $idsPayed;
		$this->payedUsersByUser = $payedUsersByUser;
		$this->payedNewUser = $payedNewUser;
		$this->payedNewUsersByUser = $payedNewUsersByUser;
		$this->idsActiveOrders = $idsActiveOrders;
		$this->idsActiveNewOrders = $idsActiveNewOrders;
		$this->idsOrderUsersNew = $idsOrderUsersNew;
		return true;
	}

	public function getSumm($orders){
		$cAllSumm = 0;

		$filter = array(
			'ORDER_ID' => $orders
		);
		$dbBasketItems = CSaleBasket::GetList(
			array("ID" => "ASC"),
			$filter,
			false,
			false,
			array("ID", "PRICE", "NAME", "PRODUCT_ID", "DISCOUNT_PRICE", "QUANTITY", "CURRENCY",
				"ORDER_ID", "FUSER_ID"
			)
		);

		$arBaskets = array();
		while ($arItem = $dbBasketItems->Fetch()){
			$arBaskets[$arItem["ID"]] = $arItem;
		}
		$byOrder = array();
		foreach($arBaskets as $item){
			$orderPrice = $item['PRICE'] + $item['DISCOUNT_PRICE'] * $item['QUANTITY'];
			$cAllSumm += $orderPrice;
			if ($item['ORDER_ID'] >0 )
			$byOrder[$item['ORDER_ID']] += $orderPrice;
		}
//		xmp($byOrder);
		$this->cAllSumm = $cAllSumm;
		return $this->cAllSumm;
	}

	public function getOrdersForUsers($users, $setLoyal = false){
		// get ORDERS users list
		$filter = array(
			'USER_ID' => $users
		);
		$payedUsersByUser = array();
		$idsPayed = array();
		$idsCanceled= array();
		$idsLoyalActiveUsers = array();
		$idsLoyalUsers = array();
		$workArray = array();
		$arByStamp = array();
		$idsActiveOrders = array();
		$db = CSaleOrder::getList(array(), $filter, false, false, array('ID', 'USER_ID', 'PAYED', 'CANCELED', 'DATE_INSERT', 'DISCOUNT_VALUE', 'PRICE', 'STATUS_ID'));
		while ( $order = $db->fetch() ) {
			if ( !$setLoyal ) {
				$this->idsOrders[$order['ID']] = $order;

				if ( $order['CANCELED'] == 'Y' || $order['STATUS_ID'] == 'M' || $order['STATUS_ID'] == 'C' ) {
					$idsCanceled[$order['ID']] = $order['USER_ID'];
				}
				elseif ( $order['PAYED'] == 'Y' ) {
					$idsPayed[] = $order['ID'];
					$payedUsersByUser[$order['USER_ID']] = $order['USER_ID'];
				}
				else {
					if ( $order['STATUS_ID'] == 'N' || $order['STATUS_ID'] == 'A' ) {
						$idsActiveOrders[$order['ID']] = $order['USER_ID'];
					}
				}
			}
			if ( $setLoyal ) {
				$dateTime = new DateTime($order['DATE_INSERT']);

				if ( $order["CANCELED"] != 'Y' && $order['PAYED'] == 'Y') {
					if ( isset($workArray[$order["USER_ID"]]) ) {
						$idsLoyalUsers[$order["USER_ID"]] = 1;
					}
					$arByStamp[$order["USER_ID"]][$dateTime->getTimestamp()] = $order["ID"];
					$workArray[$order["USER_ID"]][] = $order["ID"];

					if ( isset($idsLoyalUsers[$order["USER_ID"]]) && $dateTime->format('Y-m-d') >= $this->active->format('Y-m-d') ) {
						$idsLoyalActiveUsers[$order["USER_ID"]][] = $order["ID"];
					}
				}

			}
		}

		foreach($idsLoyalUsers as $userID=>$val){
			$idsLoyalUsers[$userID] = $workArray[$userID];
		}
		$this->payedUsersByUser = $payedUsersByUser;
		$this->idsPayed = $idsPayed;
		$this->idsCanceled = $idsCanceled;
		$this->idsLoyalActiveUsers = $idsLoyalActiveUsers;
		$this->idsActiveOrders = $idsActiveOrders;
		$this->idsLoyalUsers = $idsLoyalUsers;
		$this->arByStamp = $arByStamp;
		return true;
	}

	public function getAverage(){
		$average = 0;
		foreach ( $this->arByStamp as $userID => $orders ){
			ksort($orders);
			$first = false;
			foreach ( $orders as $stamp => $order ) {
				if ( !$first ) $first = new DateTime(date('Y-m-d', $stamp));
				$last = new DateTime(date('Y-m-d', $stamp));
			}
			$diff = $first->diff($last);
			$month = $diff->d/30 + $diff->m;
			$orCount = count($orders) - 1;
			$ev = $month / $orCount;
			$average += $ev;
		}
		$this->average = $average;
		return $this->average;
	}
}