<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."cat_extra.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_price')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_extra');

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

ClearVars();

$errorMessage = "";
$bVarsFromForm = false;

$ID = (isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : 0);

if ($_SERVER['REQUEST_METHOD'] == "POST" && $Update <> '' && !$bReadOnly && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();

	$arFields = array(
		"NAME" => $NAME,
		"PERCENTAGE" => $PERCENTAGE,
		"RECALCULATE" => (($ID > 0) ? $RECALCULATE : "N")
	);

	if ($ID > 0)
	{
		if (!CExtra::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage = $ex->GetString();
			else
				$errorMessage = GetMessage("CEEN_ERROR_SAVING_EXTRA");
		}
	}
	else
	{
		$ID = (int)CExtra::Add($arFields);
		if ($ID <= 0)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage = $ex->GetString();
			else
				$errorMessage = GetMessage("CEEN_ERROR_SAVING_EXTRA");
		}
	}

	if ($errorMessage == '')
	{
		if ($adminSidePanelHelper->isAjaxRequest())
		{
			$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $ID));
		}
		else
		{
			if (empty($apply))
			{
				$adminSidePanelHelper->localRedirect($listUrl);
				LocalRedirect($listUrl);
			}
			else
			{
				$applyUrl = $selfFolderUrl."cat_extra_edit.php?lang=".$lang."&ID=".$ID;
				$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
				LocalRedirect($applyUrl);
			}
		}
	}
	else
	{
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
		$bVarsFromForm = true;
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

if ($ID > 0)
	$APPLICATION->SetTitle(GetMessage("CEEN_UPDATING"));
else
	$APPLICATION->SetTitle(GetMessage("CEEN_ADDING"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($ID > 0)
{
	$arExtra = CExtra::GetByID($ID);
	if (!$arExtra)
	{
		$ID = 0;
	}
	else
	{
		$str_NAME = $arExtra["NAME"];
		$str_PERCENTAGE = $arExtra["PERCENTAGE"];
		$str_RECALCULATE = "N";
	}
}
if ($bVarsFromForm)
{
	$str_NAME = $NAME;
	$str_PERCENTAGE = $PERCENTAGE;
	$str_RECALCULATE = ($RECALCULATE == "Y" ? 'Y' : 'N');
}

$aMenu = array(
	array(
		"TEXT" => GetMessage("CEEN_2FLIST"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);

if ($ID > 0 && !$bReadOnly)
{
	$aMenu[] = array("SEPARATOR" => "Y");
	$addUrl = $selfFolderUrl."cat_extra_edit.php?lang=".LANGUAGE_ID;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aMenu[] = array(
		"TEXT" => GetMessage("CEEN_NEW_DISCOUNT"),
		"ICON" => "btn_new",
		"LINK" => $addUrl
	);
	$deleteUrl = $selfFolderUrl."cat_extra.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."#tb";
	$buttonAction = "LINK";
	if ($adminSidePanelHelper->isPublicFrame())
	{
		$deleteUrl = $adminSidePanelHelper->editUrlToPublicPage($deleteUrl);
		$buttonAction = "ONCLICK";
	}
	$aMenu[] = array(
		"TEXT" => GetMessage("CEEN_DELETE_DISCOUNT"),
		"ICON" => "btn_delete",
		$buttonAction => "javascript:if(confirm('".GetMessageJS("CEEN_DELETE_DISCOUNT_CONFIRM")."')) top.window.location.href='".$deleteUrl."';",
		"WARNING" => "Y"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
$actionUrl = $APPLICATION->GetCurPage();
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
CAdminMessage::ShowMessage($errorMessage);?>
<form method="POST" action="<?=$actionUrl?>" name="form1">
<?echo GetFilterHiddens("filter_");?>
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post();

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("CEEN_TAB_DISCOUNT"), "ICON" => "catalog", "TITLE" => GetMessage("CEEN_TAB_DISCOUNT_DESCR"))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
	if ($ID > 0)
	{
		?><tr>
			<td width="40%">ID:</td>
			<td width="60%"><?=$ID?></td>
		</tr><?
	}
	?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("CEEN_NAME")?>:</td>
		<td width="60%">
			<input type="text" name="NAME" size="50" value="<? echo htmlspecialcharsbx($str_NAME); ?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("CEEN_PERCENTAGE")?>:</td>
		<td width="60%">
			<input type="text" name="PERCENTAGE" size="10" maxlength="20" value="<?=htmlspecialcharsbx($str_PERCENTAGE); ?>" />%
		</td>
	</tr>
	<?
	if ($ID > 0)
	{
		?>
		<tr>
			<td width="40%"><?echo GetMessage("CEEN_RECALC")?>:</td>
			<td width="60%">
				<input type="hidden" name="RECALCULATE" value="N" />
				<input type="checkbox" name="RECALCULATE" value="Y"<?if ($str_RECALCULATE == "Y") echo " checked"?> />
			</td>
		</tr>
		<?
	}

$tabControl->EndTab();
$tabControl->Buttons(array("disabled" => $bReadOnly, "back_url" => $listUrl));
$tabControl->End();
?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");