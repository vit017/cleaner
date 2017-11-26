<form name="city_location" class="page-header__city">
      <span class="city-dropdown">
        <span class="city-dropdown__title js-city"><?=$arResult["CURRENT_CITY"]?></span>
        <span class="city-dropdown__content">
          <span class="city-dropdown__content-title">Выберите город:</span>
          <ul class="city-dropdown__list">
              <?foreach($arResult['CITIES'] as $city){?>
                      <li data-id="<?=$city['ID']?>" class="city-dropdown__list-item <?=$city['SELECTED']=='Y'?'city-dropdown__list-item_active':' js-city-change'?>"><?=$city['CITY_NAME']?></li>
            <?}?>
          </ul>
        </span>
      </span>
    <input type="hidden" name="CITY_ID">
    <input type="hidden" name="change_city" value="Y">
</form>