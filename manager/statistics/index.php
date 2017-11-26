<?php
define('NEED_AUTH', 'Y');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Страница статистики (для менеджера)');
CModule::IncludeModule('sale');
$default_start = '2013-01-21';
$default_end = '2015-03-25';
if ( isset($_REQUEST['ACTION']) ) {
	if ( strlen($_REQUEST['DATE_FROM']) > 0 ) {
		$start = new DateTime($_REQUEST['DATE_FROM']);
	}
	else {
		$start = new DateTime($default_start);
	}

	if ( strlen($_REQUEST['DATE_TO']) > 0 ) {
		$end = new DateTime($_REQUEST['DATE_TO']);
	}
	else {
		$end = new DateTime($default_end . ' 23:59:59');
	}


//USERS
	$arExcept = array();
	$db = CUser::GetList($b = "ID", $o = "DESC", array("GROUPS_ID" => array(bhSettings::$testers_group, bhSettings::$cleaners_group)),
		array('SELECT' => array('ID')));
	while ( $arUser = $db->Fetch() ) {
		$arExcept[$arUser['ID']] = $arUser['EMAIL'];
	}
//xmp($arExcept);
	$arUsers = array();
	$usersNew = array();
	$db = CUser::GetList($b = "ID", $o = "DESC", array('ACTIVE' => 'Y'), array('SELECT' => array('ID')));
	while ( $arUser = $db->Fetch() ) {
		if ( !isset($arExcept[$arUser['ID']]) ) {
			//all registered users
			$arUsers[$arUser['ID']] = $arUser['EMAIL'];
			$reg = new DateTime($arUser['DATE_REGISTER']);

			if ( bhTools::dateFormat($reg, 'js') >= bhTools::dateFormat($start, 'js') && bhTools::dateFormat($reg, 'js') <= bhTools::dateFormat($end, 'js') ) {
				$usersNew[$arUser['ID']] = $arUser['ID'];
			}
		}
	}

	$countUsers = array();
	$countNewUsers = array();

//ORDERS
	$filterDate = array('DATE_FROM' => $start->format('d.m.Y H:i:s'), 'DATE_TO' => $end->format('d.m.Y H:i:s'), 'USER_ID' => array_keys($arUsers));
	$ids = array();
	$idsPayed = $idsCanceled = $payedNewUser = $canceledNewUser = array();
	$arOrders = $idsUsers = array();
	$cOrderUsersNew = 0;
	$cAllSumm = 0;
	$cAllSummByNew = 0;
	$db = CSaleOrder::getList(array(), $filterDate, false, false, array('ID', 'USER_ID', 'PAYED', 'CANCELED', 'DATE_INSERT', 'DISCOUNT_PRICE', 'PRICE'));
	while ( $order = $db->fetch() ) {
		$arOrders[$order['ID']] = $order;
		$idsUsers[$order['USER_ID']][] = $order['ID'];
		$ids[] = $order['ID'];
		if ( $order['PAYED'] == 'Y' ) {
			$idsPayed[] = $order['ID'];
			if ( isset($usersNew[$order['USER_ID']]) ) {
				$payedNewUser[$order['USER_ID']] = $order['USER_ID'];
			}
		}
		if ( $order['CANCELED'] == 'Y' ) {
			$idsCanceled[] = $order['ID'];
			if ( isset($usersNew[$order['USER_ID']]) ) {
				$canceledNewUser[$order['USER_ID']] = $order['USER_ID'];
			}
		}

		$orderPrice = $order['PRICE'] + $order['DISCOUNT_PRICE'];

		if ( isset($usersNew[$order['USER_ID']]) ) {
			$cOrderUsersNew++;
			$cAllSummByNew += $orderPrice;
			$countNewUsers[$order['USER_ID']] = $order['USER_ID'];
		}

		$countUsers[$order['USER_ID']] = $order['USER_ID'];
		$cAllSumm += $orderPrice;
	}

	$cOrders = count($ids);
	$cOrdersPayed = count($idsPayed);
	$cOrdersCanceled = count($idsCanceled);
	$cOrdersPayedNewUser = count($payedNewUser);
	$cOrdersCanceledNewUser = count($canceledNewUser);
	$cUsersNew = count($countNewUsers);
	$cUsers = count($countUsers);


	$idsLoyalUsers = array();
	$idsLoyalNewUsers = array();
	foreach ( $idsUsers as $userID => $orders ) {
		if ( count($orders) > 1 ) {
			$idsLoyalUsers[$userID] = count($orders);
			if ( isset($usersNew[$userID]) ) {
				$idsLoyalNewUsers[$userID] = count($orders);
			}
		}
	}
	$cLoyal = count($idsLoyalUsers);
	$cLoyalNew = count($idsLoyalNewUsers);

//ACTIVE USERS
	$active = new DateTime('-45 days');

	$idsLoyalActiveUsers = array();
	$filterDate = array('USER_ID' => array_keys($idsLoyalUsers));
	$db = CSaleOrder::getList(array(), $filterDate, false, false, array('DATE_INSERT', 'ID', 'CANCELED', 'USER_ID'));
	while ( $order = $db->fetch() ) {
		$dateTime = new DateTime($order['DATE_INSERT']);
		if ( $dateTime->format('Y-m-d') >= $active->format('Y-m-d') ) {
			$idsLoyalActiveUsers[$order["USER_ID"]][] = $order["ID"];
			if ( isset($usersNew[$order["USER_ID"]]) ) {
				$idsLoyalActiveNewUsers[$order["USER_ID"]] = $order["ID"];
			}
		}
		$y = $dateTime->format('y');
		$m = $dateTime->format('m');
		if ( $order["CANCELED"] != 'Y' ) {
			//           $arByMonth[$y][$m][$order["USER_ID"]][] = $order["ID"];
//            $arByUser[$order["USER_ID"]][$y][$m][] = $order["ID"];
			$arByStamp[$order["USER_ID"]][$dateTime->getTimestamp()] = $order["ID"];
		}

	}

	$cLoyalActiveUsers = count($idsLoyalActiveUsers);
	$cLoyalActiveNewUsers = count($idsLoyalActiveNewUsers);
//CALCULATES
	$evBill = intVal($cAllSumm / $cOrders);
	$evBillNew = intVal($cAllSummByNew / $cOrderUsersNew);

	$summ = 0;
	foreach ( $idsLoyalUsers as $count ) {
		$summ += $count;
	}
	$evOrders = round($summ / $cLoyal, 1);

	$summ = 0;
	foreach ( $idsLoyalNewUsers as $count ) {
		$summ += $count;
	}
	$evOrdersNew = round($summ / $cLoyalNew, 1);

	$everage = 0;
	$everageNew = 0;
	foreach ( $arByStamp as $userID => $orders ){
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
		$everage += $ev;
		if ( isset($usersNew[$userID]) ) {
			$everageNew += $ev;
		}
	}

	$evPerMonth = round($everage / count($arByUser), 1);
	$evPerMonthNew = round($everageNew / $cUsersNew ,1);
	$show = true;
} else {
	$show = false;
}
//die;?>
<div>
	<form method="POST" style="margin:0px 0 20px 80px">
		<input type="date" style="width:150px" name="DATE_FROM" value="<?=strlen($_REQUEST['DATE_FROM'])>0?$_REQUEST['DATE_FROM']:$default_start?>">
		<label> - </label>
		<input type="date" style="width:150px" name="DATE_TO" value="<?=strlen($_REQUEST['DATE_TO'])>0?$_REQUEST['DATE_TO']:$default_end?>">
		<input type="submit" name="ACTION" value="DO IT!">
	</form>
	<br />
	<?if ($show) {?>
		<table style="width:800px;">
			<tr>
				<td style="width:60px"></td>
				<td>
					<table border="1" cellspacing="2" cellpadding="5" style="width:290px;">
						<tr>
							<th colspan="5" style="line-height: 30px">
								Все клиенты
							</th>
						</tr>
						<tr style="font-weight: bold">
							<td>Количество заказов</td>
							<td width="20%"><?=$cOrders?></td>
						</tr>
						<tr style="">
							<td>- новыми</td>
							<td width="15%"><?=$cOrderUsersNew?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>- оплаченных</td>
							<td width="15%"><?=$cOrdersPayed?></td>
						</tr>
						<tr style="">
							<td>- новыми</td>
							<td width="15%"><?=$cOrdersPayedNewUser?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>- отмененные</td>
							<td width="15%"><?=$cOrdersCanceled?></td>
						</tr>
						<tr style="">
							<td>- новыми</td>
							<td width="15%"><?=$cOrdersCanceledNewUser?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Количество клиентов</td>
							<td width="15%"><?=$cUsers?></td>
						</tr>
						<tr >
							<td>- Новых</td>
							<td width="15%"><?=$cUsersNew?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>- Постоянных</td>
							<td width="15%"><?=$cLoyal?></td>
						</tr>
						<tr >
							<td>- активных (после <?=$active->format('d.m.Y')?>)</td>
							<td width="15%"><?=$cLoyalActiveUsers?></td>
						</tr>
						<tr >
							<td>- умерших</td>
							<td width="15%"><?=$cLoyal-$cLoyalActiveUsers?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Средний чек</td>
							<td width="15%"><?=$evBill?> <span class="rouble">Р</span></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Среднее количество заказов на постоянного клиента</td>
							<td width="15%"><?=$evOrders?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Частота заказов для постоянных клиентов</td>
							<td width="15%"><?=$evPerMonth?> в мес.</td>
						</tr>
					</table>
				</td>
				<td>

					<table border="1" cellspacing="2" cellpadding="5" style="width:290px;">
						<tr>
							<th colspan="5" style="line-height: 30px">
								Когорты по дате первого заказа
							</th>
						</tr>
						<tr style="font-weight: bold">
							<td>Количество заказов</td>
							<td width="20%"><?=$cOrderUsersNew?></td>
						</tr>
						<tr >
							<td>- оплаченных</td>
							<td width="15%"><?=$cOrdersPayedNewUser?></td>
						</tr>
						<tr >
							<td>- отмененные</td>
							<td width="15%"><?=$cOrdersCanceledNewUser?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Количество клиентов</td>
							<td width="15%"><?=$cUsersNew?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>- Постоянных</td>
							<td width="15%"><?=$cLoyalNew?></td>
						</tr>
						<tr >
							<td>- активных (после <?=$active->format('d.m.Y')?>)</td>
							<td width="15%"><?=$cLoyalActiveNewUsers?></td>
						</tr>
						<tr >
							<td>- умерших</td>
							<td width="15%"><?=$cLoyalNew-$cLoyalActiveNewUsers?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Средний чек</td>
							<td width="15%"><?=$evBillNew?> <span class="rouble">Р</span></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Среднее количество заказов на постоянного клиента</td>
							<td width="15%"><?=$evOrdersNew?></td>
						</tr>
						<tr style="font-weight: bold">
							<td>Частота заказов для постоянных клиентов</td>
							<td width="15%"><?=$evPerMonthNew?> в мес.</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	<?}?>
</div>
