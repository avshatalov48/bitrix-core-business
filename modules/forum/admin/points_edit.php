<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule("forum");
ClearVars();
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$ID = intval($ID);
$message = false;
$arSysLangs = array();
$arSysLangNames = array();
$db_lang = CLangAdmin::GetList(($b="sort"), ($o="asc"));
$langCount = 0;
while ($arLang = $db_lang->Fetch())
{
	$arSysLangs[$langCount] = $arLang["LID"];
	$arSysLangNames[$langCount] = htmlspecialcharsbx($arLang["NAME"]);
	$langCount++;
}


$bInitVars = false;
if ($REQUEST_METHOD=="POST" && $forumPermissions=="W" && (!empty($save) || !empty($apply)) && check_bitrix_sessid())
{
	$arFields = array(
		"MIN_POINTS" => $MIN_POINTS,
		"CODE" => $CODE);
		
	if (isset($VOTES))
		$arFields["VOTES"] = intval($VOTES);
		
	for ($i = 0; $i<count($arSysLangs); $i++)
	{
		if (!empty(${"NAME_".$arSysLangs[$i]}))
		{
			$arFields["LANG"][] = array(
				"LID" => $arSysLangs[$i],
				"NAME" => ${"NAME_".$arSysLangs[$i]});
		}
	}

	$res = 0;
	if ($ID>0)
		$res = CForumPoints::Update($ID, $arFields);
	else
		$res = CForumPoints::Add($arFields);
	if (intval($res) <= 0 && $e = $GLOBALS["APPLICATION"]->GetException())
	{
		$message = new CAdminMessage(($ID > 0 ? GetMessage("FORUM_PE_ERROR_UPDATE") : GetMessage("FORUM_PE_ERROR_ADD")), $e);
		$bInitVars = True;
	}
	elseif ($save <> '')
		LocalRedirect("forum_points.php?lang=".LANG."&".GetFilterParams("filter_", false));
	else 
		$ID = $res;
}

if ($ID>0)
{
	$db_points = CForumPoints::GetList(array(), array("ID" => $ID));
	$db_points->ExtractFields("str_", False);
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_forum_points", "", "str_");
}

$sDocTitle = ($ID>0) ? str_replace("#ID#", $ID, GetMessage("FORUM_PE_TITLE_UPDATE")) : GetMessage("FORUM_PE_TITLE_ADD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("FPN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_points.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list",
	)
);

if ($ID > 0 && $forumPermissions == "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("FPN_NEW_POINT"),
		"LINK" => "/bitrix/admin/forum_points_edit.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("FPN_DELETE_POINT"),
		"LINK" => "javascript:if(confirm('".GetMessage("FPN_DELETE_POINT_CONFIRM")."')) window.location='/bitrix/admin/forum_points.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($message)
	echo $message->Show();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>" name="forum_edit">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FPN_TAB_POINT"), "ICON" => "forum", "TITLE" => GetMessage("FPN_TAB_POINT_DESCR")),
	);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>

	<?if ($ID>0):?>
	<tr>
		<td width="40%">ID:</td>
		<td width="60%"><?echo $ID ?></td>
	</tr>
	<?endif;?>

	<tr class="adm-detail-required-field">
		<td width="40%">
			<?=(COption::GetOptionString("forum", "SHOW_VOTES", "Y") == "Y" ?
				GetMessage("FORUM_PE_MIN_POINTS") :
				(COption::GetOptionString("main", "rating_weight_type", "auto") == "auto" ?
					GetMessage("FORUM_PE_RATING_VOTES"): GetMessage("FORUM_PE_RATING_VALUE")))?>:
		</td>
		<td width="60%">
			<input type="text" name="MIN_POINTS" value="<?=htmlspecialcharsbx($str_MIN_POINTS)?>" size="10" />
		</td>
	</tr>

	<tr>
		<td><?= GetMessage("FORUM_PE_MNEMOCODE") ?>:</td>
		<td>
			<input type="text" name="CODE" value="<?=htmlspecialcharsbx($str_CODE)?>" size="30" />
		</td>
	</tr>
	<?
	if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y"):
	?>
	<tr>
		<td><?= GetMessage("FORUM_PE_VOTES") ?>:</td>
		<td>
			<input type="text" name="VOTES" value="<?=htmlspecialcharsbx($str_VOTES)?>" size="10" />
		</td>
	</tr>

	<?
	endif;
	for ($i = 0; $i < count($arSysLangs); $i++):
		$arPointsLang = CForumPoints::GetLangByID($ID, $arSysLangs[$i]);
		$str_NAME = ($bInitVars ? ${"NAME_".$arSysLangs[$i]} : $arPointsLang["NAME"]);
		?>
		<tr class="heading">
			<td colspan="2">
				[<?echo $arSysLangs[$i];?>] <?echo $arSysLangNames[$i];?>
			</td>
		</tr>
		<tr class="adm-detail-required-field">
			<td>
				<?= GetMessage("FORUM_PE_NAME") ?>:
			</td>
			<td>
				<input type="text" name="NAME_<?echo $arSysLangs[$i] ?>" value="<?=htmlspecialcharsbx($str_NAME)?>" size="40" />
			</td>
		</tr>
	<?endfor;?>

<?
$tabControl->EndTab();
?>

<?
$tabControl->Buttons(
	array(
		"disabled" => ($forumPermissions < "W"),
		"back_url" => "/bitrix/admin/forum_points.php?lang=".LANG."&".GetFilterParams("filter_", false)
	)
);
$tabControl->End();
?>
</form>
<?
$messageParams = array("POINTS[MIN_POINTS]" => "MIN_POINTS");
for ($i = 0; $i<count($arSysLangs); $i++)
{
	$messageParams["POINTS[NAME][LID][".$arSysLangs[$i]."]"] = "NAME_".$arSysLangs[$i];
}

$tabControl->ShowWarnings("forum_edit", $message, $messageParams);
require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>