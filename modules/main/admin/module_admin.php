<?php

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

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

require_once(__DIR__ . "/../include/prolog_admin_before.php");
const HELP_FILE = "settings/module_admin.php";

if (!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$id = $_REQUEST["id"];

$arModules = array();
function OnModuleInstalledEvent($id)
{
	foreach(GetModuleEvents("main", "OnModuleInstalled", true) as $arEvent)
	{
		ExecuteModuleEventEx($arEvent, array($id));
	}
}

//Get list of subdirs in modules folder
$folders = array(
	"/local/modules",
	"/bitrix/modules",
);
foreach ($folders as $folder)
{
	$handle = @opendir($_SERVER["DOCUMENT_ROOT"].$folder);
	if ($handle)
	{
		while (false !== ($dir = readdir($handle)))
		{
			if (
				!isset($arModules[$dir])
				&& is_dir($_SERVER["DOCUMENT_ROOT"].$folder . "/" . $dir)
				&& !in_array($dir, ['.', '..', 'main'], true)
				&& mb_strpos($dir, ".") === false
			)
			{
				$module_dir = $_SERVER["DOCUMENT_ROOT"] . $folder . "/" . $dir;
				if ($info = CModule::CreateModuleObject($dir))
				{
					$arModules[$dir]["MODULE_ID"] = $info->MODULE_ID;
					$arModules[$dir]["MODULE_NAME"] = $info->MODULE_NAME;
					$arModules[$dir]["MODULE_DESCRIPTION"] = $info->MODULE_DESCRIPTION;
					$arModules[$dir]["MODULE_VERSION"] = $info->MODULE_VERSION;
					$arModules[$dir]["MODULE_VERSION_DATE"] = $info->MODULE_VERSION_DATE;
					$arModules[$dir]["MODULE_SORT"] = $info->MODULE_SORT;
					$arModules[$dir]["MODULE_PARTNER"] = (mb_strpos($dir, ".") !== false) ? $info->PARTNER_NAME : "";
					$arModules[$dir]["MODULE_PARTNER_URI"] = (mb_strpos($dir, ".") !== false) ? $info->PARTNER_URI : "";
					$arModules[$dir]["IsInstalled"] = $info->IsInstalled();
				}
			}
		}
		closedir($handle);
	}
}
\Bitrix\Main\Type\Collection::sortByColumn(
	$arModules,
	['MODULE_SORT' => SORT_ASC, 'MODULE_NAME' => SORT_STRING],
	'',
	null,
	true
);

$fb = ($id === 'fileman' && !$USER->CanDoOperation('fileman_install_control'));
if ($isAdmin && !$fb && check_bitrix_sessid())
{
	if ($_REQUEST["uninstall"] <> '' || $_REQUEST["install"] <> '')
	{
		$id = str_replace("\\", "", str_replace("/", "", $id));
		if ($Module = CModule::CreateModuleObject($id))
		{
			if (
				$_REQUEST["uninstall"] <> ''
				&& $Module->IsInstalled()
			)
			{
				OnModuleInstalledEvent($id);
				$Module->DoUninstall();
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID);
			}
			elseif (
				$_REQUEST["install"] <> ''
				&& !$Module->IsInstalled()

			)
			{
				if ($DB->type === "MYSQL" && defined("MYSQL_TABLE_TYPE") && MYSQL_TABLE_TYPE <> '')
				{
					$DB->Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", true);
				}

				OnModuleInstalledEvent($id);
				$Module->DoInstall();
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG);
			}
		}
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] === "version_down")
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

		if ($_REQUEST["id"] === "main")
		{
			$fn = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/version.php";
		}
		else
		{
			$fn = $_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".preg_replace("/[^a-z0-9.]/", "", $_REQUEST["id"])."/install/version.php");
		}

		$count = (int)$_REQUEST['count'];
		$count = $count > 0? $count: 1;

		if (file_exists($fn) && is_file($fn))
		{
			$fc = file_get_contents($fn);
			if (preg_match("/(\\d+)\\.(\\d+)\\.(\\d+)/", $fc, $match))
			{
				if ($match[3]-$count >= 0)
				{
					$match[3] -= $count;
				}
				else
				{
					$match[3] = (100-$count)+($match[3]);
					if ($match[2] == 0)
					{
						$match[2] = 9;
						$match[1] -= 1;
					}
					else
					{
						$match[2] -= 1;
					}
				}

				if ($match[1] > 0 && $match[2] >= 0 && $match[3] >= 0)
				{
					$fc = str_replace($match[0], $match[1].".".$match[2].".".$match[3], $fc);
					file_put_contents($fn, $fc);
					bx_accelerator_reset();
				}
				echo $match[1].".".$match[2].".".$match[3];
			}
		}

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
	}
}

