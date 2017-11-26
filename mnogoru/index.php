<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Используем эко средства и одноразовые материалы. Принимаем оплату банковскими картами. На уборку приедет профессиональный сотрудник.");
$APPLICATION->SetPageProperty("keywords", "уборка, квартиры, москва, санкт-петербург, онлайн, клининговая компания, заказать, убрать, клининг, вызвать уборщика, эко, требуется уборщица, скидка");
$APPLICATION->SetPageProperty("title", "Закажите уборку в квартире онлайн");
$APPLICATION->SetTitle("Уборка без проблем");
?>
    <style type="text/css">
        .main_timer {
            position: absolute;
            top: 0;
            right: 0;
            text-align: center;
            background: rgba(255,255,255,0.3);
            width: 270px;
        }
        #sec {
            font-size: 25px;
            letter-spacing: 5px;
            color: #333;
        }
        #sec span {
            color: #3257fe;
            font-weight: 600;
        }
        .main_timer p {font-size: 12px;}
        @media screen and (max-width: 479px) {
            .main_timer {display: none;}
        }
    </style>
    <div class="main_timer">
        <span id="sec">load ...</span><br>
        <p>среднее время приезда клинера</p>
    </div>
<?
$APPLICATION->IncludeComponent("breadhead:basket.catalog2.0", "mnogo", array(
    "IBLOCK_TYPE" => "main",
    "IBLOCK_ID" => "1",
    "SECTION_ID" => "1",
    "INCLUDE_SUBSECTIONS" => "Y",
    "SHOW_ALL_WO_SECTION" => "Y",
    "PRICE_CODE" => array(
        0 => "base",
    ),
    "CHECK_MUSTBE" => "Y",
    "PROPERTY_CHECK" => "MUSTBE",
    "BASKET_URL" => "/order/basket/",
    "ACTION_VARIABLE" => "action",
    "ACTION_NAME" => "checkPrice",
    "PRODUCT_ID_VARIABLE" => "id",
    "USE_PRODUCT_MINIMUM" => "Y",
    "PROPERTY_MINIMUM" => "ORDER_MIN",
    "PROPERTY_NAME_FORMS" => "NAME_FORMS",
    "PROPERTY_DURATION" => "DURATION",
    "SUBMIT_TITLE" => "Узнать стоимость",
    "PRODUCT_QUANTITY_VARIABLE" => "quantity"
),
    false
);
?>
    <div class="some-shitty-method" style="background: #fff">
        <style type="text/css">
            @media (min-width: 930px) {
                .page-content .some-shitty-method:last-child {
                    margin-bottom: -90px;
                }
            }
        </style>
        <?php
        include($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/.default/views/service/landing-sections.php");
        ?>
		<section class="main-section" style="min-height: 0 !important;padding: 50px 0!important;">
			<div class="sc_partners">
				<h2 class="main-section__title" style="margin-bottom: 0;">Наши партнеры</h2>
				<div class="img_block_prt">
					<a href="https://maxclean.help/mnogo.php" class="partner_a partn1" target="_blank">mnogo.ru</a>
					<a href="http://www.naiglobal.com/" class="partner_a partn2" target="_blank">naiglobal.com</a>
					<a href="https://miran.ru/" class="partner_a partn3" target="_blank">miran.ru</a>
					<a href="https://www.becar.ru/" class="partner_a partn4" target="_blank">becar.ru</a>
				</div>
			</div>
		</section>
        <section class="main-section main-section_type_form" id="mail-form" style="min-height: 0 !important; padding: 90px 0 120px 0 !important;">
            <div class="form-section">
                <h2 class="main-section__title">Закажите прямо сейчас</h2>
                <div class="form-section__content">
                   <?$APPLICATION->IncludeComponent("breadhead:basket.catalog2.0", "cleaner", array(
                        "IBLOCK_TYPE" => "main",
                        "IBLOCK_ID" => "1",
                        "SECTION_ID" => "1",
                        "INCLUDE_SUBSECTIONS" => "Y",
                        "SHOW_ALL_WO_SECTION" => "Y",
                        "PRICE_CODE" => array(
                            0 => "base",
                        ),
                        "CHECK_MUSTBE" => "Y",
                        "PROPERTY_CHECK" => "MUSTBE",
                        "BASKET_URL" => "/order/basket/",
                        "ACTION_VARIABLE" => "action",
                        "ACTION_NAME" => "checkPrice",
                        "PRODUCT_ID_VARIABLE" => "id",
                        "USE_PRODUCT_MINIMUM" => "Y",
                        "PROPERTY_MINIMUM" => "ORDER_MIN",
                        "PROPERTY_NAME_FORMS" => "NAME_FORMS",
                        "PROPERTY_DURATION" => "DURATION",
                        "SUBMIT_TITLE" => "Узнать стоимость",
                        "PRODUCT_QUANTITY_VARIABLE" => "quantity"
                    ),
                        false
                    );
                    ?>
                </div>
            </div>
        </section>
    </div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>