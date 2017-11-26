<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arResult['INITIAL_BUNDLES']['a'] = array(); // force PhpToJSObject to map this to {}, not to []
foreach($arResult['PATH'] as $node)
{
	$fNode = array(
		'DISPLAY' => $node['NAME'],
		'VALUE' => intval($node['ID']),
		'CODE' => $node['CODE'],
		'IS_PARENT' => $node['CHILD_CNT'] > 0,
		'TYPE_ID' => intval($node['TYPE_ID'])
	);

	if($node['IS_UNCHOOSABLE'])
		$fNode['IS_UNCHOOSABLE'] = true;

	$arResult['INITIAL_BUNDLES'][intval($node['PARENT_ID'])][] = $fNode;
}

$arResult['RANDOM_TAG'] = rand(999, 99999);
$arResult['ADMIN_MODE'] = ADMIN_SECTION == 1;

// trunk
$arResult['ROOT_NODE'] = 0;
if(is_array($arResult['TREE_TRUNK']) && !empty($arResult['TREE_TRUNK']))
{
	$names = array();
	foreach($arResult['TREE_TRUNK'] as $item)
	{
		$names[] = $item['NAME'];
		$arResult['ROOT_NODE'] = $item['ID'];
	}

	$arResult['TRUNK_NAMES'] = $names;
}

// modes
$modes = array();
if(ADMIN_SECTION == 1 || $arParams['ADMIN_MODE'] == 'Y')
	$modes[] = 'admin';

foreach($modes as &$mode)
	$mode = 'bx-'.$mode.'-mode';

$arResult['MODE_CLASSES'] = implode(' ', $modes);