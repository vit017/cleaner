</div> <!-- page-content closing tag-->
</div><!-- page-wrapper closing tag-->
<footer class="page-footer">
    <div class="main-nav">
        <div class="container">
            <ul class="main-nav__info">
<!--                <li class="main-nav__info-item">-->
<!--                          <span class="city-dropdown city-dropdown_width_full">-->
<!--                        <span class="city-dropdown__title city-dropdown__title_light">Санкт-Петербург</span>-->
<!--                        <span class="city-dropdown__content">-->
<!--                          Сейчас мы&nbsp;работаем только в&nbsp;Санкт-Петербурге. <a href="/moscow/">Узнать&nbsp;о&nbsp;запуске</a> в&nbsp;Москве-->
<!--                        </span>-->
<!--                      </span>-->
<!--                    --><?////$APPLICATION->IncludeComponent("breadhead:geoip", "list", array());?>
<!--                    </span>-->
<!--                </li>-->
                <li class="main-nav__info-item">
                    <a href="tel:<?=TOLLFREENUMBER;?>" class="phone-block phone-block_light js-phone"><?=phonePurify(TOLLFREENUMBER);?></a>
                </li>
            </ul>
            <?$APPLICATION->IncludeComponent("bitrix:menu", "main", array(
                "ROOT_MENU_TYPE" => "top",
                "MENU_CACHE_TYPE" => "N",
                "MENU_CACHE_TIME" => "1",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "MENU_CACHE_GET_VARS" => array(
                    'typemenu' => 'footer'
                ),
                "MAX_LEVEL" => "1",
                "CHILD_MENU_TYPE" => "",
                "USE_EXT" => "N",
                "DELAY" => "N",
                "ALLOW_MULTI_SELECT" => "N"
            ),
                false
            );?>
            <nav class="main-nav__social clearfix">
                <a class="main-nav__social-item btn" href="https://vk.com/club125274824" target="_blank">
                    <!-- <span class="btn__icon btn__icon_type_vk"></span>Вконтакте -->
                    <img src="/layout/assets/images/icon_sprite_social_05.png">
                </a>
                <a class="main-nav__social-item btn" href="https://ok.ru/group/57993637199912" target="_blank">
                    <img src="/layout/assets/images/icon_sprite_social_07.png">
                </a>
                <a class="main-nav__social-item btn" href="https://www.facebook.com/MaxClean.help/" target="_blank">
                    <!-- <span class="btn__icon btn__icon_type_insta"></span>Instagram -->
                    <img src="/layout/assets/images/icon_sprite_social_01.png">
                </a>
            </nav>
            <div class="main-nav__footer clearfix">
                <div class="main-nav__footer-left">
                    <nav class="main-nav__links">
                        <span class="main-nav__links-item">
                          <a href="/terms/">Пользовательское соглашение</a>
                        </span>
                        <span class="main-nav__links-item">
                            <a href="/policies/">Правила сайта и защиты информации</a>
                        </span>
                        <span class="main-nav__links-item">
                            <a data-target="#call_back" data-toggle="modal" class="callBackLink">Заказать звонок</a>
                        </span>
                        <span class="main-nav__links-item main-nav__links-item_cards">
                            <img src="/layout/assets/images/content/cards.png"/>
                        </span>
                    </nav>
                </div>
                <div class="main-nav__footer-right">
                    <span class="main-nav__copyright">
                        2016-<?=date("Y");?> MaxClean<br>
                        г. Санкт-Петербург, Пироговская набережная, дом 17, корпус 1
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>
</div> <!-- page-container closing tag-->

<!-- menu -->
<div class="modal-menu">
    <header class="modal-menu__header">
        <div class="container clearfix">
            <a class="modal-menu__header-logo hide-text" href="/">MaxClean</a>
            <span class="modal-menu__header-close">Закрыть</span>
        </div>
    </header>
    <div class="main-nav">
        <div class="container">
            <ul class="main-nav__info">
