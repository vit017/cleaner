<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?ShowError($arResult["ERROR_MESSAGE"]);?>

<?if ($arResult["QUESTIONS_COUNT"] > 0):?>


<div class="learn-question-tabs">
	<?=GetMessage("LEARNING_QUESTION_S");?>&nbsp;
	<?for ($tabIndex = 1; $tabIndex <= $arResult["QUESTIONS_COUNT"]; $tabIndex++):?>
		<span class="learn-tab" onClick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.SelectTab(<?=$tabIndex?>)" id="learn_tab_<?=$arResult["LESSON"]["ID"]?>_<?=$tabIndex?>">&nbsp;<?=$tabIndex?>&nbsp;</span>
	<?endfor?>
</div>

<br />

<?foreach ($arResult["QUESTIONS"] as $index => $arQuestion):?>
<div id="learn_question_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>" style="display:none;">
	<div class="learn-question-cloud">
		<div class="learn-question-number"><?=GetMessage("LEARNING_QUESTION_S")?><br /><?=($index+1)?> <?=GetMessage("LEARNING_QUESTION_FROM");?> <?=$arResult["QUESTIONS_COUNT"]?></div>
		<div class="learn-question-name"><?=$arQuestion["NAME"]?>
		<?if (strlen($arQuestion["DESCRIPTION"]) > 0):?>
			<br /><br /><?=$arQuestion["DESCRIPTION"]?>
		<?endif?>
		<?if ($arQuestion["FILE"] !== false):?>
			<br /><br /><img src="<?=$arQuestion["FILE"]["SRC"]?>" width="<?=$arQuestion["FILE"]["WIDTH"]?>" height="<?=$arQuestion["FILE"]["HEIGHT"]?>" />
		<?endif?>
		</div>
	</div>

	<br /><strong><?=GetMessage("LEARNING_SELECT_ANSWER");?>:</strong>

	<form name="form_self_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>" onSubmit="return false;" action="">
	<?$answerIndex = 0; foreach ($arQuestion["ANSWERS"] as $arAnswer):?>
		<?if ($arQuestion["QUESTION_TYPE"] == "M"):?>
			<div class="learn-answer" id="correct_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>_<?=$answerIndex?>"></div>
			<label><input type="checkbox" name="answer[]" onClick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.OnChangeAnswer();" />&nbsp;<?=$arAnswer["ANSWER"]?></label>
		<?else:?>
			<div class="learn-answer" id="correct_<?=$arResult["LESSON"]["ID"]?>_<?=($index+1)?>_<?=$answerIndex?>"></div>
			<label><input type="radio" name="answer" onClick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.OnChangeAnswer();" />&nbsp;<?=$arAnswer["ANSWER"]?></label>
		<?endif?>
		<input type="hidden" name="right_<?=$answerIndex?>" value="<?=$arAnswer["CORRECT"]?>" /><br clear="all" />
	<?$answerIndex++;endforeach?>
	<p><input type="submit" name="submit" disabled="disabled" value="<?=GetMessage("LEARNING_SUBMIT_ANSWER");?>" onclick="LearnTab_<?=$arResult["LESSON"]["ID"]?>.CheckAnswer();"></p>
	</form>
</div>
<?endforeach?>

<script type="text/javascript">var LearnTab_<?=$arResult["LESSON"]["ID"]?> = new LearnTabs(<?=$arResult["LESSON"]["ID"]?>, 1);</script>
<noscript><?=GetMessage("LEARNING_ENABLE_JAVASCRIPT");?></noscript>

<?endif?>