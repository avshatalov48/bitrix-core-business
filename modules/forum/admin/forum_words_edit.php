<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Main;
use \Bitrix\Forum;
Main\Loader::includeModule("forum");
$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
$request = Main\Context::getCurrent()->getRequest();
$errorCollection = new Main\ErrorCollection();
$ID = intval($request->get("ID"));
$DICTIONARY_ID = intval($request->get("DICTIONARY_ID"));
$bVarsFromForm = false;

$arFields = [
	"DICTIONARY_ID" => $DICTIONARY_ID,
	"WORDS" => "",
	"PATTERN_CREATE" => "WORD",
	"DESCRIPTION" => "",
	"USE_IT" => "Y"
];
$filter = null;
try {
	if ($ID > 0)
	{
		$filter = new Forum\BadWords\Filter($ID);
		$arFields["DICTIONARY_ID"] = $filter["DICTIONARY_ID"];
		$arFields["WORDS"] = $filter["WORDS"];
		$arFields["PATTERN_CREATE"] = $filter["PATTERN_CREATE"];
		$arFields["DESCRIPTION"] = $filter["DESCRIPTION"];
		$arFields["USE_IT"] = $filter["USE_IT"];
	}
}
catch (Exception $e)
{
	$errorCollection->add([new Main\Error($e->getMessage())]);
}

/*******************************************************************/
if ($request->isPost() && check_bitrix_sessid() && $forumPermissions >= "W")
{
	$arFields["WORDS"] = $request->getPost("WORDS");
	$arFields["PATTERN_CREATE"] = $request->getPost("PATTERN_CREATE");
	$arFields["REPLACEMENT"] = trim($request->getPost("REPLACEMENT"));
	$arFields["DESCRIPTION"] = trim($request->getPost("DESCRIPTION"));
	$arFields["USE_IT"] = $request->getPost("USE_IT") === "Y" ? "Y" : "N";

	if (empty($arFields["PATTERN_CREATE"]))
	{
		$errorCollection->add([new Main\Error(GetMessage("FLTR_NOT_ACTION"))]);
	}
	if (trim($arFields["WORDS"]) == '')
	{
		$errorCollection->add([new Main\Error(GetMessage("FLTR_NOT_WORDS"))]);
	}

	if ($errorCollection->isEmpty())
	{
		if ($filter instanceof Forum\BadWords\Filter)
		{
			$result = $filter->update($arFields);
		}
		else
		{
			$result = Forum\BadWords\Filter::add($arFields);
		}
		if ($result->isSuccess())
		{
			$url = "forum_words.php?DICTIONARY_ID={$arFields["DICTIONARY_ID"]}&lang=".LANG;
			if ($request->getPost("Update"))
			{
				$id = $result->getId();
				$url = "forum_words_edit.php?DICTIONARY_ID={$arFields["DICTIONARY_ID"]}&ID={$id}&lang=".LANG;
			}
			LocalRedirect($url);
		}
		else
		{
			$errorCollection->add($result->getErrors());
		}
	}
}
$sDocTitle = ($ID > 0) ? str_replace("#ID#", $ID, GetMessage("FLTR_EDIT")) : GetMessage("FLTR_NEW");
$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*******************************************************************/
$aMenu = array(
	array(
		"TEXT" => GetMessage("FLTR_LIST"),
		"LINK" => "/bitrix/admin/forum_words.php?DICTIONARY_ID={$arFields["DICTIONARY_ID"]}&lang=".LANG,
		"ICON" => "btn_list",
	)
);
if ($ID > 0 && $forumPermissions >= "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$aMenu[] = array(
		"TEXT" => GetMessage("FLTR_NEW"),
		"LINK" => "/bitrix/admin/forum_words_edit.php?DICTIONARY_ID={$arFields["DICTIONARY_ID"]}&lang=".LANG,
		"ICON" => "btn_new",
	);
	$aMenu[] = array(
		"TEXT" => GetMessage("FLTR_DEL"),
		"LINK" => "javascript:if(confirm('".GetMessage("FLTR_DEL_CONFIRM")."')) window.location='/bitrix/admin/forum_words.php?DICTIONARY_ID={$arFields["DICTIONARY_ID"]}&lang=".LANG."&action_button=delete&ID[]=".$ID."&".bitrix_sessid_get()."';",
		"ICON" => "btn_delete",
	);
}

(new CAdminContextMenu($aMenu))->Show();

if (!$errorCollection->isEmpty())
{
	$message = [];
	foreach ($errorCollection->getValues() as $error)
	{
		$message[] = $error->getMessage();
	}
	\CAdminMessage::ShowMessage(implode("<br>", $message));
}

/*******************************************************************/
?><form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="forum_edit">
	<input type="hidden" name="Update" value="Y"><input type="hidden" name="lang" value="<?=LANG ?>">
	<input type="hidden" name="ID" value="<?=$ID ?>">
	<input type="hidden" name="DICTIONARY_ID" value="<?=htmlspecialcharsbx($arFields["DICTIONARY_ID"])?>" />
	<?=bitrix_sessid_post()?><?
	$tabControl = new CAdminTabControl("tabControl", array(array("DIV" => "edit", "TAB" => $sDocTitle, "ICON" => "forum", "TITLE" => "")));
	$tabControl->Begin();
	$tabControl->BeginNextTab();
?>
<tr class="adm-detail-required-field">
	<td width="40%"><?=GetMessage("FLTR_SEARCH")?>:</td>
	<td width="60%"><input type="text" name="WORDS" maxlength="255" value="<?=htmlspecialcharsbx($arFields["WORDS"])?>"></td>
</tr>
<tr>
	<td><?=GetMessage("FLTR_USE_IT")?>: </td>
	<td><input type="checkbox" name="USE_IT" value="Y" <?=($arFields["USE_IT"] == "Y" ? "checked" : "")?>></td>
</tr>
<tr>
	<td><?=GetMessage("FLTR_SEARCH_WHAT")?>:</td><td>
	<?
	$arr = array(
		"reference" => array(
			GetMessage("FLTR_SEARCH_0"),
			GetMessage("FLTR_SEARCH_1"),
			GetMessage("FLTR_SEARCH_2"),
		),
		"reference_id" => array(
			"WORDS",
			"TRNSL",
			"PTTRN",
		)
	);
	echo SelectBoxFromArray("PATTERN_CREATE", $arr, $arFields["PATTERN_CREATE"], "", "");
	?></td>
</tr>
<tr><td><?=GetMessage("FLTR_REPLACEMENT")?>:</td>
	<td><input type="text" name="REPLACEMENT" maxlength="255"  value="<?=isset($arFields["REPLACEMENT"]) ? htmlspecialcharsbx($arFields["REPLACEMENT"]) : null?>"></td></tr>
<tr class="heading">
	<td colspan="2"><?=GetMessage("FLTR_DESCRIPTION")?>:</td>
</tr>
<tr valign="top">
	<td colspan="2" align="center">
		<textarea style="width:60%; height:150px;" name="DESCRIPTION" wrap="VIRTUAL"><?=isset($arFields["DESCRIPTION"]) ? htmlspecialcharsbx($arFields["DESCRIPTION"]) : null?></textarea>
	</td>
</tr>
<?$tabControl->EndTab();?>
<?$tabControl->Buttons(
		array(
				"disabled" => ($forumPermissions < "W"),
				"back_url" => "/bitrix/admin/forum_words.php?DICTIONARY_ID={$arFields["DICTIONARY_ID"]}&lang=".LANG
			)
	);?>
<?$tabControl->End();?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
