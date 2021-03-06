<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$arToolbar = array();

if($arResult["IBLOCK_PERM"] >= "U")
{
	$arToolbar[] = array(
		"TEXT"=>$arResult["IBLOCK"]["ELEMENT_ADD"],
		"TITLE"=>GetMessage("CT_BLL_TOOLBAR_ADD_ELEMENT_TITLE"),
		"LINK"=>$arResult["LIST_NEW_ELEMENT_URL"],
		"ICON"=>"btn-add-element",
	);

	if($arResult["IBLOCK_PERM"] >= "W")
	{
		$arToolbar[] = array(
			"TEXT"=>GetMessage("CT_BLL_TOOLBAR_EDIT_SECTION"),
			"TITLE"=>GetMessage("CT_BLL_TOOLBAR_EDIT_SECTION_TITLE"),
			"LINK"=>$arResult["LIST_SECTION_URL"],
			"ICON"=>"btn-edit-sections",
		);
	}
}

if($arParams["CAN_EDIT"])
{
	if(count($arToolbar))
		$arToolbar[] = array("SEPARATOR" => true);

	if($arResult["IBLOCK"]["BIZPROC"] == "Y")
	{
		$arToolbar[] = array(
			"TEXT"=>GetMessage("CT_BLL_TOOLBAR_BIZPROC"),
			"TITLE"=>GetMessage("CT_BLL_TOOLBAR_BIZPROC_TITLE"),
			"LINK"=>$arResult["BIZPROC_WORKFLOW_ADMIN_URL"],
			"ICON"=>"btn-list-bizproc",
		);
	}

	$arToolbar[] = array(
		"TEXT"=>GetMessage("CT_BLL_TOOLBAR_LIST"),
		"TITLE"=>GetMessage("CT_BLL_TOOLBAR_LIST_TITLE"),
		"LINK"=>$arResult["LIST_EDIT_URL"],
		"ICON"=>"btn-edit-list",
	);
}
?>

<?
if(count($arToolbar))
{
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS"=>$arToolbar,
		),
		$component, array("HIDE_ICONS" => "Y")
	);
}
?>

<?
if($arResult["IBLOCK_PERM"] >= "W")
{
	$sections = '&nbsp;<select name="section_to_move" size="1">';
	foreach($arResult["LIST_SECTIONS"] as $id => $name)
	{
		$sections .= '<option value="'.$id.'">'.$name.'</option>';
	}
	$sections .= '</select>&nbsp;';

	$arActions = array(
		"delete"=>true,
		"list"=>array(
			"section" => GetMessage("CT_BLL_MOVE_TO_SECTION"),
		),
		"custom_html"=>$sections,
	);
}
else
{
	$arActions = false;
}

foreach($arResult["FILTER"] as $i => $arFilter)
{
	if($arFilter["type"] == "E"):
		$FIELD_ID = $arFilter["id"];
		$arField = $arFilter["value"];
		ob_start();
		?><input type="hidden" name="<?echo $FIELD_ID?>" value=""><? //This will emulate empty input
		$control_id = $APPLICATION->IncludeComponent(
			"bitrix:main.lookup.input",
			"elements",
			array(
				"INPUT_NAME" => $FIELD_ID,
				"INPUT_NAME_STRING" => "inp_".$FIELD_ID,
				"INPUT_VALUE_STRING" => (isset($_REQUEST["inp_".$FIELD_ID])? $_REQUEST["inp_".$FIELD_ID]: ""),
				"START_TEXT" => "",
				"MULTIPLE" => "N",
				//These params will go throught ajax call to ajax.php in template
				"IBLOCK_TYPE_ID" => $arParams["~IBLOCK_TYPE_ID"],
				"IBLOCK_ID" => $arField["LINK_IBLOCK_ID"],
			), $component, array("HIDE_ICONS" => "Y")
		);
		$html = ob_get_contents();
		ob_end_clean();

		$arResult["FILTER"][$i]["type"] = "custom";
		$arResult["FILTER"][$i]["value"] = $html;
		$arResult["FILTER"][$i]["filtered"] = isset($_REQUEST["inp_".$FIELD_ID]) && strlen($_REQUEST["inp_".$FIELD_ID]);
	endif;
}

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"",
	array(
		"GRID_ID"=>$arResult["GRID_ID"],
		"HEADERS"=>$arResult["ELEMENTS_HEADERS"],
		"ROWS"=>$arResult["ELEMENTS_ROWS"],
		"ACTIONS"=>$arActions,
		"NAV_OBJECT"=>$arResult["NAV_OBJECT"],
		"SORT"=>$arResult["SORT"],
		"FILTER"=>$arResult["FILTER"],
		"FOOTER" => array(
			array("title" => GetMessage("CT_BLL_SELECTED"), "value" => $arResult["NAV_OBJECT"]->SelectedRowsCount())
		),
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP"=>"N",
	),
	$component, array("HIDE_ICONS" => "Y")
);?>

