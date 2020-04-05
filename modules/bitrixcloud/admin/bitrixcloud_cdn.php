<?
/*.require_module 'standard';.*/
/*.require_module 'pcre';.*/
/*.require_module 'bitrix_main_include_prolog_admin_before';.*/

define("ADMIN_MODULE_NAME", "bitrixcloud");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
/** @global CUser $USER */
if (!$USER->CanDoOperation("bitrixcloud_cdn"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$strError = "";
if (!CModule::IncludeModule("bitrixcloud"))
	$strError = GetMessage("MODULE_INCLUDE_ERROR");
elseif (IsModuleInstalled('intranet'))
	$strError = GetMessage("MODULE_INTRANET_ERROR");

if ($strError != "")
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo CAdminMessage::ShowMessage(array(
		"DETAILS" => $strError,
	));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$aTabs = array(
	array(
		"DIV" => "main",
		"TAB" => GetMessage("BCL_MAIN_TAB1"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("BCL_MAIN_TAB_TITLE"),
	),
	array(
		"DIV" => "dirs",
		"TAB" => GetMessage("BCL_FOLDERS_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("BCL_FOLDERS_TAB_TITLE"),
	),
	array(
		"DIV" => "sites",
		"TAB" => GetMessage("BCL_SITES_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("BCL_SITES_TAB_TITLE"),
	),
	array(
		"DIV" => "ext",
		"TAB" => GetMessage("BCL_EXTENDED_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("BCL_EXTENDED_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
$bVarsFromForm = false;
$message = /*.(CAdminMessage).*/ null;
if (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& (
		isset($_POST["save"])
		|| isset($_POST["apply"])
		|| isset($_POST["bitrixcloud_siteb"])
	)
	&& check_bitrix_sessid()
)
{
	if (isset($_POST["save"]) || isset($_POST["apply"]))
	{
		CAdminNotify::DeleteByTag("bitrixcloud_off");

		$server_name = trim($_POST["server_name"]);
		$https =  ($_POST["https"]==="y");
		$optimize = ($_POST["optimize"]==="y");
		if ($server_name == "")
		{
			$message = new CAdminMessage(GetMessage("BCL_DOMAIN_ERROR"));
		}
		elseif (empty($_POST["site"]) && ($_POST["cdn_active"] === "Y"))
		{
			$message = new CAdminMessage(GetMessage("BCL_SITES_ERROR"));
		}
		else
		{
			$cdn_config = CBitrixCloudCDNConfig::getInstance()->loadFromOptions();
			if (
				$cdn_config->getDomain() !== $server_name
				|| $cdn_config->isHttpsEnabled() !== $https
				|| $cdn_config->isOptimizationEnabled() !== $optimize
			)
			{
				CBitrixCloudCDN::domainChanged();
			}

			$cdn_config->setSites(array_keys($_POST["site"]));
			$cdn_config->setDomain($server_name);
			$cdn_config->setHttps($https);
			$cdn_config->setOptimization($optimize);
			$cdn_config->setKernelRewrite($_POST["kernel_folder"]!=="n");
			$cdn_config->setContentRewrite($_POST["content_folders"]==="y");

			$cdn_config->saveToOptions();

			CBitrixCloudCDNConfig::getInstance()->setDebug($_POST["debug"] === "y");
			if (!CBitrixCloudCDN::SetActive($_POST["cdn_active"] === "Y", true))
			{
				$e = $APPLICATION->GetException();
				if (is_object($e))
				{
					if ($_POST["cdn_active"] === "Y")
						$message = new CAdminMessage(GetMessage("BCL_ENABLE_ERROR"), $e);
					else
						$message = new CAdminMessage(GetMessage("BCL_DISABLE_ERROR"), $e);
				}
			}
		}
	}
	if (is_object($message))
	{
		$bVarsFromForm = true;
	}
	else
	{
		if (isset($_POST["save"]) && $_GET["return_url"] != "")
			LocalRedirect($_GET["return_url"]);

		LocalRedirect("/bitrix/admin/bitrixcloud_cdn.php?lang=".LANGUAGE_ID.($_GET["return_url"] ? "&return_url=".urlencode($_GET["return_url"]) : "")."&".$tabControl->ActiveTabParam());
	}
}
$cdn_config = CBitrixCloudCDNConfig::getInstance()->loadFromOptions();
$APPLICATION->SetTitle(GetMessage("BCL_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (is_object($message))
	echo $message->Show();

if ($bVarsFromForm)
{
	$active = $_POST["cdn_active"] === "Y";
	$optimize = $_POST["optimize"]==="y";
	$kernel_folder = $_POST["bitrix_folder"]!=="n";
	$content_folders = $_POST["content_folders"]==="y";
	if (is_array($_POST["site"]))
		$sites = $_POST["site"];
	else
		$sites = array(
			1,
		);
	$server_name = $_POST["server_name"];
	$https = $_POST["https"]==="y";
	$savedOnce = true;
}
else
{
	$active = CBitrixCloudCDN::IsActive();
	if (!$cdn_config->isActive())
	{
		$active &= $cdn_config->getQuota()->getTrafficSize() <= $cdn_config->getQuota()->getAllowedSize();
		$active &= !$cdn_config->getQuota()->isExpired();
	}
	if (!$server_name)
	{
		$server_name = COption::GetOptionString("main", "server_name", $_SERVER["HTTP_HOST"]);
	}
	$optimize = $cdn_config->isOptimizationEnabled();
	$kernel_folder = $cdn_config->isKernelRewriteEnabled();
	$content_folders = $cdn_config->isContentRewriteEnabled();
	$sites = $cdn_config->getSites();
	$server_name = $cdn_config->getDomain();
	$https = $cdn_config->isHttpsEnabled();
	$savedOnce = CBitrixCloudOption::getOption("cdn_config_active")->isExists();
}
?>
<form method="POST" action="bitrixcloud_cdn.php?lang=<?echo LANGUAGE_ID ?><?echo $_GET["return_url"] ? "&amp;return_url=".urlencode($_GET["return_url"]) : "" ?>" enctype="multipart/form-data" name="editform">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%">
			<label for="cdn_active"><?echo GetMessage("BCL_TURN_ON"); ?>:</label>
		</td>
		<td width="60%">
			<input type="hidden" name="cdn_active" value="N">
			<input type="checkbox" id="cdn_active" name="cdn_active" value="Y" <?echo $active ? 'checked="checked"' : '' ?>>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="optimize"><?echo GetMessage("BCL_OPTIMIZE"); ?>:</label>
		</td>
		<td width="60%">
			<input type="hidden" name="optimize" value="n">
			<input type="checkbox" id="optimize" name="optimize" value="y" <?echo $optimize ? 'checked="checked"' : '' ?>>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%">
			<label for="kernel_folder"><?echo GetMessage("BCL_KERNEL"); ?>:</label>
		</td>
		<td width="60%">
			<input type="hidden" name="kernel_folder" value="n">
			<input type="checkbox" id="kernel_folder" name="kernel_folder" value="y" <?echo ($kernel_folder ? 'checked="checked"' : '' )?>>
			<?echo GetMessage("BCL_KERNEL_NOTE")?>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="content_folders"><?echo GetMessage("BCL_UPLOAD"); ?>:</label>
		</td>
		<td width="60%">
			<input type="hidden" name="content_folders" value="n">
			<input type="checkbox" id="content_folders" name="content_folders" value="y" <?echo ($content_folders ? 'checked="checked"' : '' )?>>
			<?echo GetMessage("BCL_CONTENT_NOTE")?>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%">
			<label for="site_admin"><?echo GetMessage("BCL_ADMIN_PANEL"); ?>:</label>
		</td>
		<td width="60%">
			<input type="checkbox" id="site_admin" name="site[admin]" value="y" <?echo (!$savedOnce || isset($sites["admin"])) ? 'checked="checked"' : '' ?>>
		</td>
	</tr>
<?
$by = 'sort';
$order = 'asc';
$rsSites = CSite::GetList($by, $order);
while ($arSite = $rsSites->Fetch())
{
?>
	<tr>
		<td>
			<label for="site_<?echo htmlspecialcharsbx($arSite["LID"]); ?>"><?echo htmlspecialcharsEx($arSite["NAME"]." [".$arSite["LID"]."]"); ?>:</label>
		</td>
		<td>
			<input type="checkbox" id="site_<?echo htmlspecialcharsbx($arSite["LID"]); ?>" name="site[<?echo htmlspecialcharsbx($arSite["LID"]); ?>]" value="y" <?echo (!$savedOnce || isset($sites[$arSite["LID"]])) ? 'checked="checked"' : '' ?>>
		</td>
	</tr>
<?
}
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%">
			<label  for="server_name"><?echo GetMessage("BCL_SERVER_DOMAIN_NAME");?>:</label>
		</td>
		<td width="60%">
			<input type="text" id="server_name" name="server_name" value="<?echo htmlspecialcharsbx($server_name); ?>">
		</td>
	</tr>
	<tr>
		<td width="40%">
			<label for="https"><?echo GetMessage("BCL_HTTPS"); ?>:</label>
		</td>
		<td width="60%">
			<input type="hidden" name="https" value="n">
			<input type="checkbox" id="https" name="https" value="y" <?echo $https ? 'checked="checked"' : '' ?>>
		</td>
	</tr>
<?
$tabControl->Buttons(array(
	"back_url" => $_GET["return_url"] ? $_GET["return_url"] : "bitrixcloud_cdn.php?lang=".LANGUAGE_ID,
));
?>
<?echo bitrix_sessid_post(); ?>
<input type="hidden" name="debug" value="<?echo htmlspecialcharsbx($_REQUEST["debug"]) ?>">
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
<?
$tabControl->End();
?>
</form>
<?echo BeginNote(), GetMessage("BCL_NOTE"), EndNote();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>