<?
use Bitrix\Main\UrlRewriter;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/urlrewrite_edit.php");

if(!$USER->CanDoOperation('edit_php') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$aMsg = array();
$message = null;
$bVarsFromForm = false;

if (StrLen($site_id) <= 0)
	LocalRedirect("/bitrix/admin/urlrewrite_list.php?lang=".LANG);

if ($REQUEST_METHOD=="POST" && strlen($Update)>0 && $isAdmin && check_bitrix_sessid())
{
	if (StrLen($CONDITION) <= 0)
		$aMsg[] = array("id"=>"CONDITION", "text"=>GetMessage("MURL_NO_USL"));

	if(empty($aMsg))
	{
		if ($CONDITION_OLD != $CONDITION)
		{
			$arResult = UrlRewriter::getList($site_id, array("CONDITION" => $CONDITION));
			if (count($arResult) > 0)
				$aMsg[] = array("id"=>"CONDITION", "text"=>str_replace("#CONDITION#", htmlspecialcharsbx($CONDITION), GetMessage("MURL_DUPL_CONDITION")));
		}
	}

	if (empty($aMsg))
	{
		if (StrLen($CONDITION_OLD) > 0)
		{
			UrlRewriter::update(
				$site_id,
				array("CONDITION" => $CONDITION_OLD),
				array(
					"CONDITION" => $CONDITION,
					"ID" => $ID,
					"PATH" => $FILE_PATH,
					"RULE" => $RULE
				)
			);
		}
		else
		{
			UrlRewriter::add(
				$site_id,
				array(
					"CONDITION" => $CONDITION,
					"ID" => $ID,
					"PATH" => $FILE_PATH,
					"RULE" => $RULE
				)
			);
		}
	}

	if (empty($aMsg))
	{
		if (strlen($apply) <= 0)
			LocalRedirect("/bitrix/admin/urlrewrite_list.php?lang=".LANG."&filter_site_id=".UrlEncode($site_id)."&".GetFilterParams("filter_", false));
	}
	else
	{
		$message = new CAdminMessage(GetMessage("SAE_ERROR"), new CAdminException($aMsg));
		$bVarsFromForm = true;
	}
}

if (StrLen($CONDITION) > 0)
	$APPLICATION->SetTitle(GetMessage("MURL_EDIT"));
else
	$APPLICATION->SetTitle(GetMessage("MURL_ADD"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arResultList = UrlRewriter::getList($site_id, array("CONDITION" => $CONDITION));

if (count($arResultList) <= 0)
{
	unset($CONDITION);
	$arResult = array();
	$str_CONDITION_OLD = "";
	$str_CONDITION = "";
	$str_ID = "";
	$str_FILE_PATH = "";
	$str_RULE = "";
}
else
{
	$arResult = $arResultList[0];
	$str_CONDITION_OLD = htmlspecialcharsbx($arResult["CONDITION"]);
	$str_CONDITION = htmlspecialcharsbx($arResult["CONDITION"]);
	$str_ID = htmlspecialcharsbx($arResult["ID"]);
	$str_FILE_PATH = htmlspecialcharsbx($arResult["PATH"]);
	$str_RULE = htmlspecialcharsbx($arResult["RULE"]);
}

if ($bVarsFromForm)
{
	$str_CONDITION_OLD = htmlspecialcharsbx($CONDITION_OLD);
	$str_CONDITION = htmlspecialcharsbx($CONDITION);
	$str_ID = htmlspecialcharsbx($ID);
	$str_FILE_PATH = htmlspecialcharsbx($FILE_PATH);
	$str_RULE = htmlspecialcharsbx($RULE);
}
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("MURL_2_LIST"),
		"LINK" => "/bitrix/admin/urlrewrite_list.php?lang=".LANG."&filter_site_id=".UrlEncode($site_id)."&".GetFilterParams("filter_", false),
		"ICON"	=> "btn_list",
		"TITLE" => GetMessage("MURL_2_LIST_ALT"),
	)
);

if (StrLen($CONDITION) > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("MURL_ACT_ADD"),
		"LINK" => "/bitrix/admin/urlrewrite_edit.php?lang=".LANG."&site_id=".UrlEncode($site_id)."&".GetFilterParams("filter_", false),
		"ICON"	=> "btn_new",
		"TITLE" => GetMessage("MURL_ACT_ADD_ALT"),
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("MURL_ACT_DEL"),
		"LINK" => "javascript:if(confirm('".GetMessage("MURL_ACT_DEL_CONF")."')) window.location='/bitrix/admin/urlrewrite_list.php?ID=".urlencode(urlencode($CONDITION))."&filter_site_id=".urlencode(urlencode($site_id))."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"WARNING" => "Y",
		"ICON"	=> "btn_delete"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message)
	echo $message->Show();
?>


<form method="POST" action="<?= $APPLICATION->GetCurPage()?>?" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?= LANG ?>">
<input type="hidden" name="site_id" value="<?= htmlspecialcharsbx($site_id) ?>">
<input type="hidden" name="CONDITION_OLD" value="<?= $str_CONDITION_OLD ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MURL_TAB"), "TITLE" => GetMessage("MURL_TAB_ALT"))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<tr class="adm-detail-required-field">
		<td width="40%"><?= GetMessage("MURL_USL") ?>:</td>
		<td width="60%">
			<input type="text" name="CONDITION" size="50" maxlength="250" value="<?= $str_CONDITION ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("MURL_COMPONENT") ?>:</td>
		<td>
			<input type="text" name="ID" size="50" maxlength="250" value="<?= $str_ID ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("MURL_FILE") ?>:</td>
		<td>
			<input type="text" name="FILE_PATH" size="50" maxlength="250" value="<?= $str_FILE_PATH ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("MURL_RULE") ?>:</td>
		<td>
			<input type="text" name="RULE" size="50" maxlength="250" value="<?= $str_RULE ?>">
		</td>
	</tr>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
	array(
		"disabled" => !$isAdmin,
		"back_url" => "/bitrix/admin/urlrewrite_list.php?lang=".LANG."&filter_site_id=".UrlEncode($site_id)."&".GetFilterParams("filter_", false)
	)
);
?>
<?
$tabControl->End();
?>
</form>
<?
$tabControl->ShowWarnings("form1", $message);
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>