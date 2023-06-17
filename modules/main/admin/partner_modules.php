<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use Bitrix\Main\Application;

require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");
define("HELP_FILE", "settings/module_admin.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$id = $_REQUEST["id"] ?? null;
$mod = $_REQUEST["mod"] ?? null;
$resultMod = $_REQUEST["result"] ?? null;

if($isAdmin && $_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["module"]) && check_bitrix_sessid())
{
	$moduleId = preg_replace("#[^a-z0-9.,_-]#i", "", $_POST["module"]);
	if($moduleId <> '')
	{
		if(isset($_POST["act"]) && $_POST["act"] == "unnotify")
		{
			$cModules = COption::GetOptionString("main", "mp_modules_date", "");
			if($cModules <> '')
			{
				$arModules = unserialize($cModules, ['allowed_classes' => false]);

				foreach($arModules as $id => $val)
				{
					if($val["ID"] == $moduleId)
						unset($arModules[$id]);
				}
				if(!empty($arModules))
					COption::SetOptionString("main", "mp_modules_date", serialize($arModules));
				else
					COption::RemoveOption("main", "mp_modules_date");
			}
			die();
		}
		elseif(isset($_POST["act"]) && $_POST["act"] == "add_opinion")
		{
			$arF = Array(
					"comments" => $GLOBALS["APPLICATION"]->ConvertCharset($_POST["comments"], SITE_CHARSET, "windows-1251"),
					"lkey" => Application::getInstance()->getLicense()->getPublicHashKey(),
					"act" => "add_delete_comment",
					"name" => $GLOBALS["APPLICATION"]->ConvertCharset($USER->GetFullName(), SITE_CHARSET, "windows-1251"),
					"email" => $USER->GetEmail(),
					"reason" => $_POST["reason"],
				);

			$ht = new CHTTP();
			$ht->Post("https://marketplace.1c-bitrix.ru/solutions/".$moduleId."/", $arF);
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&result=OPAD");
		}
		elseif(isset($_POST["act"]) && $_POST["act"] == "unnotify_mp")
		{
			$arrayId = preg_replace("#[^a-z0-9.,_-]#i", "", $_POST["array_id"]);
			$moduleId = preg_replace("#[^a-z0-9.,_-]#i", "", $_POST["module"]);
			$cMpModulesResult = COption::GetOptionString("main", "last_mp_modules_result", "");
			if ($cMpModulesResult <> '')
			{
				$arModulesResult = unserialize($cMpModulesResult, ['allowed_classes' => false]);
				foreach ($arModulesResult[$arrayId] as $key => $arModule)
				{
					if (trim(mb_strtoupper($key)) == trim(mb_strtoupper($moduleId)))
					{
						unset ($arModulesResult[$arrayId][$key]);
					}
				}
			}
			COption::SetOptionString("main", "last_mp_modules_result", serialize($arModulesResult));
			die();
		}
	}
}

$arModules = array();
function OnModuleInstalledEvent($id, $installed, $Module)
{
	foreach(GetModuleEvents("main", "OnModuleInstalled", true) as $arEvent)
	{
		ExecuteModuleEventEx($arEvent, array($id, $installed));
	}

	$cModules = COption::GetOptionString("main", "mp_modules_date", "");
	$arModules = array();
	if($cModules <> '')
		$arModules = unserialize($cModules, ['allowed_classes' => false]);

	if($installed == "Y")
	{
		$arModules[] = array("ID" => $id, "NAME" => htmlspecialcharsbx($Module->MODULE_NAME), "TMS" => time());
		if(count($arModules) > 3)
			$arModules = array_slice($arModules, -3);

		COption::SetOptionString("main", "mp_modules_date", serialize($arModules));
	}
	else
	{
		foreach($arModules as $arid => $val)
		{
			if($val["ID"] == $id)
				unset($arModules[$arid]);
		}
		if(!empty($arModules))
			COption::SetOptionString("main", "mp_modules_date", serialize($arModules));
		else
			COption::RemoveOption("main", "mp_modules_date");

		$_SESSION["MP_MOD_DELETED"] = array("ID" => $id, "NAME" => $Module->MODULE_NAME);
	}
}

