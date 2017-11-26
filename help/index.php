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

        <h1 class="page-title">Помощь</h1>

        <section class="page-blocks clearfix">
            <div class="page-blocks__item page-blocks__item_type_main">
                <div class="content-section">
                    <div class="hidden-desktop">
                        <h3><span class="phone"><?=phonePurify(TOLLFREENUMBER);?></span></h3>
                        <p>
                            Наши специалисты помогут вам разобраться в непонятной ситуации с 10:00 до 22:00.
                        </p>
                    </div>
                    <h2>О нашем сервисе</h2>
                    <h3>Что такое MaxClean?</h3>
                    <p>
                        Онлайн-сервис MaxClean позволяет заказать качественную и экологичную уборку квартиры в несколько кликов и по понятной цене. При этом можно вызвать клинера как с ноутбука, так и с мобильного телефона, а оплатить банковской картой или наличными.
                    </p>
                    <h3>Кто приезжает на уборку?</h3>
                    <p>
                        К вам приедет вежливый и профессиональный клинер MaxClean. Чтобы попасть в нашу команду, все клинеры проходят тщательный процесс отбора из нескольких этапов, включающих личное собеседование, проверку данных и обучение по стандартам сервиса.
                    </p>
                    <br>
                    <h2>Уборка с MaxClean</h2>
                    <h3>Что входит в уборку?</h3>
                    <p>
                        Клинеры MaxClean прошли такое обучение, что готовы сделать лучшую уборку в каждом доме и офисе. <span class="link" data-target="#what-we-clean" data-toggle="modal"><strong>Взгляните на список</strong></span>, что входит в повседневную уборку квартиры.
                    </p>
                    <p>Дополнительно можем:</p>
                    <ul>
                        <li>Помыть внутри холодильника</li>
                        <li>Почистить внутри духовки</li>
                        <li>Убраться внутри кухонных шкафчиков</li>
                        <li>Погладить одежду</li>
                        <li>Помыть окна</li>
                        <li>Убраться на балконе</li>
                    </ul>
                    <h3>Чем вы убираете?</h3>
                    <p>
                        Для уборки вашей квартиры мы будем использовать натуральные, экологичные, гипоаллергенные средства от немецкой компании KIEHL и одноразовые расходные материалы! <span class="link" data-target="#clean-tools" data-toggle="modal"><strong>Пожалуйста, ознакомьтесь с полным списком</strong></span>
                    </p>
                    <h3>Я живу загородом, сможете приехать ко мне?</h3>
                    <p>
                        Наши клинеры с удовольствием к вам приедут, если вы живете недалеко от станции метро или в шаговой доступности от остановки общественного транспорта.
                    </p>
                    <h3>Убираете ли Вы после ремонта?</h3>
                    <p>
                        Да, мы делаем уборку уборку после ремонта. Поскольку данный вид уборки, требующий иного подхода и много сил, чем повседневная уборка помещений и поэтому оплата берется за каждый час работы клинера. Позвоните нам по телефону 8 800 222-83-30.
                    </p>
                    <br>
                    <h2>Оплата</h2>
                    <h3>Сколько стоит уборка MaxClean? Как оплатить?</h3>
                    <p>
                        <strong><a href="https://maxclean.help/order/basket/">Узнайте стоимость</a></strong> вашей уборки прямо сейчас. Все средства и расходные материалы уже включены в стоимость.
                    </p>
                    <p>
                        Мы принимаем к оплате банковские карты и наличные. Для заказа уборки вам нужно выбрать способ оплаты наличными или указать данные своей банковской карты, после чего ваш заказ будет оформлен. Мы спишем с вашей банковской карты сумму, соответствующую стоимости уборки, автоматически после ее завершения. В случае оплаты наличными просто оплатите сумму после уборки. На электронную почту придет письмо, в котором будет указан состав уборки.
                    </p>
                    <h3>Можно ли оставлять клинеру чаевые?</h3>
                    <p>
                        Вы можете оставить клинеру «чаевые», если считаете это необходимым. Однако мы очень просим вас оставить оценку и отзыв на сайте после окончания уборки. На основе ваших отзывов мы премируем или накладываем взыскания на клинера.
                    </p>
                    <h3>Могу ли я отменить уборку?</h3>
                    <p>
                        Если ваши планы изменились, вы можете отменить заказ в личном кабинете не позднее дня до уборки.
                    </p>
                    <h3>Как вы обеспечиваете безопасность моих платежных данных?</h3>
                    <p>
                        Сервис MaxClean гарантирует сохранность и конфиденциальность ваших персональных данных. Данные ваших банковских карт мы не храним.
                    </p>


                    <form name="feedback" class="feedbackForm form">
                        <h2>Обратная связь</h2>
                        <p>Оставьте свой вопрос или комментарий о нашем сервисе.</p>
                        <label class="input-txt input-txt_width_full" data-placeholder="Ваш вопрос или комментарий">
                            <textarea name="comment" class="input-txt__field input-txt__field_area" placeholder="Ваш вопрос или комментарий"></textarea>
                        </label>

                        <label class="input-txt input-txt_width_full input-txt_state_focused" data-placeholder="Ваше имя">
                            <input type="text" class="input-txt__field" placeholder="Ваше имя" name="name"></label>
                        <label class="input-txt input-txt_width_full input-txt_state_focused" data-placeholder="E-mail">
                            <input type="email" class="input-txt__field" placeholder="E-mail" name="email">
                        </label>

                        <div class="form__controls">
                            <input class="btn btn_type_second" type="submit" value="Отправить">
                        </div>
                    </form>

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