<?
define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
$canRead = $USER->CanDoOperation('security_session_settings_read');
$canWrite = $USER->CanDoOperation('security_session_settings_write');
if(!$canRead && !$canWrite)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "savedb",
		"TAB" => GetMessage("SEC_SESSION_ADMIN_SAVEDB_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_SESSION_ADMIN_SAVEDB_TAB_TITLE"),
	),
	array(
		"DIV" => "sessid",
		"TAB" => GetMessage("SEC_SESSION_ADMIN_SESSID_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_SESSION_ADMIN_SESSID_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";

if(
	$_SERVER['REQUEST_METHOD'] == "POST"
	&& (
		$_REQUEST['save'].$_REQUEST['apply'] != ""
		|| $_REQUEST['db_session_on'].$_REQUEST['db_session_off'] != ""
		|| $_REQUEST['sessid_ttl_off'].$_REQUEST['sessid_ttl_on'] != ""
	)
	&& $canWrite
	&& check_bitrix_sessid()
)
{
	if(isset($_POST["db_session_on"]))
	{
		CSecuritySession::activate();
	}
	elseif(isset($_POST["db_session_off"]))
	{
		CSecuritySession::deactivate();
	}

	$ttl = intval($_POST["sessid_ttl"]);
	if($ttl <= 0)
		$ttl = 60;
	COption::SetOptionInt("main", "session_id_ttl", $ttl);

	if(array_key_exists("sessid_ttl_on", $_POST))
	{
		COption::SetOptionString("main", "use_session_id_ttl", "Y");
	}
	elseif(array_key_exists("sessid_ttl_off", $_POST))
	{
		COption::SetOptionString("main", "use_session_id_ttl", "N");
	}

	if($_REQUEST["save"] != "" && $_GET["return_url"] != "")
		LocalRedirect($_GET["return_url"]);
	else
		LocalRedirect("/bitrix/admin/security_session.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
}

$APPLICATION->SetTitle(GetMessage("SEC_SESSION_ADMIN_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$showSecondMessage = true;
$messages = array();
if(COption::GetOptionString("security", "session") == "Y")
{
	$messageType = "OK";
	$messageText = GetMessage("SEC_SESSION_ADMIN_DB_ON");
	if(COption::GetOptionString("main", "use_session_id_ttl") == "Y")
	{
		$messageText .= "<br>";
		$messageText .= GetMessage("SEC_SESSION_ADMIN_SESSID_ON");
		$showSecondMessage = false;
	}
	$messages[] = array("type" => $messageType, "text" => $messageText);
}
else
{
	$messageType = "ERROR";
	$messageText = GetMessage("SEC_SESSION_ADMIN_DB_OFF");
	if(COption::GetOptionString("main", "use_session_id_ttl") != "Y")
	{
		$messageText .= "<br>";
		$messageText .= GetMessage("SEC_SESSION_ADMIN_SESSID_OFF");
		$showSecondMessage = false;
	}
	$messages[] = array("type" => $messageType, "text" => $messageText);
}

if($showSecondMessage)
{
	if(COption::GetOptionString("main", "use_session_id_ttl") == "Y")
	{
		$messages[] = array(
			"type" => "OK",
			"text" => GetMessage("SEC_SESSION_ADMIN_SESSID_ON")
		);
	}
	else
	{
		$messages[] = array(
			"type" => "ERROR",
			"text" => GetMessage("SEC_SESSION_ADMIN_SESSID_OFF")
		);
	}
}

foreach($messages as $message)
{
	CAdminMessage::ShowMessage(array(
				"MESSAGE" => $message["text"],
				"TYPE" => $message["type"],
				"HTML" => true
			));
}
?>

<form method="POST" action="security_session.php?lang=<?=LANGUAGE_ID?><?=$returnUrl?>"  enctype="multipart/form-data" name="editform">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<?if(COption::GetOptionString("security", "session") == "Y"):?>
	<tr>
		<td colspan="2" align="left">
			<input type="submit" name="db_session_off" value="<?echo GetMessage("SEC_SESSION_ADMIN_DB_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
		</td>
	</tr>
<?else:?>
	<?if(CSecuritySession::checkSessionId(session_id())):?>
	<tr>
		<td colspan="2" align="left">
			<input type="submit" name="db_session_on" value="<?echo GetMessage("SEC_SESSION_ADMIN_DB_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
		</td>
	</tr>
	<?else:?>
	<tr>
		<td colspan="2" align="left">
			<?
				CAdminMessage::ShowMessage(array(
						"TYPE" => "ERROR",
						"DETAILS" => GetMessage("SEC_SESSION_ADMIN_SESSID_WARNING"),
						"HTML" => true
					));
			?>
		</td>
	</tr>
	<?endif;?>
<?endif;?>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_SESSION_ADMIN_DB_NOTE")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><span style="color:red">*</span><?echo GetMessage("SEC_SESSION_ADMIN_DB_WARNING")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
<?if(COption::GetOptionString("main", "use_session_id_ttl") == "Y"):?>
		<td colspan="2" align="left">
			<input type="submit" name="sessid_ttl_off" value="<?echo GetMessage("SEC_SESSION_ADMIN_SESSID_BUTTON_OFF")?>"<?if(!$canWrite) echo " disabled"?>>
		</td>
	</tr>
<?else:?>
	<tr>
		<td colspan="2" align="left">
			<input type="submit" name="sessid_ttl_on" value="<?echo GetMessage("SEC_SESSION_ADMIN_SESSID_BUTTON_ON")?>"<?if(!$canWrite) echo " disabled"?> class="adm-btn-save">
		</td>
	</tr>
<?endif;?>
<tr>
	<td width="40%"><?echo GetMessage("SEC_SESSION_ADMIN_SESSID_TTL")?>:</td>
	<td width="60%"><input type="text" name="sessid_ttl" size="6" value="<?echo COption::GetOptionInt("main", "session_id_ttl", 60)?>"></td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_SESSION_ADMIN_SESSID_NOTE")?>
		<?echo EndNote(); ?>
	</td>
</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>(!$canWrite),
		"back_url"=>$_GET["return_url"]? $_GET["return_url"]: "security_session.php?lang=".LANG,
	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<?
$tabControl->End();
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>