<!--                <li class="main-nav__info-item">-->
<!--                          <span class="city-dropdown city-dropdown_width_full">-->
<!--                        <span class="city-dropdown__title city-dropdown__title_light">Санкт-Петербург</span>-->
<!--                        <span class="city-dropdown__content">-->
<!--                          Сейчас мы&nbsp;работаем только в&nbsp;Санкт-Петербурге. <a href="/moscow/">Узнать&nbsp;о&nbsp;запуске</a> в&nbsp;Москве-->
<!--                        </span>-->
<!--                      </span>-->
<!--                    --><?////$APPLICATION->IncludeComponent("breadhead:geoip", "list", array());?>
<!--                    </span>-->
<!--                </li>-->
                <li class="main-nav__info-item">
                    <a href="tel:<?=TOLLFREENUMBER;?>" class="phone-block phone-block_light js-phone"><?=phonePurify(TOLLFREENUMBER);?></a>
                </li>
            </ul>
            <?
            define('MODAL_MENU', true);
            $APPLICATION->IncludeComponent("bitrix:menu", "main", array(
                "ROOT_MENU_TYPE" => "top",
                "MENU_CACHE_TYPE" => "N",
                "MENU_CACHE_TIME" => "1",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "MENU_CACHE_GET_VARS" => array('typemenu' => 'modal'),
                "MAX_LEVEL" => "1",
                "CHILD_MENU_TYPE" => "",
                "USE_EXT" => "N",
                "DELAY" => "N",
                "ALLOW_MULTI_SELECT" => "N"
            ),
                false
            );?>
            <nav class="main-nav__social clearfix">
                <a class="main-nav__social-item btn" href="https://vk.com/club125274824" target="_blank">
                    <!-- <span class="btn__icon btn__icon_type_vk"></span>Вконтакте -->
                    <img src="/layout/assets/images/icon_sprite_social_05.png">
                </a>
                <a class="main-nav__social-item btn" href="https://ok.ru/group/57993637199912" target="_blank">
                    <!-- <span class="btn__icon btn__icon_type_fb"></span>Facebook -->
                    <img src="/layout/assets/images/icon_sprite_social_07.png">
                </a>
                <a class="main-nav__social-item btn" href="https://www.facebook.com/MaxClean.help/" target="_blank">
                    <!-- <span class="btn__icon btn__icon_type_insta"></span>Instagram -->
                    <img src="/layout/assets/images/icon_sprite_social_01.png">
                </a>
            </nav>
            <div class="main-nav__footer clearfix">
                <div class="main-nav__footer-left">
                    <nav class="main-nav__links">
                        <span class="main-nav__links-item">
                          <a href="/terms/">Пользовательское соглашение</a>
                        </span>
                        <span class="main-nav__links-item">
                            <a href="/policies/">Правила сайта и защиты информации</a>
                        </span>
                        <span class="main-nav__links-item">
                            <a data-target="#call_back" data-toggle="modal" class="callBackLink">Заказать звонок</a>
                        </span>
                        <span class="main-nav__links-item main-nav__links-item_cards">
                            <img src="/layout/assets/images/content/cards.png"/>
                        </span>
                    </nav>
                </div>
                <div class="main-nav__footer-right">
                        <span class="main-nav__copyright">
                            2016-<?=date("Y");?> MaxClean<br>
                            г. Санкт-Петербург, Пироговская набережная, дом 17, корпус 1 
                        </span>
                </div>
            </div>
        </div>
    </div>
</div>



<div id="call_back" class="modal fade hide">
    <div class="modal__body">
        <span class="modal__close" title="Закрыть" data-dismiss="modal"></span>
        <div class="modal_form_time">
            <div class="start-section__title">Хотите, мы перезвоним в течение минуты?</div>
            <p>Мы ценим Ваше время и готовы помочь найти нужную Вам информацию. Если Вы готовы, мы обсудим это по телефону.</p>
            <form name="callBack" class="callBackForm">
                <input name="phone" type="tel" class="phoneInput" placeholder="Введите номер">
                <input class="tell_buttom btn" type="submit" value="Жду звонка">
            </form>
        </div>
    </div>
</div>


<!-- scripts -->
<script src="//yandex.st/jquery/1.11.0/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="/layout/assets/js/vendor/jquery-1.11.0.min.js"><\/script>')</script>
<script src="/layout/assets/js/vendor/plugins.min.js"></script>
<script src="/layout/assets/js/jquery-ui.min.js"></script> <!--Подключаем Календари, Табы и прочие плюшки от jQueryUI-->
<script src="/layout/assets/js/inputmask.js"></script> <!--Подключаем InputMask-->
<script src="/layout/assets/js/jquery.inputmask.js"></script> <!--Подключаем InputMask-->
<script src="/layout/assets/js/main.js"></script>
<script src="<?=SITE_TEMPLATE_PATH;?>/js/script.js"></script>





<!--Календарь, оформление заказа-->

<?if(date("H:i")>"19:00")
    $minDate="+1d";
else
    $minDate="-0";
?>
<script>
$(document).ready(function() {
    $( "#datepicker1" ).datepicker({
        inline: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: "<?=$minDate;?>",
        maxDate: "+1M",
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: "dd.mm.yy",
        dayNames: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
        monthNamesShort: [ "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
        //beforeShowDay: $.datepicker.noWeekends, // закрываем выходные
    });
});

$(document).bind("ajaxComplete",function(){
    $( "#datepicker1" ).datepicker({
        inline: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: "<?=$minDate;?>",
        maxDate: "+1M",
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: "dd.mm.yy",
        dayNames: ["Воскресенье", "Понедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"],
        monthNamesShort: [ "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"],
        //beforeShowDay: $.datepicker.noWeekends, // закрываем выходные
    });
});
</script>

<!-- http://api.yandex.ru/share/doc/dg/concepts/share-button-ov.xml -->
<script type="text/javascript" src="https://yandex.st/share/share.js" charset="utf-8"></script>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/include/select_cleaner_support.php");?>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter38469730 = new Ya.Metrika({
                    id:38469730,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true,
                    trackHash:true,
                    ut:"noindex",
                    ecommerce:"dataLayer"
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/38469730?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-80705328-1', 'auto');
    ga('send', 'pageview');

</script>

<!-- Код тега ремаркетинга Google -->

<script type="text/javascript">
    /* <![CDATA[ */
    var google_conversion_id = 872493187;
    var google_custom_params = window.google_tag_params;
    var google_remarketing_only = true;
    /* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<script>
  $('.order-form input').focus(function(){$('.forMobile .fixed_block').hide();}); 
</script>
<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/872493187/?guid=ON&amp;script=0"/>
    </div>
</noscript>

<script>
(function(w, d, s, h, id) {
    w.roistatProjectId = id; w.roistatHost = h;
    var p = d.location.protocol == "https:" ? "https://" : "http://";
    var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init";
    var js = d.createElement(s); js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
})(window, document, 'script', 'cloud.roistat.com', 'cefdddfd8dcbd94cf6d8ad34a1f37449');
</script>

</body>
</html>