$APPLICATION->SetTitle(GetMessage("TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<style>
.no-select {-moz-user-select: none; -khtml-user-select: none; user-select: none;}
</style>
<script>
function DoAction(oEvent, action, module_id)
{
	if (oEvent.ctrlKey || BX.browser.IsMac() && (oEvent.altKey || oEvent.metaKey))
	{
		BX('version_for_' + module_id).className = 'no-select';
		if (action == 'version_down')
		{
			ShowWaitWindow();
			BX.ajax.post(
				'module_admin.php?lang=<?= LANGUAGE_ID ?>&id='+module_id+'&count='+(oEvent.shiftKey? 10: 1)+'&<?= bitrix_sessid_get() ?>&action='+action,
				null,
				function(result)
				{
					CloseWaitWindow();
					BX('version_for_' + module_id).className = '';
					if (result.length > 0)
					{
						BX('version_for_' + module_id).innerHTML = result;
					}
				}
			);
		}
	}
}
</script>
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">
	<tr class="heading">
		<td width="60%"><b><?= Loc::getMessage("MOD_NAME") ?></b></td>
		<td><b><?= Loc::getMessage("MOD_VERSION") ?></b></td>
		<td><b><?= Loc::getMessage("MOD_DATE_UPDATE") ?></b></td>
		<td><b><?= Loc::getMessage("MOD_SETUP") ?></b></td>
		<td><b><?= Loc::getMessage("MOD_ACTION") ?></b></td>
	</tr>
	<tr>
		<td><b><?= Loc::getMessage("MOD_MAIN_MODULE") ?></b><br><?php
		$str = str_replace("#A1#","<a  href='update_system.php?lang=".LANG."'>", Loc::getMessage("MOD_MAIN_DESCRIPTION"));
		$str = str_replace("#A2#", "</a>", $str);
		echo $str;?></td>
		<td ondblclick="<?= htmlspecialcharsbx("DoAction(event, 'version_down', 'main')") ?>" id="version_for_main"><?= SM_VERSION ?></td>
		<td nowrap><?= CDatabase::FormatDate(SM_VERSION_DATE, "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("SHORT")) ?></td>
		<td><?= Loc::getMessage("MOD_INSTALLED") ?></td>
		<td>&nbsp;</td>
	</tr>
<?php
foreach($arModules as $info)
{
	?>
	<tr>
		<td><b><?= htmlspecialcharsex($info["MODULE_NAME"]) ?></b> <?= htmlspecialcharsex($info["MODULE_PARTNER"] <> ''? " <b><i>(".str_replace(array("#NAME#", "#URI#"), array($info["MODULE_PARTNER"], $info["MODULE_PARTNER_URI"]), Loc::getMessage("MOD_PARTNER_NAME")).")</i></b>" : "(".$info["MODULE_ID"].")") ?><br><?= $info["MODULE_DESCRIPTION"] ?></td>
		<td ondblclick="<?= htmlspecialcharsbx("DoAction(event, 'version_down', '".CUtil::AddSlashes($info["MODULE_ID"])."')") ?>" id="version_for_<?= htmlspecialcharsbx($info["MODULE_ID"]) ?>"><?= $info["MODULE_VERSION"] ?></td>
		<td nowrap><?= CDatabase::FormatDate($info["MODULE_VERSION_DATE"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("SHORT")) ?></td>
		<td nowrap><?php
			if ($info["IsInstalled"])
			{
				?><?= Loc::getMessage("MOD_INSTALLED")?><?php
			}
			else
			{
				?><span class="required"><?= Loc::getMessage("MOD_NOT_INSTALLED") ?></span><?php
			}
		?></td>
		<td>
			<form action="<?= $APPLICATION->GetCurPage() ?>" method="GET" id="form_for_<?= htmlspecialcharsbx($info["MODULE_ID"]) ?>">
				<input type="hidden" name="action" value="" id="action_for_<?= htmlspecialcharsbx($info["MODULE_ID"]) ?>">
				<input type="hidden" name="lang" value="<?= LANG ?>">
				<input type="hidden" name="id" value="<?= htmlspecialcharsbx($info["MODULE_ID"]) ?>">
				<?= bitrix_sessid_post() ?>
				<?php
				if ($info["IsInstalled"])
				{
					$disabled = (
						!$isAdmin
						|| in_array($info["MODULE_ID"], [ "fileman", "intranet", "ui" ], true)
						|| (
							in_array($info['MODULE_ID'], [ 'rest', 'socialnetwork' ], true)
							&& ModuleManager::isModuleInstalled('intranet')
						)
							? 'disabled'
							: ''
					);
					?>
					<input <?= $disabled ?> type="submit" name="uninstall" value="<?= Loc::getMessage('MOD_DELETE') ?>">
					<?php
				}
				else
				{
					$disabled = (
						!$isAdmin
							? 'disabled'
							: ''
					);
					?>
					<input <?= $disabled ?> type="submit" class="adm-btn-green" name="install" value="<?= Loc::getMessage("MOD_INSTALL_BUTTON")?>">
					<?php
				}
				?>
			</form>
		</td>
	</tr>
	<tr style="display: none;"><td colspan="5"></td></tr>
	<?php
}
?>
</table>
<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT."/modules/main/include/epilog_admin.php");
