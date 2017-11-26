<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 23.01.15
 * Time: 15:24
 */
define('NEED_AUTH', 'Y');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle('Страница создания новой скидки (типа плательщика)');?>

<?$arResult = array();
if(isset($_POST['set'])){
    if(strlen($_POST['checkField'])>0){
        $arResult['ERROR']['checkField'] = true;
    }elseif(strlen($_POST['discount'])==0 || intVal($_POST['discount'])<=0){
        $arResult['ERROR']['discount'] = true;
        $arResult['ERROR_MESSAGE'] = 'НЕкорректный формат размера скидки';
    }else{
        $discount = trim($_POST['discount']);
        $db_ptype = CSalePersonType::GetList(Array("SORT" => "ASC"), Array("NAME"=>'Скидка '.$discount.'%'));
        if ($ptype = $db_ptype->Fetch())
        {
            $arResult['ERROR']['discount'] = true;
            $arResult['ERROR_MESSAGE'] = 'Такая скидка уже есть';
        }
    }
    if(empty($arResult['ERROR']) || !isset($arResult['ERROR'])){
        $personTypeID = CSalePersonType::Add(array('NAME'=>'Скидка '.$discount.'%', 'ACTIVE'=>'Y', 'LID'=>SITE_ID));
        if($personTypeID>0){
            $propsGroupId = CSaleOrderPropsGroup::Add(array("PERSON_TYPE_ID"=>$personTypeID, "NAME"=>"Контакты", "SORT"=>0));
            //create new basket
            $arFields = CSaleDiscount::GetByID(6);
          //  xmp($arFields);
            unset($arFields['ID']);
            unset($arFields['TIMESTAMP_X']);
            unset($arFields['DATE_CREATE']);
            $arFields['USER_GROUPS'] = array(2);
            $arFields['NAME'] = $discount.'%';
            $arConditions = unserialize($arFields['CONDITIONS']);
            $arConditions['CHILDREN'][0]['DATA']['value'] = array($personTypeID);
            $arFields['CONDITIONS'] = serialize($arConditions);

            $arFields['UNPACK'] = 'function($arOrder){return ((isset($arOrder[\'PERSON_TYPE_ID\']) && (($arOrder[\'PERSON_TYPE_ID\'] == '.$personTypeID.')))); };");';

            $arFields['APPLICATION'] = 'function (&$arOrder){CSaleDiscountActionApply::ApplyBasketDiscount($arOrder, "", -'.$discount.', "P");};';

            $arACTIONS = unserialize($arFields['ACTIONS']);
            $arACTIONS['CHILDREN'][0]['DATA']['Value'] = $discount;
            $arFields['ACTIONS'] = serialize($arACTIONS);

            $discountActionId =  CSaleDiscount::Add($arFields);

            //create pay system settings
            if ($arPaySys = CSalePaySystem::GetByID(2, 1))
            {
                $PSAfields = CSalePaySystemAction::GetByID($arPaySys['PSA_ID']);
                unset($PSAfields['ID']);
                $PSAfields['PERSON_TYPE_ID'] = $personTypeID;
                $dbPTA = CSalePaySystemAction::getList(array(), array('PAY_SYSTEM_ID'=>2, 'PERSON_TYPE_ID' => $personTypeID));
                if($arPSA = $dbPTA->fetch()){
                    $PSA = $arPSA['ID'];
                }else
                    $PSA = CSalePaySystemAction::Add($PSAfields);
            }
            if ($arPaySys = CSalePaySystem::GetByID(1, 1))
            {
                $PSAfields = CSalePaySystemAction::GetByID($arPaySys['PSA_ID']);
                unset($PSAfields['ID']);
                $PSAfields['PERSON_TYPE_ID'] = $personTypeID;
                $dbPTA = CSalePaySystemAction::getList(array(), array('PAY_SYSTEM_ID'=>1, 'PERSON_TYPE_ID' => $personTypeID));
                if($arPSA = $dbPTA->fetch()){
                    $PSA = $arPSA['ID'];
                }else
                    $PSA = CSalePaySystemAction::Add($PSAfields);
            }

            //catalog discount for coupons
            $arDiscFields = CCatalogDiscount::GetByID(7);
            $arDiscFields['NAME'] = 'Скидка '.$discount.'% для любой суммы';
            $arDiscFields['SORT'] = $personTypeID;
            $arDiscFields["DISCOUNT_VALUE"] = $discount;
            $arDiscFields["CONDITIONS"] = unserialize($arDiscFields['CONDITIONS']);
            $arDiscFields['GROUP_IDS'] = array(6);
            unset($arDiscFields['ID']);
            $discountID = CCatalogDiscount::Add($arDiscFields);

            //add order props

            $db = CSaleOrderProps::getList(array(), array('PERSON_TYPE_ID'=>1));
            while($arProp = $db ->fetch()){
                $propId = $arProp['ID'];
                unset($arProp['ID']);
                $arProp['PERSON_TYPE_ID'] = $personTypeID;
                $arProp['PROPS_GROUP_ID'] = $propsGroupId;
                $dbT = CSaleOrderProps::getList(array(), array('PERSON_TYPE_ID'=>$personTypeID, 'NAME'=>$arProp['NAME']));
                if($arAlredy = $dbT->fetch()) {
                    $newPropID = $arAlredy['ID'];
                }else{
                    $newPropID = CSaleOrderProps::Add($arProp);
                };
                if($newPropID>0){
                    if($arProp['TYPE'] == 'SELECT'){
                        $arVariant = array();
                        $db2 = CSaleOrderPropsVariant::GetList(array(), array('ORDER_PROPS_ID'=>$propId));
                        while($arVals = $db2->fetch()){
                            unset($arVals['ID']);
                            $arVals['ORDER_PROPS_ID'] = $newPropID;
                            $arVariant[] = $arVals;
                            if (!CSaleOrderPropsVariant::Add($arVals)){
                                echo 'Error vals add to Prop '.$newPropID.'<br>';
                            }
                        }
                        echo 'READY!';
                        echo '<br/><a href="http://'.$_SERVER['HTTP_HOST'].'/bitrix/admin/cat_discount_edit.php?lang=ru&ID='.$discountID.'">Here is a link to add coupons</a>';
                    }
                }else echo 'Error add Prop'.$propId.'<br>';
            }
        }
    }
}
?>

<form class="form-section" method="POST">
    <div class="form-section__content">
        <label class="form-section__input input-txt input-txt_bg_grey" data-placeholder="Размер скидки в %">
            <input type="text" name="discount" value="" size="2" class="input-txt__field" placeholder="Размер скидки в %">
            <?if(isset($arResult['ERROR']['discount'])){
                echo $arResult['ERROR_MESSAGE'];
            }?>
        </label>
        <input type="text" name="checkField" value=""  style="display: none;">
        <input type="submit" name="set" class='btn btn_size_big' value="Создать">
    </div>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>