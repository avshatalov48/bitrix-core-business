<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
IncludeModuleLangFile(__FILE__);

$rights = $APPLICATION->GetGroupRight("vote");
if ($rights < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$old_module_version = CVote::IsOldVersion();
$module_id = "vote";
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$options = $arDisplayOptions = [
	"USE_HTML_EDIT" => [ // html editor in admin part to edit vote or question body
		"message" => GetMessage("VOTE_USE_HTML_EDIT"),
		"field_type" => "checkbox"],
	"VOTE_COMPATIBLE_OLD_TEMPLATE" => [ // this is very old option to use old templates before component. We do not use it anymore.
		"message" => GetMessage("VOTE_COMPATIBLE"),
		"field_type" => "checkbox"],
	"VOTE_DIR" => [ //
		"message" => GetMessage("VOTE_PUBLIC_DIR"),
		"field_type" => "text"],
	"VOTE_TEMPLATE_PATH" => [ //Путь к шаблонам показа форм опросов (SV)
		"message" => GetMessage("VOTE_TEMPLATE_VOTES"),
		"field_type" => "text"],
	"VOTE_TEMPLATE_PATH_VOTE" => [ // Выбор шаблона показа результатов опроса (RV)
		"message" => GetMessage("VOTE_TEMPLATE_RESULTS_VOTE"),
		"field_type" => "text"],
	"VOTE_TEMPLATE_PATH_QUESTION" => [ //Путь к шаблонам показа результатов вопроса: (RQ)
		"message" => GetMessage("VOTE_TEMPLATE_RESULTS_QUESTION"),
		"field_type" => "text"],
	"VOTE_TEMPLATE_PATH_QUESTION_NEW" => [// Относительный путь к шаблонам показа результатов вопроса: C 4 ВЕРСИИ
		"message" => GetMessage("VOTE_TEMPLATE_RESULTS_QUESTION_NEW"),
		"field_type" => "text"]
	];
if ($request->isPost() && $request->getPost("edit_vote_options") === "Y")
{
	try
	{
		if ($rights < "W")
		{
			throw new \Bitrix\Main\AccessDeniedException();
		}
		if (!check_bitrix_sessid())
		{
			throw new \Bitrix\Main\ArgumentException("Bad sessid.");
		}
		if ($request->getPost("restore") !== null)
		{
			COption::RemoveOption($module_id);
			$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
			while($zr = $z->Fetch())
				$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
		}
		else
		{
			foreach ($options as $key => $value)
			{
				if ($request->getPost($key) !== null)
				{
					COption::SetOptionString($module_id, $key, $request->getPost($key));
				}
			}
		}
	}
	catch (\Exception $exception)
	{
		CAdminMessage::ShowMessage($exception->getMessage());
	}
}
if (COption::GetOptionString("vote", "VOTE_COMPATIBLE_OLD_TEMPLATE", "N") == "N")
{
	if (strlen(COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH")) <= 0 &&
		strlen(COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_VOTE")) <= 0 &&
		strlen(COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_QUESTION")) <= 0 &&
		strlen(COption::GetOptionString("vote", "VOTE_TEMPLATE_PATH_QUESTION_NEW")) <= 0)
	{
		unset($arDisplayOptions["VOTE_COMPATIBLE_OLD_TEMPLATE"]);
	}
	unset($arDisplayOptions["VOTE_DIR"]);
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH"]);
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH_VOTE"]);
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH_QUESTION"]);
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH_QUESTION_NEW"]);
}
elseif ($old_module_version == "Y")
{
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH_QUESTION_NEW"]);
}
else
{
	unset($arDisplayOptions["VOTE_DIR"]);
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH"]);
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH_VOTE"]);
	unset($arDisplayOptions["VOTE_TEMPLATE_PATH_QUESTION"]);
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="edit_vote_options" value="Y" />
<?
$tabControl->BeginNextTab();
	foreach($arDisplayOptions as $key => $Option)
	{
		$val = COption::GetOptionString($module_id, $key);
		$key = htmlspecialcharsbx($key);
	?>
	<tr><td valign="top" width="50%"><?=$Option["message"]?></td>
		<td valign="top" width="50%"><?
		if($Option["field_type"] == "checkbox")
		{
			?><input type="hidden" name="<?=$key?>" value="N" ><?
			?><input type="checkbox" name="<?=$key?>" id="<?=$key?>" value="Y" <?if($val=="Y") { echo" checked"; }?>><?
		}
		else
		{
			?><input type="text" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="<?=$key?>"><?
		}
		?></td>
	</tr>
	<?
	}
$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<input <?if ($rights<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("VOTE_SAVE")?>">
<input type="reset" name="reset" value="<?=GetMessage("VOTE_RESET")?>">
<input <?if ($rights<"W") echo "disabled" ?> type="submit" name="restore" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
</form>
<?
?>
