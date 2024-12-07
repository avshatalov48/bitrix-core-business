<?php
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/learning/options.php");

$APPLICATION->AddHeadScript('/bitrix/js/learning/rights_edit.js');

// Ensure, that data in database converted to 11.5.0 version of module
if ( ! CModule::IncludeModule('learning') )
	return (false);

$LEARNING_RIGHT = 'A';
if (CLearnAccess::IsLoggedUserCanAccessModuleSettings())
	$LEARNING_RIGHT = 'W';

if($LEARNING_RIGHT < "W")
	return;

$oAccess = CLearnAccess::GetInstance($USER->GetID());

$strWarning = "";

$arAllOptions =
	Array(
		Array("use_htmledit", GetMessage("LEARNING_OPTIONS_USE_HTMLEDIT"), "Y", Array("checkbox", "Y")),
		Array("menu_max_courses", GetMessage("LEARNING_OPTIONS_MENU_MAX_COURSES"), "10", Array("text", 10)),
	);

//Restore defaults
if ($LEARNING_RIGHT == "W" && $_SERVER["REQUEST_METHOD"]=="GET" && $RestoreDefaults <> '' && check_bitrix_sessid())
{
	COption::RemoveOption("learning");
}

//Save options
if($_SERVER["REQUEST_METHOD"]=="POST" && $Update <> '' && $LEARNING_RIGHT == "W" && check_bitrix_sessid())
{
	// Work with permissions
	if (CLearnAccess::IsLoggedUserCanAccessModuleSettings() && isset($_POST['BASE_RIGHTS']))
	{
		// Process permissions
		$arAccessSymbols = array();
		$arTaskIds = array();
		foreach ($_POST['BASE_RIGHTS'] as $key => $arData)
		{
			if (isset($arData['GROUP_CODE']))
				$arAccessSymbols[] = $arData['GROUP_CODE'];
			elseif (isset($arData['TASK_ID']))
				$arTaskIds[] = $arData['TASK_ID'];
		}
		if (count($arAccessSymbols) !== count($arTaskIds))
			throw new LearnException('', LearnException::EXC_ERR_ALL_LOGIC | LearnException::EXC_ERR_ALL_GIVEUP);

		$arPermPairs = array_combine($arAccessSymbols, $arTaskIds);

		// Save permissions
		$oAccess->SetBasePermissions ($arPermPairs);

		unset ($oAccess);
	}

	for($i=0; $i<count($arAllOptions); $i++)
	{
		$name=$arAllOptions[$i][0];
		$val=$$name;
		if($arAllOptions[$i][3][0]=="checkbox" && $val!="Y")
			$val="N";
		COption::SetOptionString("learning", $name, $val, $arAllOptions[$i][1]);
	}

	$arPaths = array();
	$dbPaths = CSitePath::GetList();
	while ($arPath = $dbPaths->Fetch())
	{
		$arPaths[$arPath["SITE_ID"]][$arPath["TYPE"]] = $arPath;
	}

	$arType = array("C", "H", "L");
	/*
	"C" - Course,
	"H" - cHapter,
	"L" - Lesson,
	*/
	$affectedRows = 0;
	$dbSites = CSite::GetList('', '', array("ACTIVE" => "Y"));
	while ($arSite = $dbSites->Fetch())
	{
		//BXClearCache(True, "/".$arSite["LID"]."/blog/");

		foreach($arType as $type)
		{

			if (intval($arPaths[$arSite["LID"]][$type])>0)
			{
				if (${"SITE_PATH_".$arSite["LID"]."_".$type} <> '')
				{
					if ($arPaths[$arSite["LID"]][$type]["PATH"] != ${"SITE_PATH_".$arSite["LID"]."_".$type})
						$affectedRows++;
					CSitePath::Update($arPaths[$arSite["LID"]][$type]["ID"], array("PATH" => ${"SITE_PATH_".$arSite["LID"]."_".$type}, "TYPE"=>$type));
				}
				else
				{
					CSitePath::Delete($arPaths[$arSite["LID"]][$type]["ID"]);
					$affectedRows++;
				}
			}
			else
			{
				CSitePath::Add(
					array(
						"SITE_ID" => $arSite["LID"],
						"PATH" => ${"SITE_PATH_".$arSite["LID"]."_".$type},
						"TYPE" => $type
					)
				);
				$affectedRows++;
			}
		}
		unset($arPaths[$arSite["LID"]]);

	}

	if ($affectedRows && IsModuleInstalled('search') && CModule::IncludeModule("search"))
	{
		CSearch::ReindexModule("learning");
	}

	foreach ($arPaths as $key)
		foreach($key as $val)
			CSitePath::Delete($val);
}
?>

