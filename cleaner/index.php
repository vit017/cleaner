<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 26.05.14
 * Time: 15:33
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Страница клинера');
?>
<div class="container">

  <h1 class="page-title">Страница клинера</h1>

  <section class="page-blocks clearfix">
    <div class="page-blocks__item page-blocks__item_type_main">
        <?
            if($_REQUEST["BACK_URL"]) {
        ?>
            <div class="page-blocks__item-controls">
                <a class="btn btn_type_third btn_size_small" href="<?=$_REQUEST["BACK_URL"]?$_REQUEST["BACK_URL"]:'/user/history/'?>"><span class="btn__icon btn__icon_type_back"></span>Назад</a>
            </div>
        <?
            }
        ?>
        <?
        if($_REQUEST["ID"]) {
            $db = CUser::GetList($b = "ID", $o = "DESC", array("ID"=>$_REQUEST["ID"]), array('SELECT'=>array('UF_RATING', "PERSONAL_PHOTO")));
            if($sr = $db->Fetch()){
                //xmp($sr);
                $photo = CFile::GetFileArray($sr['PERSONAL_PHOTO']);
                ?>
                <div class="cleaner-profile">
                    <span class="cleaner-profile__pic">
                      <img src="<?=$photo["SRC"]?>" alt=""/>
                    </span>
                    <div class="cleaner-profile__inf">
                        <?/*$db_props = CSaleOrderPropsVariant::GetList(
                            array("SORT" => "ASC"),
                            array(
                                "PERSON_TYPE_ID" => 1,
                                "PROPS_GROUP_ID" => 9,
                                "VALUE"=>$sr["ID"]
                            )
                        );
                        if ($props = $db_props->Fetch())
                        {
                            $nickNAme = $props['NAME'];
                        }else*/
                            $nickNAme = $sr['NAME']?>
                        <h4 class="cleaner-profile__inf-name"><? echo $nickNAme ?> <?// echo $sr['LAST_NAME']; ?></h4>
	                    <?if(isset($sr["UF_RATING"])){?>
	                        <span class="rating">
		                        <?for($i=1; $i<6; $i++){?>
			                        <i class="rating__item <?=$i <= $sr["UF_RATING"]?'rating__item_active':''?>">&nbsp;</i>
			                    <?}?>
		                    </span>
	                    <?}?>
                        <p class="cleaner-profile__inf-txt">
                            <? echo $sr['PERSONAL_NOTES']; ?>
                        </p>
                    </div>
                </div>
            <?
            }


        } elseif($_REQUEST["BACK_URL"]) {
            localRedirect($_REQUEST["BACK_URL"]);
        }
        ?>
    </div>
    <div class="page-blocks__item page-blocks__item_type_aside">

      <a class="btn btn_width_full btn_responsive_true" href="/order/basket/">Заказать уборку</a>
    </div>
  </section>

</div>

<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
