<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (strlen($arResult["FatalErrorMessage"]) > 0)
{
	?>
	<span class='errortext'><?= $arResult["FatalErrorMessage"] ?></span><br /><br />
	<?
}
else
{
	if (strlen($arResult["ErrorMessage"]) > 0)
	{
		?>
		<span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br /><br />
		<?
	}

	if ($arResult["ShowMode"] == "StartWorkflowSuccess")
	{
		?>
		<?= GetMessage("BPWC_WRCT_SUCCESS") ?>
		<?
	}
	elseif ($arResult["ShowMode"] == "StartWorkflowError")
	{
		?>
		<?= GetMessage("BPWC_WRCT_ERROR") ?>
		<?
	}
	elseif ($arResult["ShowMode"] == "WorkflowParameters")
	{
		$arButtons = array(
			array(
				"TEXT"=>GetMessage("BPWC_WRCT_2LIST"),
				"TITLE"=>GetMessage("BPWC_WRCT_2LIST"),
				"LINK"=>$arResult["PATH_TO_LIST"],
				"ICON"=>"btn-list",
			),
		);
		$APPLICATION->IncludeComponent(
			"bitrix:main.interface.toolbar",
			"",
			array(
				"BUTTONS" => $arButtons
			),
			$component
		);
		?>
		<br>

		<form method="post" name="start_workflow_form1" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data">
			<input type="hidden" name="back_url" value="<?= htmlspecialchars($arResult["BackUrl"]) ?>">
			<?= bitrix_sessid_post() ?>
			<table class="bpwiz1-view-form data-table" cellpadding="0" cellspacing="0">
			<tr>
				<th colspan="2"><?= GetMessage("BPWC_WRCT_SUBTITLE") ?></th>
			</tr>
			<tr>
				<td align="right" width="40%"><?= GetMessage("BPWC_WRCT_NAME") ?>:</td>
				<td width="60%">
					<?= $arResult["TEMPLATE"]["NAME"] ?>
				</td>
			</tr>
			<?if ($arResult["TEMPLATE"]["DESCRIPTION"] != ''):?>
				<tr>
					<td align="right" width="40%"><?= GetMessage("BPWC_WRCT_DESCR") ?>:</td>
					<td width="60%">
						<?= $arResult["TEMPLATE"]["DESCRIPTION"] ?>
					</td>
				</tr>
			<?endif?>
			<?
			foreach ($arResult["TEMPLATE"]["PARAMETERS"] as $parameterKey => $arParameter)
			{
				if ($parameterKey == "TargetUser")
					continue;
				?>
				<tr>
					<td align="right" width="40%" valign="top"><?= $arParameter["Required"] ? "<span style=\"color:red\">*</span> " : ""?><?= htmlspecialchars($arParameter["Name"]) ?>:<?if (strlen($arParameter["Description"]) > 0) echo "<br /><small>".htmlspecialchars($arParameter["Description"])."</small><br />";?></td>
					<td width="60%" valign="top"><?
						echo $arResult["DocumentService"]->GetGUIFieldEdit(
							array("bizproc", "CBPVirtualDocument", "type_".$arParams["BLOCK_ID"]),
							"start_workflow_form1",
							$parameterKey,
							$arResult["ParametersValues"][$parameterKey],
							$arParameter,
							false
						);
					?></td>
				</tr>
				<?
			}
			?>
			</table>
			<input type="submit" name="DoStartParamWorkflow" value="<?= strlen($arResult["CreateTitle"]) > 0 ? $arResult["CreateTitle"] : GetMessage("BPWC_WRCT_SAVE") ?>" />
			<input type="submit" name="CancelStartParamWorkflow" value="<?= GetMessage("BPWC_WRCT_CANCEL") ?>" />
		</form>
		<?
	}
	elseif ($arResult["ShowMode"] == "SelectWorkflow")
	{
		if (count($arResult["TEMPLATES"]) > 0)
		{
			foreach ($arResult["TEMPLATES"] as $workflowTemplateId => $arWorkflowTemplate)
			{
				?>
				<tr>
					<td colspan="2">
						<a href="<?= $arWorkflowTemplate["URL"] ?>"><?= $arWorkflowTemplate["NAME"] ?></a><?= strlen($arWorkflowTemplate["DESCRIPTION"]) > 0 ? ":" : "" ?>
						<?= $arWorkflowTemplate["DESCRIPTION"] ?>
					</td>
				</tr>
				<?
			}
		}
		else
		{
			?>
			<tr>
				<td colspan="2"><?= GetMessage("BPABS_NO_TEMPLATES") ?></td>
			</tr>
			<?
		}
	}
}
?>