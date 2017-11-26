<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$frame = $this->createFrame()->begin();
?>
<?if($arResult["region"] && $arResult["city"]){?>
<span class="notetext"><?echo $arResult["region"]?><?if($arResult["city"] != $arResult["region"]) echo ", ".$arResult["city"]?></span>
<?}?>
<?$frame->beginStub();?>
<?$frame->end();?>