$folders = array(
	"/local/modules",
	"/bitrix/modules",
);
foreach($folders as $folder)
{
	if(file_exists($_SERVER["DOCUMENT_ROOT"].$folder))
	{
		$handle = opendir($_SERVER["DOCUMENT_ROOT"].$folder);
		if($handle)
		{
			while (false !== ($dir = readdir($handle)))
			{
				if(!isset($arModules[$dir]) && is_dir($_SERVER["DOCUMENT_ROOT"].$folder."/".$dir) && $dir!="." && $dir!=".." && strpos($dir, ".") !== false)
				{
					$module_dir = $_SERVER["DOCUMENT_ROOT"].$folder."/".$dir;
					if($info = CModule::CreateModuleObject($dir))
					{
						$arModules[$dir]["MODULE_ID"] = $info->MODULE_ID;
						$arModules[$dir]["MODULE_NAME"] = $info->MODULE_NAME;
						$arModules[$dir]["MODULE_DESCRIPTION"] = $info->MODULE_DESCRIPTION;
						$arModules[$dir]["MODULE_VERSION"] = $info->MODULE_VERSION;
						$arModules[$dir]["MODULE_VERSION_DATE"] = $info->MODULE_VERSION_DATE;
						$arModules[$dir]["MODULE_SORT"] = $info->MODULE_SORT;
						$arModules[$dir]["MODULE_PARTNER"] = $info->PARTNER_NAME;
						$arModules[$dir]["MODULE_PARTNER_URI"] = $info->PARTNER_URI;
						$arModules[$dir]["IsInstalled"] = $info->IsInstalled();
						if(defined(str_replace(".", "_", $info->MODULE_ID)."_DEMO"))
						{
							$arModules[$dir]["DEMO"] = "Y";
							if($info->IsInstalled())
							{
								if(CModule::IncludeModuleEx($info->MODULE_ID) != MODULE_DEMO_EXPIRED)
								{
									$arModules[$dir]["DEMO_DATE"] = ConvertTimeStamp($GLOBALS["SiteExpireDate_".str_replace(".", "_", $info->MODULE_ID)], "SHORT");
								}
								else
									$arModules[$dir]["DEMO_END"] = "Y";
							}
						}
					}
				}
			}
			closedir($handle);
		}
	}
}
\Bitrix\Main\Type\Collection::sortByColumn(
	$arModules,
	['MODULE_SORT' => SORT_ASC, 'MODULE_NAME' => SORT_STRING],
	'',
	null,
	true
);

$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");
$arRequestedModules = CUpdateClientPartner::GetRequestedModules("");

$arUpdateList = CUpdateClientPartner::GetUpdatesList($errorMessage, LANG, $stableVersionsOnly, $arRequestedModules, Array("fullmoduleinfo" => "Y"));
$strError_tmp = "";
$arClientModules = CUpdateClientPartner::GetCurrentModules($strError_tmp);

$linkToBuy = false;
$linkToBuyUpdate = false;
if(LANGUAGE_ID == "ru")
{
	$linkToBuy = "https://marketplace.1c-bitrix.ru"."/tobasket.php?ID=#CODE#";
	$linkToBuyUpdate = "https://marketplace.1c-bitrix.ru"."/tobasket.php?ID=#CODE#&lckey=" . Application::getInstance()->getLicense()->getPublicHashKey();
}

$bHaveNew = false;
$modules = Array();
$modulesNew = Array();
if(!empty($arUpdateList["MODULE"]))
{
	foreach($arUpdateList["MODULE"] as $k => $v)
	{
		if(!array_key_exists($v["@"]["ID"], $arClientModules))
		{
			$bHaveNew = true;
			$modulesNew[] = Array(
					"NAME" => htmlspecialcharsBack($v["@"]["NAME"]),
					"ID" => $v["@"]["ID"],
					"DESCRIPTION" => $v["@"]["DESCRIPTION"],
					"PARTNER" => $v["@"]["PARTNER_NAME"],
					"FREE_MODULE" => $v["@"]["FREE_MODULE"],
					"DATE_FROM" => $v["@"]["DATE_FROM"],
					"DATE_TO" => $v["@"]["DATE_TO"],
					"UPDATE_END" => $v["@"]["UPDATE_END"],
				);
		}
		else
		{
			$modules[$v["@"]["ID"]] = Array(
					"VERSION" => (isset($v["#"]["VERSION"]) ? $v["#"]["VERSION"][count($v["#"]["VERSION"]) - 1]["@"]["ID"] : ""),
					"FREE_MODULE" => $v["@"]["FREE_MODULE"],
					"DATE_FROM" => $v["@"]["DATE_FROM"],
					"DATE_TO" => $v["@"]["DATE_TO"],
					"UPDATE_END" => $v["@"]["UPDATE_END"],
				);
		}
	}
}

