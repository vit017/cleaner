                  </div> <!-- page-content closing tag-->
            </div><!-- page-wrapper closing tag-->
        <footer class="page-footer">
            <?
            unset($phoneDef);
            global $USER;
            $rsUser = CUser::GetByID($USER->GetID());
            $arUser = $rsUser->Fetch();
            if ($arUser["PERSONAL_CITY"]==617)
                $phoneDef=phonePurify(MANAGER_PHONE);
            else
                $phoneDef=phonePurify(MANAGER_PHONE_MSK);
            ?>

            <div class="main-nav">
                <div class="container">
                    <ul class="main-nav__info">
                      <li class="main-nav__info-item">
                      <span class="city-dropdown city-dropdown_width_full">
                        <span class="city-name">Санкт-Петербург</span>
                      </span>
                          <?//$APPLICATION->IncludeComponent("breadhead:geoip", "list", array());?>

                      </li>
                      <li class="main-nav__info-item">
                        <a href="tel:<?=str_replace(array('(', ')', ' ', '-'),'',$phoneDef)?>" class="phone-block phone-block_light js-phone"><?=$phoneDef?></a>
                      </li>
                    </ul>
                    <?$APPLICATION->IncludeComponent("bitrix:menu", "main", array(
                            "ROOT_MENU_TYPE" => "left",
                            "MENU_CACHE_TYPE" => "N",
                            "MENU_CACHE_TIME" => "3600",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => array(
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
                            <img src="/layout/assets/images/icon_sprite_social_05.png">
                        </a>
                        <a class="main-nav__social-item btn" href="https://ok.ru/group/57993637199912" target="_blank">
                            <img src="/layout/assets/images/icon_sprite_social_07.png">
                        </a>
                        <a class="main-nav__social-item btn" href="https://www.facebook.com/MaxClean.help/" target="_blank">
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
                            </nav>
                        </div>
                        <div class="main-nav__footer-right">
                    <span class="main-nav__copyright">
                        2016 <a href="https://gettidy.ru/" target="_blank">MaxClean</a><br>
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
              <a class="modal-menu__header-logo hide-text" href="/">Get Tidy</a>
              <span class="modal-menu__header-close">Закрыть</span>
          </div>
        </header>
        <div class="main-nav">
            <div class="container">
                <ul class="main-nav__info">
                    <li class="main-nav__info-item">
                      <span class="city-dropdown city-dropdown_width_full">
                        <span class="city-name">Санкт-Петербург</span>
                      </span>
                        <?//$APPLICATION->IncludeComponent("breadhead:geoip", "list", array());?>

                    </li>
                    <li class="main-nav__info-item">
                        <a href="tel:<?=str_replace(array('(', ')', ' ', '-'),'',$phoneDef)?>" class="phone-block phone-block_light js-phone"><?=$phoneDef?></a>
                    </li>
                </ul>
                <?$APPLICATION->IncludeComponent("bitrix:menu", "main", array(
                        "ROOT_MENU_TYPE" => "left",
                        "MENU_CACHE_TYPE" => "N",
                        "MENU_CACHE_TIME" => "3600",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => array(
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
                        <img src="/layout/assets/images/icon_sprite_social_05.png">
                    </a>
                    <a class="main-nav__social-item btn" href="https://ok.ru/group/57993637199912" target="_blank">
                        <img src="/layout/assets/images/icon_sprite_social_07.png">
                    </a>
                    <a class="main-nav__social-item btn" href="https://www.facebook.com/MaxClean.help/" target="_blank">
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
                        </nav>
                    </div>
                    <div class="main-nav__footer-right">
                        <span class="main-nav__copyright">
                            2016 <a href="https://gettidy.ru/" target="_blank">MaxClean</a><br>
                            г. Санкт-Петербург, Пироговская набережная, дом 17, корпус 1
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <? if($APPLICATION->GetCurDir() != "/cleaners/no_work/"){?>
        <!-- scripts -->
        <script src="//yandex.st/jquery/1.11.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/layout/assets/js/vendor/jquery-1.11.0.min.js"><\/script>')</script>
        <script src="/layout/assets/js/vendor/plugins.min.js"></script>
        <script src="/layout/assets/js/main.js"></script>

        <script src="<?=SITE_TEMPLATE_PATH;?>/js/script.js"></script>

        <!-- http://api.yandex.ru/share/doc/dg/concepts/share-button-ov.xml -->
        <script type="text/javascript" src="https://yandex.st/share/share.js" charset="utf-8"></script>
        <script>
          $('body').on('click', '.js-show_detail', function(){
              var block = $(this).find('.js-order_detail');
              var top = parseInt($(this).offset().top) - 100;
              if(!block.is(':visible')){
                  $('.js-order_detail').fadeOut();
                  block.fadeIn();
              }else{
                  block.fadeOut();
              }
              $('html, body').animate({
                  scrollTop: top+'px'
              });
          })


        </script>
    <? }?>
      <style>
          .js-show_detail{
              cursor: pointer;
          }


      </style>
      <!-- Yandex.Metrika counter --> <script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter38469730 = new Ya.Metrika({ id:38469730, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true, ut:"noindex" }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/38469730?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
      <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-80705328-1', 'auto');
          ga('send', 'pageview');

      </script>
	  <script>
	      $('.order-form input').focus(function(){$('.forMobile .fixed_block').hide();}); 
	  </script>
    </body>
</html>
