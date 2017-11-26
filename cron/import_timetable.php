<?php
/**
 * Created by PhpStorm.
 * User: katya
 * Date: 15.07.14
 * Time: 19:29
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<form name="form" method="POST" enctype="multipart/form-data">
	<input type="file" name="import" value=""><br/>
	<input type="submit" name="save">
</form>

<?if($_REQUEST['save']){

	$_FILES['import']['MODULE_ID'] = 'iblock';
	$file = CFile::SaveFile($_FILES['import'], '');
	$arFile = CFile::getFileArray($file);
	$fileCSV = file($_SERVER["DOCUMENT_ROOT"].$arFile['SRC']);
	array_shift($fileCSV);

	foreach($fileCSV as $i=>$string){
		$arDateTimeUser = preg_split('[,]', $string);
		$arDateTimeUser[2] = trim($arDateTimeUser[2]);
		$arUserName[$arDateTimeUser[2]][] = $arDateTimeUser[0];

		//user[date]=>time
		//xmp($arDateTimeUser);
		if($arDateTimeUser[1]=='8')
			$arDateTimeUser[1] = '08';
		$arUserTt[$arDateTimeUser[2]][$arDateTimeUser[0]][] = $arDateTimeUser[1];
	}
	CModule::IncludeModule('iblock');
	CModule::IncludeModule('catalog');
	$dbC = CIBlockElement::getList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_calendar, 'ACTIVE'=>'Y'), false, false, array('PROPERTY_DATE', 'PROPERTY_TIME', 'ID'));
	while($elC = $dbC->Fetch()){
		$arCalendar[$elC['PROPERTY_DATE_VALUE']][$elC['PROPERTY_TIME_VALUE']] = $elC['ID'];
	}
	foreach($arUserTt as $user=>$arDate){
		$arIDs = array();
		//xmp($arDate);

		foreach($arDate as $date=>$arTime){
            $date = new dateTime($date);
			if(isset($arCalendar[$date->format('d.m.Y')])){
				foreach($arTime as $time)
					if(isset($arCalendar[$date->format('d.m.Y')][$time])){
						$arIDs[] = $arCalendar[$date->format('d.m.Y')][$time];

				}
			}
		}

		if(!empty($arIDs)){
			$name = array_shift($arUserName[$user]).'-'.array_pop($arUserName[$user]);
			$el = new CIBlockElement();
			$dbFEl = CIBlockElement::getList(array(), array('IBLOCK_ID'=>bhSettings::$IBlock_schedule, 'NAME'=>$name, 'PROPERTY_USER'=>$user));
			if($elementF = $dbFEl->Fetch()){
				$elementF['ID'];
				$arFields = array('PROPERTY_VALUES'=>array('USER' =>$user, 'DATETIME'=>$arIDs));
				if(!$el->Update($elementF['ID'], $arFields)){
					echo $el->LAST_ERROR;
				};
			}
			else{
				$arFields = array('NAME'=>$name,'IBLOCK_ID'=>bhSettings::$IBlock_schedule, 'ACTIVE'=>'Y',
				'PROPERTY_VALUES'=>array('USER' =>trim($user), 'DATETIME'=>$arIDs));
				if(!$el->Add($arFields)){
					echo $el->LAST_ERROR;
				};
			}
		}
	}
	unset($_REQUEST);
}?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>