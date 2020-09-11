<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$forumPermissions = $APPLICATION->GetGroupRight("forum");
if ($forumPermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule("forum");
IncludeModuleLangFile(__FILE__); 
ClearVars();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");

$ID = intval($ID);

$message = false;
$bInitVars = false;
if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && $forumPermissions=="W" && check_bitrix_sessid())
{
	$POINTS_PER_POST = str_replace(",", ".", $POINTS_PER_POST);

	$arFields = array(
		"MIN_NUM_POSTS" => $MIN_NUM_POSTS,
		"POINTS_PER_POST" => $POINTS_PER_POST);
	$res = 0;
	if ($ID > 0)
		$res = CForumPoints2Post::Update($ID, $arFields);
	else
		$res = CForumPoints2Post::Add($arFields);
		
	if (intval($res) <= 0 && $e = $GLOBALS["APPLICATION"]->GetException())
	{
		$message = new CAdminMessage(($ID > 0 ? GetMessage("FORUM_PPE_EDDOR_UPDATE") : GetMessage("FORUM_PPE_ERROR_ADD")), $e);
		$bInitVars = True;
	}
	elseif ($save <> '')
		LocalRedirect("forum_points2post.php?lang=".LANG."&".GetFilterParams("filter_", false));
	else 
		$ID = $res;
}

if ($ID>0)
{
	$db_points = CForumPoints2Post::GetList(array(), array("ID" => $ID));
	$db_points->ExtractFields("str_", False);
}

if ($bInitVars)
{
	$DB->InitTableVarsForEdit("b_forum_points2post", "", "str_");
}

$sDocTitle = ($ID>0) ? str_replace("#ID#", $ID, GetMessage("FORUM_PPE_TITLE_UPD")) : GetMessage("FORUM_PPE_TITLE_ADD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
	array(
		"TEXT" => GetMessage("FPPN_2FLIST"),
		"LINK" => "/bitrix/admin/forum_points2post.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list",
	)
);

if ($ID > 0 && $forumPermissions == "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("FPPN_NEW_POINT"),
		"LINK" => "/bitrix/admin/forum_points2post_edit.php?lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("FPPN_DELETE_POINT"), 
		"LINK" => "javascript:if(confirm('".GetMessage("FPPN_DELETE_POINT_CONFIRM")."')) window.location='/bitrix/admin/forum_points2post.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
if($message)
	echo $message->Show();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="forum_edit">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("FPPN_TAB_POINT"), "ICON" => "forum", "TITLE" => GetMessage("FPPN_TAB_POINT_DESCR"))
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
			<?= GetMessage("FORUM_PPE_MIN_MES") ?>:
		</td>
		<td width="60%">
			<input type="text" name="MIN_NUM_POSTS" value="<?=htmlspecialcharsbx($str_MIN_NUM_POSTS)?>" size="20" maxlength="18">
		</td>
	</tr>

	<tr>
		<td><?= GetMessage("FORUM_PPE_PPM") ?>:</td>
		<td>
			<input type="text" name="POINTS_PER_POST" value="<?=htmlspecialcharsbx($str_POINTS_PER_POST)?>" size="20" maxlength="19">
		</td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(
		array(
				"disabled" => ($forumPermissions < "W"),
				"back_url" => "/bitrix/admin/forum_points2post.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
	);
$tabControl->End();
?>
</form>
<?
$tabControl->ShowWarnings("forum_edit", $message, array("POINTS2POST[MIN_NUM_POSTS]" => "MIN_NUM_POSTS"));

echo BeginNote();?>
	<?= GetMessage("FORUM_PPE_NOTES") ?>
<?echo EndNote();?>

<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>