$errorMessage = "";
$errorMessageFull = "";
$fb = ($id == 'fileman' && !$USER->CanDoOperation('fileman_install_control'));
if((!empty($_REQUEST["uninstall"]) || !empty($_REQUEST["install"]) || !empty($_REQUEST["clear"])) && $isAdmin && !$fb && check_bitrix_sessid())
{
	$id = str_replace("\\", "", str_replace("/", "", $id));
	if($Module = CModule::CreateModuleObject($id))
	{
		if($Module->IsInstalled() && !empty($_REQUEST["uninstall"]))
		{
			OnModuleInstalledEvent($id, 'N', $Module);
			if(COption::GetOptionString("main", "event_log_marketplace", "Y") === "Y")
				CEventLog::Log("INFO", "MP_MODULE_UNINSTALLED", "main", $id);

			if($Module->DoUninstall() !== false)
			{
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&mod=".$id."&result=DELOK");
			}
			else
			{
				$errorMessage = GetMessage("MOD_UNINSTALL_ERROR", Array("#CODE#" => $id));
				if($e = $APPLICATION->GetException())
					$errorMessageFull = $e->GetString();
			}

		}
		elseif(!$Module->IsInstalled() && !empty($_REQUEST["install"]))
		{
			if ($DB->type == "MYSQL" && defined("MYSQL_TABLE_TYPE") && MYSQL_TABLE_TYPE <> '')
			{
				$DB->Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", true);
			}

			OnModuleInstalledEvent($id, 'Y', $Module);
			if(COption::GetOptionString("main", "event_log_marketplace", "Y") === "Y")
				CEventLog::Log("INFO", "MP_MODULE_INSTALLED", "main", $id);

			if($Module->DoInstall() !== false)
			{
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&mod=".$id."&result=OK");
			}
			else
			{
				$errorMessage = GetMessage("MOD_INSTALL_ERROR", Array("#CODE#" => $id));
				if($e = $APPLICATION->GetException())
					$errorMessageFull = $e->GetString();
			}

		}
	elseif(!$Module->IsInstalled() && !empty($_REQUEST["clear"]))
		{
			if($Module->MODULE_ID <> '' && ($mdir = getLocalPath("modules/".$Module->MODULE_ID)) !== false)
			{
				if(COption::GetOptionString("main", "event_log_marketplace", "Y") === "Y")
					CEventLog::Log("INFO", "MP_MODULE_DELETED", "main", $id);
				DeleteDirFilesEx($mdir."/");
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&mod=".$id."&result=CLEAROK");
			}
		}
	}
}

$sTableID = "upd_partner_modules_all";
$lAdmin = new CAdminList($sTableID);

$sTableID1 = "upd_partner_modules_new";
$lAdmin1 = new CAdminList($sTableID1);

$lAdmin->BeginPrologContent();
echo "<h2>".GetMessage("MOD_SMP_AV_MOD")."</h2><p>".GetMessage("MOD_SMP_AV_MOD_TEXT1")."<br />".GetMessage("MOD_SMP_AV_MOD_TEXT2")."</p>";
$lAdmin->EndPrologContent();

