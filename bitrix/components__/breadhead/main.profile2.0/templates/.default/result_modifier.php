<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 21.05.14
 * Time: 16:40
 */
if (!CModule::IncludeModule("sale"))
{
    ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
    return;
}
?>
   <?
   $db_vars = CSaleLocation::GetList(
       array(
           "SORT" => "ASC",
           "COUNTRY_NAME_LANG" => "ASC",
           "CITY_NAME_LANG" => "ASC"
       ),
       array("LID" => LANGUAGE_ID),
       false,
       false,
       array()
   );
   $city = $arResult["arUser"]["PERSONAL_CITY"];
   $arResult["arUser"]["PERSONAL_CITY"] = array('VARIANTS', 'VALUE');
   while ($vars = $db_vars->Fetch()){
       $arResult["arUser"]["PERSONAL_CITY"]['VARIANTS'][$vars["ID"]] = htmlspecialchars($vars["CITY_NAME"]);
       if($city == $vars["ID"]){
           $arResult["arUser"]["PERSONAL_CITY"]['VALUE'] = $vars["ID"];
       }
   }