<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&amp;lang=<?echo LANG?>">
<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_TAB_SET"), "ICON" => "learning_settings", "TITLE" => GetMessage("LEARNING_TAB_SET_ALT")),
	array("DIV" => "edit2", "TAB" => GetMessage("LEARNING_TAB_RIGHTS"), "ICON" => "learning_settings", "TITLE" => GetMessage("LEARNING_TAB_RIGHTS_ALT")),
	);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<?
	for($i=0; $i<count($arAllOptions); $i++):
		$Option = $arAllOptions[$i];
		$val = COption::GetOptionString("learning", $Option[0], $Option[2]);
		$type = $Option[3];
	?>
		<tr>
			<td width="40%"><?echo $Option[1]?><?php if($type[0]!="checkbox") echo ':'; ?></td>
			<td width="60%">
					<?if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
					<?elseif($type[0]=="text"):?>
						<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>">
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
					<?endif?>

			</td>
		</tr>
	<?endfor;?>

	<tr class="heading">
		<td colspan="2"><?=GetMessage("LEARNING_SITE_PATH")?></td>
	</tr>
	<?
	$arPaths = array();
	$dbPaths = CSitePath::GetList();
	while ($arPath = $dbPaths->Fetch())
		$arPaths[$arPath["SITE_ID"]][$arPath["TYPE"]] = $arPath["PATH"];

	$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
	while ($arSite = $dbSites->Fetch())
	{
		?>
		<tr>
			<td valign="top" colspan="2" align="center"><?= str_replace("#SITE#", $arSite["LID"], GetMessage("LEARNING_SITE_PATH_SITE")) ?>:</td>
		</tr>
		<tr>
			<td align="right"><?=GetMessage("LEARNING_SITE_PATH_SITE_COURSE")?>:</td>
			<td><input type="text" size="40" value="<?echo htmlspecialcharsbx($arPaths[$arSite["LID"]]["C"])?>" name="SITE_PATH_<?= $arSite["LID"] ?>_C"></td>
		</tr>
		<tr>
			<td align="right"><?=GetMessage("LEARNING_SITE_PATH_SITE_CHAPTER")?>:</td>
			<td><input type="text" size="40" value="<?echo htmlspecialcharsbx($arPaths[$arSite["LID"]]["H"])?>" name="SITE_PATH_<?= $arSite["LID"] ?>_H"></td>
		</tr>
		<tr>
			<td align="right"><?=GetMessage("LEARNING_SITE_PATH_SITE_LESSON")?>:</td>
			<td><input type="text" size="40" value="<?echo htmlspecialcharsbx($arPaths[$arSite["LID"]]["L"])?>" name="SITE_PATH_<?= $arSite["LID"] ?>_L"></td>
		</tr>
		<?
	}
	?>
	<tr>
		<td valign="top" align="center" colspan="2"><?=GetMessage("LEARNING_PATH_EXAMPLE")?>:</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table cellspacing="2" width="0%">
			<tr>
				<td align="right"><?=GetMessage("LEARNING_SITE_PATH_SITE_COURSE")?>:</td>
				<td>/learning/course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y</td>
			</tr>
			<tr>
				<td align="right"><?=GetMessage("LEARNING_SITE_PATH_SITE_CHAPTER")?>:</td>
				<td>/learning/course/index.php?COURSE_ID=#COURSE_ID#&CHAPTER_ID=#CHAPTER_ID#</td>
			</tr>
			<tr>
				<td align="right"><?=GetMessage("LEARNING_SITE_PATH_SITE_LESSON")?>:</td>
				<td>/learning/course/index.php?COURSE_ID=#COURSE_ID#&LESSON_ID=#LESSON_ID#</td>
			</tr>
			</table>
		</td>
	</tr>

<?php

if (CLearnAccess::IsLoggedUserCanAccessModuleSettings())
{
	$tabControl->BeginNextTab();
	CLearnRenderRightsEdit::RenderBaseRightsTab ($USER->GetID(), 'BASE_RIGHTS');
}

?>

<?$tabControl->Buttons();?>
<script>
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)?>&<?=bitrix_sessid_get()?>";
}
</script>
<div align="left">
	<input type="hidden" name="Update" value="Y">
	<input type="submit" <?if ($LEARNING_RIGHT<"W") echo "disabled" ?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
	<input type="reset" <?if ($LEARNING_RIGHT<"W") echo "disabled" ?> name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
	<input type="button" <?if ($LEARNING_RIGHT<"W") echo "disabled" ?>  type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
</div>
<?$tabControl->End();?>
<?=bitrix_sessid_post();?>
</form>


<?php
