<?php
/*
 * Блок кода в шаблоне - если уже создан заказ - вывод суммарной информации и т.д.
 */
?>
<style>
    .result-buttons a{
        margin: 20px 0px;
        display: block;
    }
		
		.title_order_info{
				color: #ffffff;
		}
		
		#result_order {
				color: #ffffff;
		}
</style>
<?
$txtTitleOrderInfo = 'Заказ создан. Информация по заказy №';
$txtLoadingInfo = 'Загружаю...';
$txtRejectOrder = 'Отменить заказ';
$txtNewOrder = 'Создать новый заказ';
$txtSendReview = 'Оставить отзыв';
?>
<div class="orderInfo">
    <h4 class="title_order_info"><?=$txtTitleOrderInfo?><?= $orderId; ?></h4>
    <div id="result_order"></div>
    <div id="loading_info"><font color="#ffffff"><?=$txtLoadingInfo?></font></div>
</div>
<script>
    jQuery(document).ready(function() {
        taxi.ordering.startOrderInfoUpdating('<?= $orderId; ?>');
    });
</script>
<div class="result-buttons">
<a href="#" id="reject_order" style="display: none" class="active" data-order_id="<?= $orderId; ?>" onclick="return false;"><font color="#ffffff"><?=$txtRejectOrder?></font></a>
<a href="/" id="new_order" onclick="return false;"> <font color="#ffffff"><?=$txtNewOrder?></font></a>
<a href="/reviews" id="send_review" style="display: none"><font color="#ffffff"><?=$txtSendReview?></font></a>
</div>

