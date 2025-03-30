<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
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

$id = $_REQUEST["id"] ?? null;

$arModules = array();
function OnModuleInstalledEvent($id)
{
	foreach(GetModuleEvents("main", "OnModuleInstalled", true) as $arEvent)
	{
		ExecuteModuleEventEx($arEvent, array($id));
	}
}

//Get list of subdirs in modules folder
$arModules = ModuleManager::getModulesFromDisk(true, false);

\Bitrix\Main\Type\Collection::sortByColumn(
	$arModules,
	['sort' => SORT_ASC, 'name' => SORT_STRING],
	'',
	null,
	true
);

$fb = ($id === 'fileman' && !$USER->CanDoOperation('fileman_install_control'));
if ($isAdmin && !$fb && check_bitrix_sessid())
{
	if (!empty($_REQUEST["uninstall"]) || !empty($_REQUEST["install"]))
	{
		$id = str_replace("\\", "", str_replace("/", "", $id));
		if ($Module = CModule::CreateModuleObject($id))
		{
			if (!empty($_REQUEST["uninstall"]) && $Module->IsInstalled())
			{
				OnModuleInstalledEvent($id);
				$Module->DoUninstall();
				LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID);
			}
			elseif (!empty($_REQUEST["install"]) && !$Module->IsInstalled())
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
		if (isset($_REQUEST["id"]) && is_string($_REQUEST["id"]))
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

			$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 0;

			if (($newVersion = ModuleManager::decreaseVersion($_REQUEST["id"], $count)) !== null)
			{
				echo $newVersion;
			}

			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
		}
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] === "db_version_down")
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

		if (isset($_REQUEST["id"]) && $_REQUEST["id"] === "main")
		{
			$updatesDir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/dev/updates";
		}
		else
		{
			$updatesDir = $_SERVER["DOCUMENT_ROOT"].getLocalPath("modules/".preg_replace("/[^a-z0-9.]/", "", $_REQUEST["id"])."/dev/updates");
		}

		$dbVersion = \Bitrix\Main\Config\Option::get('main', 'updates_' . $_REQUEST["id"] . '_version');
		if ($dbVersion)
		{
			$updaters = [];
			foreach (array_merge(
				glob($updatesDir . '/[0-9]*/[0-9]*.[0-9]*.[0-9]*/updater/index.php'),
				glob($updatesDir . '/[0-9]*/[0-9]*.[0-9]*.[0-9]*/updater.php')
			) as $updater)
			{
				if (preg_match('#/(\d+)/(\1\.\d+\.\d+)/updater(\.php|/index\.php)$#', $updater, $match))
				{
					if (version_compare($match[2], $dbVersion) < 0)
					{
						$updaters[$match[2]] = $updater;
					}
				}
			}
			if ($updaters)
			{
				uksort($updaters, 'version_compare');
				$newVersion = array_key_last($updaters);
				\Bitrix\Main\Config\Option::set('main', 'updates_' . $_REQUEST["id"] . '_version', $newVersion);
				echo $newVersion;
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
		const control = event.target;
		control.className = 'no-select';
		if (action == 'version_down' || action == 'db_version_down')
		{
			ShowWaitWindow();
			BX.ajax.post(
				'module_admin.php?lang=<?= LANGUAGE_ID ?>&id='+module_id+'&count='+(oEvent.shiftKey? 10: 1)+'&<?= bitrix_sessid_get() ?>&action='+action,
				null,
				function(result)
				{
					CloseWaitWindow();
					control.className = '';
					if (result.length > 0)
					{
						control.innerHTML = result;
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
		<td>
			<div ondblclick="<?= htmlspecialcharsbx("DoAction(event, 'version_down', 'main')") ?>" id="version_for_main"><?= SM_VERSION ?></div><?
			if (class_exists('\Dev\Main\Migrator\ModuleUpdater'))
			{
				$dbVersion = \Bitrix\Main\Config\Option::get('main', 'updates_main_version');
				if ($dbVersion)
				{
					?><div title="DB" ondblclick="<?= htmlspecialcharsbx("DoAction(event, 'db_version_down', 'main')") ?>"><?=htmlspecialcharsEx($dbVersion);?></div><?php
				}
			}
			?>
		</td>
		<td nowrap><?= CDatabase::FormatDate(SM_VERSION_DATE, "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("SHORT")) ?></td>
		<td><?= Loc::getMessage("MOD_INSTALLED") ?></td>
		<td>&nbsp;</td>
	</tr>
<?php
foreach($arModules as $info)
{
	if ($info["id"] == "main")
	{
		continue;
	}
	?>
	<tr>
		<td><b><?= htmlspecialcharsex($info["name"]) ?></b> <?= htmlspecialcharsex("(".$info["id"].")") ?><br><?= $info["description"] ?></td>
		<td>
			<div ondblclick="<?= htmlspecialcharsbx("DoAction(event, 'version_down', '".CUtil::AddSlashes($info["id"])."')") ?>"><?= $info["version"] ?></div><?
			if (class_exists('\Dev\Main\Migrator\ModuleUpdater'))
			{
				$dbVersion = \Bitrix\Main\Config\Option::get('main', 'updates_' . $info["id"] . '_version');
				if ($dbVersion)
				{
					?><div title="DB" ondblclick="<?= htmlspecialcharsbx("DoAction(event, 'db_version_down', '".CUtil::AddSlashes($info["id"])."')") ?>"><?=htmlspecialcharsEx($dbVersion);?></div><?php
				}
			}
			?>
		</td>
		<td nowrap><?= CDatabase::FormatDate($info["versionDate"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("SHORT")) ?></td>
		<td nowrap><?php
			if ($info["isInstalled"])
			{
				?><?= Loc::getMessage("MOD_INSTALLED")?><?php
			}
			else
			{
				?><span class="required"><?= Loc::getMessage("MOD_NOT_INSTALLED") ?></span><?php
			}
		?></td>
		<td>
			<form action="<?= $APPLICATION->GetCurPage() ?>" method="GET" id="form_for_<?= htmlspecialcharsbx($info["id"]) ?>">
				<input type="hidden" name="action" value="" id="action_for_<?= htmlspecialcharsbx($info["id"]) ?>">
				<input type="hidden" name="lang" value="<?= LANG ?>">
				<input type="hidden" name="id" value="<?= htmlspecialcharsbx($info["id"]) ?>">
				<?= bitrix_sessid_post() ?>
				<?php
				if ($info["isInstalled"])
				{
					$disabled = (
						!$isAdmin
						|| in_array($info["id"], ["fileman", "intranet", "ui", "security", "humanresources",], true)
						|| (
							in_array($info['id'], [ 'rest', 'socialnetwork' ], true)
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