$arHeaders = Array(
	array(
		"id" => "NAME",
		"content" => GetMessage("MOD_NAME"),
		"default" => true,
	),
	array(
		"id" => "PARTNER",
		"content" => GetMessage("MOD_PARTNER"),
		"default" => true,
	),
	array(
		"id" => "VERSION",
		"content" => GetMessage("MOD_VERSION"),
		"default" => true,
	),
	array(
		"id" => "DATE_UPDATE",
		"content" => GetMessage("MOD_DATE_UPDATE"),
		"default" => true,
	),
	array(
		"id" => "DATE_TO",
		"content" => GetMessage("MOD_DATE_TO"),
		"default" => true,
	),
	array(
		"id" => "STATUS",
		"content" => GetMessage("MOD_SETUP"),
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeaders);

$rsData = new CDBResult;
$rsData->InitFromArray($arModules);
$rsData = new CAdminResult($rsData, $sTableID);
while($info = $rsData->Fetch())
{
	$row =& $lAdmin->AddRow($info["MODULE_ID"], $info);

	if(in_array(LANGUAGE_ID, ["ru"]))
		$name = "<b><a href=\"https://marketplace.1c-bitrix.ru/".htmlspecialcharsbx($info["MODULE_ID"])."\" target=\"_blank\">".htmlspecialcharsbx($info["MODULE_NAME"])."</a></b> (".htmlspecialcharsbx($info["MODULE_ID"]).")";
	elseif(in_array(LANGUAGE_ID, ["ua"]))
		$name = "<b><a href=\"https://marketplace.bitrix.ua/".htmlspecialcharsbx($info["MODULE_ID"])."\" target=\"_blank\">".htmlspecialcharsbx($info["MODULE_NAME"])."</a></b> (".htmlspecialcharsbx($info["MODULE_ID"]).")";
	else
		$name = "<b>".htmlspecialcharsbx($info["MODULE_NAME"])."</b> (".htmlspecialcharsbx($info["MODULE_ID"]).")";

	if($info["DEMO"] == "Y")
		$name .= " <span style=\"color:red;\">".GetMessage("MOD_DEMO")."</span>";
	$name .= "<br />".htmlspecialcharsbx($info["MODULE_DESCRIPTION"]);
	$row->AddViewField("NAME", $name);
	$row->AddViewField("PARTNER", (($info["MODULE_PARTNER"] <> '') ? " ".str_replace(array("#NAME#", "#URI#"), array($info["MODULE_PARTNER"], $info["MODULE_PARTNER_URI"]), GetMessage("MOD_PARTNER_NAME"))."" : "&nbsp;"));
	$row->AddViewField("VERSION", $info["MODULE_VERSION"]);
	$row->AddViewField("DATE_UPDATE", CDatabase::FormatDate($info["MODULE_VERSION_DATE"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("SHORT")));
	if($modules[$info["MODULE_ID"]]["FREE_MODULE"] != "Y")
	{
		if($info["DEMO"] == "Y")
		{
			if($linkToBuy)
			{
				if($info["DEMO_END"] == "Y")
					$row->AddViewField("DATE_TO", "<span class=\"required\">".GetMessage("MOD_DEMO_END")."</span><br /><a href=\"".str_replace("#CODE#", $info["MODULE_ID"], $linkToBuy)."\" target=\"_blank\">".GetMessage("MOD_UPDATE_BUY_DEMO")."</a>");
				else
					$row->AddViewField("DATE_TO", $info["DEMO_DATE"]."<br /><a href=\"".str_replace("#CODE#", $info["MODULE_ID"], $linkToBuy)."\" target=\"_blank\">".GetMessage("MOD_UPDATE_BUY_DEMO")."</a>");
			}
			else
			{
				if($info["DEMO_END"] == "Y")
					$row->AddViewField("DATE_TO", "<span class=\"required\">".GetMessage("MOD_DEMO_END")."</span>");
				else
					$row->AddViewField("DATE_TO", $info["DEMO_DATE"]);
			}
		}
		else
		{
			if($modules[$info["MODULE_ID"]]["UPDATE_END"] == "Y")
			{
				if($linkToBuy && !empty($modules[$info["MODULE_ID"]]["VERSION"]))
					$row->AddViewField("DATE_TO", "<span style=\"color:red;\">".$modules[$info["MODULE_ID"]]["DATE_TO"]."</span><br /><a href=\"".str_replace("#CODE#", $info["MODULE_ID"], $linkToBuyUpdate)."\" target=\"_blank\">".GetMessage("MOD_UPDATE_BUY")."</a>");
				else
					$row->AddViewField("DATE_TO", "<span style=\"color:red;\">".$modules[$info["MODULE_ID"]]["DATE_TO"]."</span>");
			}
			else
				$row->AddViewField("DATE_TO", $modules[$info["MODULE_ID"]]["DATE_TO"]);
		}
	}
	$status = "";
	if($info["IsInstalled"])
		$status = GetMessage("MOD_INSTALLED");
	else
		$status = "<span class=\"required\">".GetMessage("MOD_NOT_INSTALLED")."</span>";

	if(!empty($modules[$info["MODULE_ID"]]["VERSION"]))
		$status .= "<br /><a href=\"/bitrix/admin/update_system_partner.php?tabControl_active_tab=tab2&addmodule=".$info["MODULE_ID"]."\" style=\"color:green;\">".GetMessage("MOD_SMP_NEW_UPDATES")."</a>";
	$row->AddViewField("STATUS", $status);

	$arActions = Array();
	if(!empty($modules[$info["MODULE_ID"]]) && !empty($modules[$info["MODULE_ID"]]["VERSION"]))
	{
		$arActions[] = array(
			"ICON" => "",
			"DEFAULT" => true,
			"TEXT" => GetMessage("MOD_SMP_UPDATE"),
			"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/update_system_partner.php?tabControl_active_tab=tab2&addmodule=".$info["MODULE_ID"]),
		);
	}

	if($info["IsInstalled"])
	{
		$arActions[] = array(
			"ICON" => "delete",
			"DEFAULT" => false,
			"TEXT" => GetMessage("MOD_DELETE"),
			"ACTION" => $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?id=".htmlspecialcharsbx($info["MODULE_ID"])."&lang=".LANG."&uninstall=Y&".bitrix_sessid_get()),
		);
	}
	else
	{
		$arActions[] = array(
			"ICON" => "add",
			"DEFAULT" => false,
			"TEXT" => GetMessage("MOD_INSTALL_BUTTON"),
			"ACTION" => $lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?id=".htmlspecialcharsbx($info["MODULE_ID"])."&lang=".LANG."&install=Y&".bitrix_sessid_get()),
		);
		$arActions[] = array(
			"ICON" => "delete",
			"DEFAULT" => false,
			"TEXT" => GetMessage("MOD_SMP_DELETE"),
			"ACTION" => "if(confirm('".GetMessageJS('MOD_CLEAR_CONFIRM', Array("#NAME#" => htmlspecialcharsbx($info["MODULE_NAME"])))."')) ".$lAdmin->ActionRedirect($APPLICATION->GetCurPage()."?id=".htmlspecialcharsbx($info["MODULE_ID"])."&lang=".LANG."&clear=Y&".bitrix_sessid_get()),
		);
	}
	$row->AddActions($arActions);
}

$lAdmin->CheckListMode();


$lAdmin1->BeginPrologContent();
echo "<h2>".GetMessage("MOD_SMP_BUY_MOD")."</h2><p>".GetMessage("MOD_SMP_BUY_MOD_TEXT1")."<br />".GetMessage("MOD_SMP_BUY_MOD_TEXT2")."</p>";
$lAdmin1->EndPrologContent();

$arHeaders1 = Array(
	array(
		"id" => "NAME",
		"content" => GetMessage("MOD_NAME"),
		"default" => true,
	),
	array(
		"id" => "PARTNER",
		"content" => GetMessage("MOD_PARTNER"),
		"default" => true,
	),
	array(
		"id" => "DATE_TO",
		"content" => GetMessage("MOD_DATE_TO"),
		"default" => true,
	),
);
$lAdmin1->AddHeaders($arHeaders1);
$rsData = new CDBResult;
$rsData->InitFromArray($modulesNew);
$rsData = new CAdminResult($rsData, $sTableID1);

while($info = $rsData->Fetch())
{

	$row =& $lAdmin1->AddRow($info["ID"], $info);

	$row->AddViewField("NAME", "<b><a href=\"http://marketplace.1c-bitrix.ru/".htmlspecialcharsbx($info["ID"])."\" target=\"_blank\">".htmlspecialcharsbx($info["NAME"])."</a></b> (".htmlspecialcharsbx($info["ID"]).")<br />".htmlspecialcharsbx($info["DESCRIPTION"]));
	$row->AddViewField("PARTNER", $info["PARTNER"]);

	if($info["UPDATE_END"] == "Y")
	{
		if($linkToBuy)
		{
			if($info["DATE_TO"] <> '')
				$row->AddViewField("DATE_TO", "<span style=\"color:red;\">".$info["DATE_TO"]."</span><br /><a href=\"".str_replace("#CODE#", $info["ID"], $linkToBuyUpdate)."\" target=\"_blank\">".GetMessage("MOD_UPDATE_BUY")."</a>");
			else
				$row->AddViewField("DATE_TO", "<a href=\"".str_replace("#CODE#", $info["ID"], $linkToBuyUpdate)."\" target=\"_blank\">".GetMessage("MOD_UPDATE_BUY")."</a>");
		}
		else
			$row->AddViewField("DATE_TO", "<span style=\"color:red;\">".$info["DATE_TO"]."</span>");
	}


	$arActions = Array();
	if($info["UPDATE_END"] != "Y")
	{
		$arActions[] = array(
			"ICON" => "",
			"DEFAULT" => true,
			"TEXT" => GetMessage("MOD_SMP_DOWNLOAD"),
			"ACTION" => $lAdmin1->ActionRedirect("/bitrix/admin/update_system_partner.php?tabControl_active_tab=tab2&addmodule=".$info["MODULE_ID"]),
		);
	}

	$row->AddActions($arActions);
}

$lAdmin1->CheckListMode();

$APPLICATION->SetTitle(GetMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");


if($mod <> '' && $resultMod == "OK")
{
	CAdminMessage::ShowNote(GetMessage("MOD_SMP_INSTALLED", Array("#MODULE_NAME#" => $arModules[$mod]["MODULE_NAME"])));
}
elseif($mod <> '' && $resultMod == "DELOK")
{
	CAdminMessage::ShowNote(GetMessage("MOD_SMP_UNINSTALLED", Array("#MODULE_NAME#" => $arModules[$mod]["MODULE_NAME"])));
}
elseif($mod <> '' && $resultMod == "CLEAROK")
{
	CAdminMessage::ShowNote(GetMessage("MOD_SMP_DELETED", Array("#MODULE_NAME#" => $mod)));
}

if($errorMessage <> '')
{
	CAdminMessage::ShowMessage(Array("DETAILS" => $errorMessageFull, "TYPE" => "ERROR", "MESSAGE" => $errorMessage, "HTML" => true));
}
if($resultMod == "OPAD")
{
	CAdminMessage::ShowNote(GetMessage("MOD_SMP_OPONION_OK"));
}

if(!empty($_SESSION["MP_MOD_DELETED"]) && in_array(LANGUAGE_ID, array("ru", "ua")))
{
	echo BeginNote();
	?>
	<form action="" method="POST">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="module" value="<?=$_SESSION["MP_MOD_DELETED"]["ID"]?>">
	<input type="hidden" name="act" value="add_opinion">
	<?=GetMessage("MOD_SMP_DELETE_OPINION", array("#ID#" => $_SESSION["MP_MOD_DELETED"]["ID"], "#NAME#" => htmlspecialcharsbx($_SESSION["MP_MOD_DELETED"]["NAME"])))?>
	<p><?=GetMessage("MOD_SMP_DELETE_OPINION_REASON")?></p>
	<input type="radio" name="reason" value="1" id="r1">&nbsp;<label for="r1"><?=GetMessage("MOD_SMP_DELETE_OPINION_REASON1")?></label><br />
	<input type="radio" name="reason" value="2" id="r2">&nbsp;<label for="r2"><?=GetMessage("MOD_SMP_DELETE_OPINION_REASON2")?></label><br />
	<input type="radio" name="reason" value="3" id="r3">&nbsp;<label for="r3"><?=GetMessage("MOD_SMP_DELETE_OPINION_REASON3")?></label><br />
	<input type="radio" name="reason" value="4" id="r4">&nbsp;<label for="r4"><?=GetMessage("MOD_SMP_DELETE_OPINION_REASON4")?></label><br />
	<input type="radio" name="reason" value="5" id="r5">&nbsp;<label for="r5"><?=GetMessage("MOD_SMP_DELETE_OPINION_REASON5")?></label><br />
	<input type="radio" name="reason" value="6" id="r6" checked>&nbsp;<label for="r6"><?=GetMessage("MOD_SMP_DELETE_OPINION_REASON6")?></label><br /><br />

	<textarea name="comments" style="width: 500px; height: 100px;"></textarea>
	<p><?=GetMessage("MOD_SMP_DELETE_OPINION_THANKS")?></p>
	<input type="submit" value="<?=GetMessage("MOD_SMP_OPONION_ADD")?>">
	</form>
	<?
	echo EndNote();
	unset($_SESSION["MP_MOD_DELETED"]);
}
if($bHaveNew)
{
	$lAdmin1->DisplayList();
}

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>