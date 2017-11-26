<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 13.05.14
 * Time: 14:16
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Клинерам");?>

  <div class="container">

    <h1 class="page-title">Клинерам</h1>

  <section class="page-blocks clearfix">
      <div class="page-blocks__item page-blocks__item_type_main">
          <div class="content-section">
              <div class="for-cleaners-intro">
                  <p>
                      Любите чистоту? <br>
                      Присоединяйтесь к команде
                      MaxClean!
                  </p>
                  <a href="#feedbackCleaner" class="btn btn_type_second">Подать заявку</a>
              </div>
              <h2>Что мы предлагаем</h2>
              <ul class="offers">
                  <li class="offers__item">
                      <span class="offers__item-icon offers__item-icon_calendar"></span>
                      <span class="offers__item-title">Гибкий график</span>
                      <p>
                          Вы сами выбираете, когда вам удобно работать, и составляете свой график.
                      </p>
                  </li>
                  <li class="offers__item">
                      <span class="offers__item-icon offers__item-icon_money"></span>
                      <span class="offers__item-title">Регулярный доход</span>
                      <p>
                          Ваш доход зависит только от вас. Чем больше работаете, тем больше получаете.
                      </p>
                  </li>
                  <li class="offers__item">
                      <span class="offers__item-icon offers__item-icon_learn"></span>
                      <span class="offers__item-title">Обучение</span>
                      <p>
                          Стандартам и правилам сервиса вас научат наши опытные специалисты.
                      </p>
                  </li>
                  <li class="offers__item">
                      <span class="offers__item-icon offers__item-icon_clothes"></span>
                      <span class="offers__item-title">Мягкие фартуки <br>и рюкзаки</span>
                  </li>
                  <li class="offers__item">
                      <span class="offers__item-icon offers__item-icon_eco"></span>
                      <span class="offers__item-title">Эко-средства <br> премиум-класса</span>
                  </li>
                  <li class="offers__item">
                      <span class="offers__item-icon offers__item-icon_metro"></span>
                      <span class="offers__item-title">Офис рядом <br> с метро</span>
                  </li>
              </ul>

              <h2>Что требуется от вас</h2>
              <ul class="txt-list">
                  <li>— Вы обладаете аналогичным опытом работы</li>
                  <li>— Вы трудолюбивы и ответственны</li>
                  <li>— Вы любите делать людей счастливыми</li>
                  <li>— Вы опрятно выглядите</li>
                  <li>— Вы знаете, что такое качественный сервис</li>
              </ul>
              <form name="feedbackCleaner" class="feedbackCleaner form" id="feedbackCleaner">
                  <h2>Форма заявки</h2>
                  <p>Отправьте заявку или позвоните по телефону 8 (800) 222-83-30</p>
                  <label class="input-txt input-txt_width_full" data-placeholder="Кратко о себе">
                      <textarea name="comment" class="input-txt__field input-txt__field_area" placeholder="Кратко о себе"></textarea>
                  </label>
                  <label class="input-txt input-txt_width_full input-txt_state_focused" data-placeholder="Ваше имя">
                    <input type="text" class="input-txt__field" placeholder="Ваше имя" name="name">
                  </label>
                  <label class="input-txt input-txt_width_full input-txt_state_focused" data-placeholder="Телефон">
                    <input type="text" id="phone" class="input-txt__field" placeholder="Телефон" name="phone">
                  </label>                  
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");