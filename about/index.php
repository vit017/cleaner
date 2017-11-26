<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.05.14
 * Time: 14:16
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "О нас");?>

    <div class="container">

        <h1 class="page-title">О нас</h1>

        <section class="page-blocks clearfix">
            <div class="page-blocks__item page-blocks__item_type_main">
                <div class="content-section">
                    <p>
                        Сервис MaxClean делает повседневную уборку квартиры простой. Вы можете вызвать клинера как с ноутбука, так и с мобильного телефона.
                    </p>
                    <p>
                        Для заказа нужно лишь выбрать площадь квартиры или офиса, указать адрес и удобное время приезда клинера. К Вам приедет профессиональный и вежливый клинер, который уберет вашу квартиру, используя только экологически чистые средства и одноразовые расходные материалы.
                    </p>
                    <p>
                        Вы увидите фото клинера еще до начала уборки. Мы считаем, что нашим клиентам важно знать кого они пускают в дом.
                    </p>
                    <ul class="offers">
                        <li class="offers__item">
                            <span class="offers__item-icon offers__item-icon_order_mint"></span>
                            <span class="offers__item-title">Простой заказ</span>
                            <p>
                                <strong><a href="/order/basket/">Онлайн-заказ</a></strong> уборки за 1 минуту. Без лишних звонков.
                            </p>
                        </li>
                        <li class="offers__item">
                            <span class="offers__item-icon offers__item-icon_insurance_mint"></span>
                            <span class="offers__item-title"> Никаких сюрпризов</span>
                            <p>
                                К вам приедет проверенный и профессиональный клинер MaxClean.
                            </p>
                        </li>
                        <li class="offers__item">
                            <span class="offers__item-icon offers__item-icon_price_mint"></span>
                            <span class="offers__item-title">Прозрачные цены</span>
                            <p>
                                Вы всегда точно знаете, за что заплатили.
                            </p>
                        </li>
                        <li class="offers__item">
                            <span class="offers__item-icon offers__item-icon_warranty_mint"></span>
                            <span class="offers__item-title">100% гарантия чистоты</span>
                            <p>
                                Если Вам не понравится уборка, мы вернёмся и уберём заново.
                            </p>
                        </li>
                        <li class="offers__item">
                            <span class="offers__item-icon offers__item-icon_learn"></span>
                            <span class="offers__item-title">Технология</span>
                            <p>
                                Убираем помещения по фирменной технологии MaxClean, используя только профессиональный инвентарь и материалы.
                            </p>
                        </li>
                        <li class="offers__item">
                            <span class="offers__item-icon offers__item-icon_eco"></span>
                            <span class="offers__item-title">Эко средства</span>
                            <p>
                                Мы используем профессиональные, безопасные средства немецкой компании «Johannes Kiehl KG», отмеченные Европейским экологическим знаком ЕС Ecolabel.
                            </p>
                        </li>
                    </ul>
                </div>
                <!-- <div class="contacts" style="">

                    <h2>Команда</h2>
                    <span style="float:left; margin-right:30px">
                        <p>
                            <img src="/include/2.jpg">
                            <br>
                            <span class="offers__item-title">Дмитрий Анисимов</span>
                            Основатель, генеральный директор.<br>
                            Написать письмо: <a href="mailto:da@gettidy.ru">da@gettidy.ru</a>
                            <br>
                            <a href="https://www.facebook.com/d.anisimov">Facebook</a>
                        </p>
                    </span>
                    <span>
                        <p>
                            <img src="/include/1.jpg">
                            <br>

                            <span class="offers__item-title">Ангелина Беликова</span>
                            HR-менеджер.
                            <br>
                            Написать письмо: <a href="mailto:ab@gettidy.ru">ab@gettidy.ru</a>
                            <br>
                            <a href="https://www.facebook.com/belikovalina">Facebook</a>
                        </p>
                    </span>
                </div> -->
                <h3>Контактные данные</h3>
                <p>
                    <a href="mailto:otvet@maxclean.help">otvet@maxclean.help</a>
                    <br>
                    <?=phonePurify(TOLLFREENUMBER);?>
                    <br>
                    Санкт-Петербург,
                    <br>
                    Пироговская наб., дом 17, корпус 1, литер А.
                    <br>
                    <!-- <a href="https://www.google.ru/maps/place/Kamennoostrovskiy+pr.,+38,+Sankt-Peterburg,+197022/@59.965806,30.311187,15z/data=!4m7!1m4!3m3!1s0x4696315ade8ea349:0xdc302618cee42329!2sKamennoostrovskiy+pr.,+38,+Sankt-Peterburg,+197022!3b1!3m1!1s0x4696315ade8ea349:0xdc302618cee42329">Карта</a> -->
                </p>
            </div>
            <?include($_SERVER["DOCUMENT_ROOT"]."/include/aside.php");?>
        </section>

    </div>
    <style>
        .contacts{
            padding-top:30px;
            border-top:2px solid #eff1f1;
            border-bottom:2px solid #eff1f1;
            margin-bottom:40px;
            margin-top:-10px;
        }
        .contacts img{
            width:340px
        }



        @media screen and (width: 360px) {
            .contacts img {
                width: 320px
            }
        }
        @media screen and (width: 340px) {
            .contacts img {
                width: 300px
            }
        }
        @media screen and (width: 320px) {
            .contacts img {
                width: 280px
            }
        }
    </style>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");