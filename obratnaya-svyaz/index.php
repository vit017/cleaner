<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.05.14
 * Time: 14:16
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Помощь и поддержка клиентов");?>



    <div class="container">

        <h1 class="page-title">Бизнес-услуги</h1>

        <section class="page-blocks clearfix">
            <div class="page-blocks__item page-blocks__item_type_main">

                <!-- <h2></h2> -->
                <p>
                    Сервис MaxClean-Бизнес предлагает индивидуальные решения по уборке, помощь по управлению объектами и дополнительные услуги. Служба MaxClean предоставляет услуги для тщательной очистки офисов, торговых площадей, магазинов, салонов красоты, фотостудий и коворкинг пространств.
                </p>
                <p>
                    Профессиональный клининг для профессионалов как вы!
                </p>

                <div class="grid">
                    <div class="grid__item desk-one-quarter">
                        <div class="advantages-item">
                                  <span class="advantages-item__icon">
                                    <i class="icon icon_insurance"></i>
                                  </span>
                            <span class="advantages-item__title">Никаких сюрпризов</span>
                            <p class="advantages-item__desc">
                                К вам приедет проверенный и профессиональный клинер MaxClean.
                            </p>
                        </div>
                    </div>
                    <div class="grid__item desk-one-quarter">
                        <div class="advantages-item">
                                  <span class="advantages-item__icon">
                                    <i class="icon icon_order"></i>
                                  </span>
                            <span class="advantages-item__title">Пакет документов</span>
                            <p class="advantages-item__desc">
                                Оплачивая по безналичнму расчету, вы получите закрывающие документы.
                            </p>
                        </div>
                    </div>
                    <div class="grid__item desk-one-quarter">
                        <div class="advantages-item">
                                  <span class="advantages-item__icon">
                                    <i class="icon icon_eco"></i>
                                  </span>
                            <span class="advantages-item__title">Оборудование для уборки</span>
                            <p class="advantages-item__desc">
                                Оборудование, средства и снабжение профессионал MaxClean привезет с собой.
                            </p>
                        </div>
                    </div>
                    <div class="grid__item desk-one-quarter">
                        <div class="advantages-item">
                                    <span class="advantages-item__icon">
                                        <i class="icon icon_warranty"></i>
                                    </span>
                            <span class="advantages-item__title">100% гарантия чистоты</span>
                            <p class="advantages-item__desc">
                                Если вам не понравится уборка, мы вернёмся и уберём заново.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <div class="hidden-desktop">
                        <h3><span class="phone"><?=$_SESSION['PHONE']?></span></h3>
                        <p>
                            Наши специалисты помогут вам разобраться в непонятной ситуации с 10:00 до 22:00.
                        </p>
                    </div>
                </div>
                <br>
                <div class="main-section" id="mail-form" style="padding: 20px 0 40px !important; background: #eff1f1; width: 100%; text-align: center; display: table;">
                    <div class="form-section">
                        <h3 class="main-section__title">Закажите прямо сейчас!</h3>
                        <div class="form-section__content">
                            <a href="/order/basket/" class="btn btn_size_big" id="link_order_cleaning">Заказать уборку</a>
                        </div>
                    </div>
                </div>
                <br>
                <div class="content-section" style="max-width: none;">
                    <div class="form" id="form">
                        <?$APPLICATION->IncludeComponent(
                            "bitrix:form.result.new",
                            "",
                            Array(
                                "SEF_MODE" => "N",
                                "WEB_FORM_ID" => 1,
                                "IGNORE_CUSTOM_TEMPLATE" => "N",
                                "USE_EXTENDED_ERRORS" => "Y",
                                "VARIABLE_ALIASES" => Array("WEB_FORM_ID"=>"WEB_FORM_ID","RESULT_ID"=>"RESULT_ID"),
                                "CACHE_TYPE" => "N",
                                "CACHE_TIME" => "3600",
                                "LIST_URL" => "",
                                "EDIT_URL" => "",
                                "SUCCESS_URL" => "",
                                "CHAIN_ITEM_TEXT" => "",
                                "CHAIN_ITEM_LINK" => ""
                            )
                        );?>
                    </div>
                </div>
                <br>
                <div class="content-section">
                    <h4>Контактные данные</h4>
                    <p><a href="mailto:b2b@maxclean.help ">b2b@maxclean.help</a></p>
                    <p><a href="tel:88002228330">8 800 222-83-30</a></p>
                </div>
            </div>
            <?include($_SERVER["DOCUMENT_ROOT"]."/include/aside.php");?>
        </section>

    </div>
    <div id="what-we-clean" class="modal fade hide">
        <div class="modal__body">
            <span class="modal__close" title="Закрыть" data-dismiss="modal"></span>
            <h2 class="modal__title">Что входит в уборку</h2>
            <div class="clean-content">
                <h3 class="clean-content__title">Комната</h3>
                <div class="clearfix">
                    <img class="clean-content__pic" src="/layout/assets/images/modal-clean/pic-1.jpg" alt=""/>
                    <ol class="clean-content__list">
                        <li>Протираем поверхности и плинтус</li>
                        <li>Убираем мусор</li>
                        <li>Заправляем постель</li>
                        <li>Убираем пыль со светильников</li>
                        <li>Поправляем небольшие вещи</li>
                        <li>Пылесосим, моем пол</li>
                        <li>Чистим зеркала и стеклянные поверхности</li>
                </div>
            </div>
            <div class="clean-content">
                <h3 class="clean-content__title">Кухня</h3>
                <div class="clearfix">
                    <img class="clean-content__pic" src="/layout/assets/images/modal-clean/pic-2.jpg" alt=""/>
                    <ol class="clean-content__list">
                        <li>Протираем стол и столешницу</li>
                        <li>Моем снаружи холодильник, духовой шкаф и плиту</li>
                        <li>Пылесосим, моем пол</li>
                        <li>Чистим раковину</li>
                        <li>Выносим мусор</li>
                        <li>Протираем поверхности и плинтус</li>
                    </ol>
                </div>
            </div>
            <div class="clean-content">
                <h3 class="clean-content__title">Ванная</h3>
                <div class="clearfix">
                    <img class="clean-content__pic" src="/layout/assets/images/modal-clean/pic-3.jpg" alt=""/>
                    <ol class="clean-content__list">
                        <li>Чистим зеркала и стеклянные поверхности</li>
                        <li>Выносим мусор</li>
                        <li>Пылесосим, моем пол</li>
                        <li>Моем и дезинфицируем раковину, ванну, душевую кабину, унитаз</li>
                        <li>Протираем поверхности</li>
                        <li>Убираем разводы снаружи шкафа и стен</li>
                    </ol>
                </div>
            </div>
            <div class="clean-content">
                <h3 class="clean-content__title">Коридор</h3>
                <div class="clearfix">
                    <img class="clean-content__pic" src="/layout/assets/images/modal-clean/pic-4.jpg" alt=""/>
                    <ol class="clean-content__list">
                        <li>Убираем пыль со светильников</li>
                        <li>Пылесосим, моем пол</li>
                        <li>Протираем поверхности и плинтус</li>
                        <li>Чистим зеркала и стеклянные поверхности</li>
                        <li>Расставляем обувь</li>
                        <li>Выносим мусор</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div id="clean-tools" class="modal fade hide">
        <div class="modal__body">
            <span class="modal__close" title="Закрыть" data-dismiss="modal"></span>
            <h2 class="modal__title">Чем мы убираем</h2>
            <p>
                Вы получите лучшую уборку в жизни. <br/>
                Взгляните на список того, что принесут с собой профессиональные клинеры MaxClean.
            </p>
            <h3>Что мы принесем с собой</h3>
            <div class="tools-item">
                <img class="tools-item__pic" src="/layout/assets/images/tools/pic-1.png" alt=""/>
                <div class="tools-item__content">
                    <h4 class="tools-item__title">Экологические средства KIEHL</h4>
                    <ul class="tools-item__list">
                        <li>— Средство для пола</li>
                        <li>— Спрей для ванных комнат</li>
                        <li>— Спрей для зеркал</li>
                        <li>— Универсальный спрей для поверхностей</li>
                    </ul>
                </div>
            </div>
            <div class="tools-item">
                <img class="tools-item__pic" src="/layout/assets/images/tools/pic-3.png" alt=""/>
                <div class="tools-item__content">
                    <h4 class="tools-item__title">Одноразовые расходные материалы</h4>
                    <ul class="tools-item__list">
                        <li>— Салфетки для пола</li>
                        <li>— Губки</li>
                        <li>— Салфетки из микрофибры</li>
                        <li>— Перчатки</li>
                        <li>— Мешки для мусора</li>
                    </ul>
                </div>
            </div>
            <div class="tools-item">
                <img class="tools-item__pic" src="/layout/assets/images/tools/pic-4.png" alt=""/>
                <div class="tools-item__content">
                    <h4 class="tools-item__title">Инвентарь</h4>
                    <ul class="tools-item__list">
                        <li>— Швабра для пола</li>
                        <li>— Щетка для мытья окон</li>
                        <li>— Чистящий ролик</li>
                    </ul>
                </div>
            </div>
            <h3>Что вам необходимо иметь</h3>
            <div class="tools-item">
                <img class="tools-item__pic" src="/layout/assets/images/tools/pic-5.png" alt=""/>
                <div class="tools-item__content">
                    <ul class="tools-item__list">
                        <li>— Пылесос (для заказов по Москве возможен приезд клинера с пылесосом для использования при повседневной сухой уборки помещения)</li>
                        <li>— Утюг</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");