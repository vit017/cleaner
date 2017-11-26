                  </div> <!-- page-content closing tag-->
            </div><!-- page-wrapper closing tag-->
        <footer class="page-footer">
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
                        <a href="tel:<?=str_replace(array('(', ')', ' ', '-'),'',$_SESSION['PHONE'])?>" class="phone-block phone-block_light js-phone"><?=$_SESSION['PHONE']?></a>
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
                        <a class="main-nav__social-item btn btn_type_vk" href="http://vk.com/gettidy" target="_blank">
                            <span class="btn__icon btn__icon_type_vk"></span>Вконтакте
                        </a>
                        <a class="main-nav__social-item btn btn_type_fb" href="http://facebook.com/gettidy" target="_blank">
                            <span class="btn__icon btn__icon_type_fb"></span>Facebook
                        </a>
                        <a class="main-nav__social-item btn btn_type_insta" href="http://instagram.com/gettidy" target="_blank">
                            <span class="btn__icon btn__icon_type_insta"></span>Instagram
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
                                <span class="main-nav__links-item main-nav__links-item_cards">
                                  <img src="/layout/assets/images/content/cards.png" alt=""/>
                                </span>
                            </nav>
                        </div>
                        <div class="main-nav__footer-right">
                            <span class="main-nav__copyright">
                                Сделано в <a href="http://breadhead.ru/" target="_blank">Breadhead</a>
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
                        <a href="tel:<?=str_replace(array('(', ')', ' ', '-'),'',$_SESSION['PHONE'])?>" class="phone-block phone-block_light js-phone"><?=$_SESSION['PHONE']?></a>
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
                    <a class="main-nav__social-item btn btn_type_vk btn_with_icons" href="http://vk.com/gettidy" target="_blank">
                        <span class="btn__icon btn__icon_type_vk"></span>Вконтакте
                    </a>
                    <a class="main-nav__social-item btn btn_type_fb btn_with_icons" href="http://facebook.com/gettidy" target="_blank">
                        <span class="btn__icon btn__icon_type_fb"></span>Facebook
                    </a>
                    <a class="main-nav__social-item btn btn_type_insta btn_with_icons" href="http://instagram.com/gettidy" target="_blank">
                      <span class="btn__icon btn__icon_type_insta"></span>Instagram
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
                            <span class="main-nav__links-item main-nav__links-item_cards">
                              <img src="/layout/assets/images/content/cards.png" alt=""/>
                            </span>
                        </nav>
                    </div>
                    <div class="main-nav__footer-right">
                        <span class="main-nav__copyright">
                            Сделано в <a href="http://breadhead.ru/" target="_blank">Breadhead</a>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

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
      <style>
          .js-show_detail{
              cursor: pointer;
          }


      </style>
    </body>
</html>
