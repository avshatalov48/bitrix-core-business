<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if (!$USER->CanDoOperation('manage_short_uri') && !$USER->CanDoOperation('view_other_settings'))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$message = null;
$isAdmin = $USER->CanDoOperation('manage_short_uri');

IncludeModuleLangFile(__FILE__);

$aTabs = [
	["DIV" => "edit1", "TAB" => GetMessage("SU_EF_tab_1"), "ICON" => "main_user_edit", "TITLE" => GetMessage("SU_EF_tab_1_title")],
];
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($_REQUEST['ID'] ?? 0);
$strError = "";
$bVarsFromForm = false;

if ($_SERVER['REQUEST_METHOD'] == "POST" && (!empty($_POST['save']) || !empty($_POST['apply'])) && $isAdmin && check_bitrix_sessid())
{
	$arFields = [
		"URI" => $_POST['URI'] ?? '',
		"SHORT_URI" => $_POST['SHORT_URI'] ?? '',
		"STATUS" => $_POST['STATUS'] ?? '',
	];
	if ($ID > 0)
	{
		$res = CBXShortUri::Update($ID, $arFields);
	}
	else
	{
		$ID = CBXShortUri::Add($arFields);
		$res = ($ID > 0);
	}

	if ($res)
	{
		if (!empty($_POST['apply']))
		{
			LocalRedirect("/bitrix/admin/short_uri_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect("/bitrix/admin/short_uri_admin.php?lang=" . LANG);
		}
	}
	else
	{
		$message = implode("\n", CBXShortUri::GetErrors());
		if ($message == '')
		{
			$message = GetMessage("SU_EF_save_error");
		}
		$message = new CAdminMessage($message);
		$bVarsFromForm = true;
	}
}

ClearVars();

$str_SHORT_URI = CBXShortUri::GenerateShortUri();
$str_MODIFIED = '';
$str_URI = '';
$str_STATUS = '';
$str_LAST_USED = '';
$str_NUMBER_USED = '';

if (isset($_REQUEST["public"]))
{
	$str_URI = $_REQUEST["str_URI"];
	$suri = CBXShortUri::GetList([], ["URI_EXACT" => $str_URI]);
	if ($a = $suri->Fetch())
	{
		$ID = $a["ID"];
	}
	$str_URI = htmlspecialcharsbx($str_URI);
}

if ($ID > 0)
{
	$suri = CBXShortUri::GetList([], ["ID" => $ID]);
	if (!$suri->ExtractFields())
	{
		$ID = 0;
	}
}

if ($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_short_uri", "");
}

$APPLICATION->SetTitle(($ID > 0 ? GetMessage("SU_EF_title_edit") . $ID : GetMessage("SU_EF_title_add")));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = [
	[
		"TEXT" => GetMessage("SU_EF_list_text"),
		"TITLE" => GetMessage("SU_EF_list"),
		"LINK" => "short_uri_admin.php?lang=" . LANG,
		"ICON" => "btn_list",
	],
];
if ($ID > 0)
{
	$aMenu[] = ["SEPARATOR" => "Y"];
	$aMenu[] = [
		"TEXT" => GetMessage("SU_EF_add_text"),
		"TITLE" => GetMessage("SU_EF_mnu_add"),
		"LINK" => "short_uri_edit.php?lang=" . LANG,
		"ICON" => "btn_new",
	];
	$aMenu[] = [
		"TEXT" => GetMessage("SU_EF_del_text"),
		"TITLE" => GetMessage("SU_EF_mnu_del"),
		"LINK" => "javascript:if(confirm('" . GetMessage("SU_EF_mnu_del_conf") . "'))window.location='short_uri_admin.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
		"ICON" => "btn_delete",
	];
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if (isset($_REQUEST["mess"]) && $_REQUEST["mess"] == "ok" && $ID > 0)
{
	CAdminMessage::ShowMessage(["MESSAGE" => GetMessage("SU_EF_saved"), "TYPE" => "OK"]);
}
if ($message)
{
	echo $message->Show();
}
?>

	<form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data"
		  name="short_uri_form">
		<?
		$tabControl->Begin();
		?>
		<?
		//********************
		//Subscriber tab
		//********************
		$tabControl->BeginNextTab();
		?>
		<? if ($ID > 0): ?>
			<tr>
				<td width="40%"><? echo GetMessage("SU_EF_date_add") ?></td>
				<td width="60%"><? echo $str_MODIFIED; ?></td>
			</tr>
		<?endif ?>
		<tr class="adm-detail-required-field">
			<td width="40%"><? echo GetMessage("SU_EF_URI") ?></td>
			<td width="60%"><input type="text" name="URI" value="<?= $str_URI ?>" size="70"></td>
		</tr>
		<tr class="adm-detail-required-field">
			<td><? echo GetMessage("SU_EF_SHORT_URI") ?></td>
			<td><input type="text" name="SHORT_URI" value="<?= $str_SHORT_URI ?>" size="70"
					   onkeyup="ShortUriChangeHandler(this.value)"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><span id="id_short_uri_span"></span>
				<?
				$request = \Bitrix\Main\Context::getCurrent()->getRequest();
				$proto = $request->isHttps() ? "https://" : "http://";
				$host = $request->getHttpHost();
				?>
				<script>
					function ShortUriChangeHandler(val)
					{
						var d = document.getElementById("id_short_uri_span");
						if (d) {
							d.innerHTML = '<a href="<?= $proto . $host ?>/' + BX.util.htmlspecialchars(encodeURI(val)) + '"><?= $proto . $host ?>/' + BX.util.htmlspecialchars(val) + '</a>';
						}
					}

					setTimeout(function () {
						ShortUriChangeHandler('<?=CUtil::JSEscape(htmlspecialcharsback($str_SHORT_URI))?>');
					}, 10);
				</script>
			</td>
		</tr>
		<tr class="adm-detail-required-field">
			<td><? echo GetMessage("SU_EF_STATUS") ?></td>
			<td><?= CBXShortUri::SelectBox("STATUS", $str_STATUS) ?></td>
		</tr>
		<? if ($ID > 0): ?>
			<tr>
				<td><? echo GetMessage("SU_EF_LAST_USED") ?></td>
				<td><? echo $str_LAST_USED; ?></td>
			</tr>
			<tr>
				<td><? echo GetMessage("SU_EF_NUMBER_USED") ?></td>
				<td><? echo $str_NUMBER_USED; ?></td>
			</tr>
		<?endif ?>

		<?
		$tabControl->Buttons(
			[
				"disabled" => !$isAdmin,
				"back_url" => "short_uri_admin.php?lang=" . LANG,

			]
		);
		?>
		<? echo bitrix_sessid_post(); ?>
		<input type="hidden" name="lang" value="<? echo LANG ?>">
		<? if ($ID > 0): ?>
			<input type="hidden" name="ID" value="<?= $ID ?>">
		<?endif; ?>
		<?
		$tabControl->End();
		?>
	</form>

<?
$tabControl->ShowWarnings("short_uri_form", $message);
?>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
