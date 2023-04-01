<?php

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Session\Handlers\StrictSessionHandler;
use Bitrix\Main\Session\SessionConfigurationResolver;

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
		"TAB" => GetMessage("SEC_SESSION_ADMIN_SAVEDB_TAB_V2"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_SESSION_ADMIN_SAVEDB_TAB_TITLE_V2"),
	),
	array(
		"DIV" => "sessid",
		"TAB" => GetMessage("SEC_SESSION_ADMIN_SESSID_TAB"),
		"ICON"=>"main_user_edit",
		"TITLE"=>GetMessage("SEC_SESSION_ADMIN_SESSID_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
$_GET["return_url"] = $_GET["return_url"] ?? "";

$returnUrl = $_GET["return_url"]? "&return_url=".urlencode($_GET["return_url"]): "";

if(
	$_SERVER['REQUEST_METHOD'] == "POST"
	&& (
		isset($_REQUEST['save']) || isset($_REQUEST['apply'])
		|| isset($_REQUEST['sessid_ttl_off']) || isset($_REQUEST['sessid_ttl_on'])
	)
	&& $canWrite
	&& check_bitrix_sessid()
)
{
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

	if(isset($_REQUEST["save"]) && $_GET["return_url"] != "")
		LocalRedirect($_GET["return_url"]);
	else
		LocalRedirect("/bitrix/admin/security_session.php?lang=".LANGUAGE_ID.$returnUrl."&".$tabControl->ActiveTabParam());
}

$APPLICATION->SetTitle(GetMessage("SEC_SESSION_ADMIN_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$session = \Bitrix\Main\Application::getInstance()->getSession();
if (method_exists($session, 'getSessionHandler'))
{
	$sessionHandler = $session->getSessionHandler();
}
else
{
	$reflectionClass = new \ReflectionClass($session);
	$reflectionProperty = $reflectionClass->getProperty('sessionHandler');
	$reflectionProperty->setAccessible(true);
	$sessionHandler = $reflectionProperty->getValue($session);
}

$resolver = new SessionConfigurationResolver(Configuration::getInstance());
$sessionConfig = $resolver->getSessionConfig();
$generalHandlerType = $sessionConfig['handlers']['general']['type'] ?? null;
$sessionInFiles = $sessionHandler instanceof StrictSessionHandler;

$showSecondMessage = true;
$messages = array();
if (!$sessionInFiles)
{
	$messageType = "OK";
	$nameOfStorage = Loc::getMessage("SEC_SESSION_ADMIN_STORAGE_NAME_TYPE_" . strtoupper($generalHandlerType));
	$messageText = GetMessage("SEC_SESSION_ADMIN_STORAGE_WITH_SESSION_DATA", ['#NAME#' => $nameOfStorage]);

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
	$messageText = GetMessage("SEC_SESSION_ADMIN_STORAGE_IN_FILES");
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
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("SEC_SESSION_ADMIN_DB_NOTE_V2")?>
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