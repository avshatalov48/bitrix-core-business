<?
use Bitrix\Main\Composite;
use Bitrix\Main\Composite\Helper;
use Bitrix\Main\Composite\Internals\AutomaticArea;
use Bitrix\Main\Config\Option;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/composite.php");
/** @var CUser $USER */
/** @var CMain $APPLICATION */

IncludeModuleLangFile(__FILE__);

$isAdmin = $USER->CanDoOperation('cache_control');
if(!$USER->CanDoOperation('cache_control') && !$USER->CanDoOperation('view_other_settings'))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (Composite\Engine::isSelfHostedPortal())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetAdditionalCSS("/bitrix/panel/main/composite.css");
$APPLICATION->AddHeadString("<style type=\"text/css\">".Composite\Engine::getInjectedCSS()."</style>");

$compositeOptions = Helper::getOptions();
$autoCompositeMode = false;
$compositeMode = false;
if (Helper::isOn())
{
	if (isset($compositeOptions["AUTO_COMPOSITE"]) && $compositeOptions["AUTO_COMPOSITE"] === "Y")
	{
		$autoCompositeMode = true;
	}
	else
	{
		$compositeMode = true;
	}
}

$tabs = array(
	array(
		"DIV" => "autocomposite",
		"TAB" => GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_TAB_TITLE").
				 ($autoCompositeMode ? " (".GetMessage("MAIN_COMPOSITE_ENABLED").")" : ""),
		"ICON" => "main_settings",
		"TITLE" => GetMessage("MAIN_COMPOSITE_TAB"),
		"ONSELECT" => "onTabSelected('autocomposite');"
	),
	array(
		"DIV" => "composite",
		"TAB" => GetMessage("MAIN_COMPOSITE_SHORT_TITLE").
				 ($compositeMode ? " (".GetMessage("MAIN_COMPOSITE_ENABLED").")" : ""),
		"ICON" => "main_settings",
		"TITLE" => GetMessage("MAIN_COMPOSITE_TAB"),
		"ONSELECT" => "onTabSelected('composite');"
	),
	array(
		"DIV" => "settings",
		"TAB" => GetMessage("MAIN_COMPOSITE_SETTINGS_TAB"),
		"ICON" => "main_settings",
		"TITLE" => GetMessage("MAIN_COMPOSITE_TAB_TITLE"),
		"ONSELECT" => "onTabSelected('settings');"
	),
	array(
		"DIV" => "groups",
		"TAB" => GetMessage("MAIN_COMPOSITE_TAB_GROUPS"),
		"ICON" => "main_settings",
		"TITLE" => GetMessage("MAIN_COMPOSITE_TAB_GROUPS_TITLE_NEW"),
		"ONSELECT" => "onTabSelected('groups');"
	),
	array(
		"DIV" => "button",
		"TAB" => GetMessage("MAIN_COMPOSITE_BANNER_SEP")." \"".GetMessage("COMPOSITE_BANNER_TEXT")."\"",
		"ICON" => "main_banner",
		"TITLE" => GetMessage("MAIN_COMPOSITE_BANNER_SEP")." &quot;".GetMessage("COMPOSITE_BANNER_TEXT")."&quot;",
		"ONSELECT" => "onTabSelected('button');"
	),
);

if (LANGUAGE_ID === "ru" || LANGUAGE_ID === "ua")
{
	$tabs[] = array(
		"DIV" => "patent",
		"TAB" => GetMessage("MAIN_COMPOSITE_PATENT_TAB"),
		"TITLE" => GetMessage("MAIN_COMPOSITE_PATENT_TAB_DESC"),
	);
}

$tabControl = new CAdminTabControl("tabControl", $tabs, false, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" &&
	check_bitrix_sessid() &&
	$isAdmin &&
	((isset($_REQUEST["composite_save_opt"]) && strlen($_REQUEST["composite_save_opt"]) > 0) ||
	 isset($_REQUEST["autocomposite_mode_button"]) ||
	 isset($_REQUEST["composite_mode_button"]))
)
{
	$compositeOptions["INCLUDE_MASK"] = $_REQUEST["composite_include_mask"];
	$compositeOptions["EXCLUDE_MASK"] = $_REQUEST["composite_exclude_mask"];
	$compositeOptions["EXCLUDE_PARAMS"] = $_REQUEST["composite_exclude_params"];
	$compositeOptions["NO_PARAMETERS"] = $_REQUEST["composite_no_parameters"];
	$compositeOptions["IGNORED_PARAMETERS"] = $_REQUEST["composite_ignored_parameters"];
	$compositeOptions["FILE_QUOTA"] = $_REQUEST["composite_quota"];
	$compositeOptions["BANNER_BGCOLOR"] = $_REQUEST["composite_banner_bgcolor"];
	$compositeOptions["BANNER_STYLE"] = $_REQUEST["composite_banner_style"];
	if (isset($_REQUEST["composite_only_parameters"]))
	{
		$compositeOptions["ONLY_PARAMETERS"] = $_REQUEST["composite_only_parameters"];
	}

	$storage = $_REQUEST["composite_storage"];
	if ( ($storage === "memcached" || $storage === "memcached_cluster") && extension_loaded("memcache"))
	{
		$compositeOptions["MEMCACHED_HOST"] = $_REQUEST["composite_memcached_host"];
		$compositeOptions["MEMCACHED_PORT"] = $_REQUEST["composite_memcached_port"];

		if (defined("BX_CLUSTER_GROUP"))
		{
			$compositeOptions["MEMCACHED_CLUSTER_GROUP"] = BX_CLUSTER_GROUP;
		}
	}
	else
	{
		$storage = "files";
	}

	$compositeOptions["STORAGE"] = $storage;

	if (isset($_REQUEST["group"]) && is_array($_REQUEST["group"]))
	{
		$compositeOptions["GROUPS"] = array();
		$b = "";
		$o = "";
		$rsGroups = CGroup::GetList($b, $o, array());
		while ($arGroup = $rsGroups->Fetch())
		{
			if ($arGroup["ID"] > 2)
			{
				if (in_array($arGroup["ID"], $_REQUEST["group"]))
				{
					$compositeOptions["GROUPS"][] = $arGroup["ID"];
				}
			}
		}
	}

	if (isset($_REQUEST["composite_domains"]) && strlen($_REQUEST["composite_domains"]) > 0)
	{
		$compositeOptions["DOMAINS"] = array();
		foreach(explode("\n", $_REQUEST["composite_domains"]) as $domain)
		{
			$domain = trim($domain, " \t\n\r");
			if ($domain != "")
			{
				$compositeOptions["DOMAINS"][$domain] = $domain;
			}
		}
	}

	if (isset($_REQUEST["composite_cache_mode"]))
	{
		if ($_REQUEST["composite_cache_mode"] === "standard_ttl")
		{
			$compositeOptions["AUTO_UPDATE"] = "Y";
			$ttl = isset($_REQUEST["composite_standard_ttl"]) ? intval($_REQUEST["composite_standard_ttl"]) : 120;
			$compositeOptions["AUTO_UPDATE_TTL"] = $ttl;
		}
		elseif ($_REQUEST["composite_cache_mode"] === "no_update")
		{
			$compositeOptions["AUTO_UPDATE"] = "N";
			$ttl = isset($_REQUEST["composite_no_update_ttl"]) ? intval($_REQUEST["composite_no_update_ttl"]) : 600;
			$compositeOptions["AUTO_UPDATE_TTL"] = $ttl;
		}
		else
		{
			$compositeOptions["AUTO_UPDATE"] = "Y";
			$compositeOptions["AUTO_UPDATE_TTL"] = "0";
		}
	}

	$compositeOptions["FRAME_MODE"] = isset($_REQUEST["composite_frame_mode"]) ? $_REQUEST["composite_frame_mode"] : "";
	$compositeOptions["FRAME_TYPE"] = isset($_REQUEST["composite_frame_type"]) ? $_REQUEST["composite_frame_type"] : "";

	if (isset($_REQUEST["autocomposite_mode_button"]) && isset($_REQUEST["auto_composite"]))
	{
		if ($_REQUEST["auto_composite"] === "Y")
		{
			Helper::setEnabled(true);
			$compositeOptions["AUTO_COMPOSITE"] = "Y";
			$compositeOptions["FRAME_MODE"] = "Y";
			$compositeOptions["FRAME_TYPE"] = "DYNAMIC_WITH_STUB";
			$compositeOptions["AUTO_UPDATE"] = "Y";
			$compositeOptions["AUTO_UPDATE_TTL"] = isset($_REQUEST["composite_standard_ttl"]) ? $_REQUEST["composite_standard_ttl"] : 120;
		}
		else if ($_REQUEST["auto_composite"] === "N")
		{
			Helper::setEnabled(false);
			$compositeOptions["AUTO_COMPOSITE"] = "N";
			$compositeOptions["FRAME_MODE"] = "N";
			$compositeOptions["AUTO_UPDATE_TTL"] = "0";
		}
	}
	elseif (isset($_REQUEST["composite_mode_button"]) && isset($_REQUEST["composite"]))
	{
		$compositeOptions["AUTO_COMPOSITE"] = "N";
		if ($_REQUEST["composite"] === "Y")
		{
			Helper::setEnabled(true);
		}
		elseif ($_REQUEST["composite"] == "N")
		{
			Helper::setEnabled(false);
		}
	}

	if (isset($_REQUEST["composite_show_banner"]) && in_array($_REQUEST["composite_show_banner"], array("Y", "N")))
	{
		Option::set("main", "~show_composite_banner", $_REQUEST["composite_show_banner"]);
	}

	Helper::setOptions($compositeOptions);
	bx_accelerator_reset();
	LocalRedirect("/bitrix/admin/composite.php?lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
}

if (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& isset($_REQUEST["check_connection"])
	&& $_REQUEST["check_connection"] === "Y"
	&& check_bitrix_sessid()
	&& $isAdmin
)
{
	$host = isset($_REQUEST["host"]) ? $_REQUEST["host"] : "";
	$port = isset($_REQUEST["port"]) ? $_REQUEST["port"] : "";

	$status = "";
	$text = "";
	if (!extension_loaded("memcache"))
	{
		$text = GetMessage("MAIN_COMPOSITE_CHECK_CONNECTION_ERR1");
		$status = "error";
	}
	elseif (strlen($host) > 0 && strlen($port) > 0 && ($memcached = new \Memcache()) && @$memcached->connect($host, $port))
	{
		$text = GetMessage("MAIN_COMPOSITE_CHECK_CONNECTION_OK");
		$status = "success";
	}
	else
	{
		$text = GetMessage("MAIN_COMPOSITE_CHECK_CONNECTION_ERR2");
		$status = "error";
	}

	header("Content-Type: application/x-javascript; charset=".LANG_CHARSET);
	die("{ status : '".$status."', text : '".CUtil::JSEscape($text)."' }");
}

$APPLICATION->SetTitle(GetMessage("MAIN_COMPOSITE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<? if (defined("FIRST_EDITION") && FIRST_EDITION=="Y"): ?>
	<?=BeginNote()?>
	<?=GetMessage("MAIN_COMPOSITE_FIRST_SITE_RESTRICTION")?>
	<?=EndNote()?>
<? endif ?>

<script>
	function onTabSelected(tabId)
	{
		var saveButton = BX("composite_save_button");
		if (tabId === "autocomposite" || tabId === "composite")
		{
			saveButton.style.visibility = "hidden";
		}
		else
		{
			saveButton.style.cssText = "";
		}
	}
</script>

<form method="POST" name="composite_form" action="<?echo $APPLICATION->GetCurPage()?>">

<?
$tabControl->Begin();
$tabControl->BeginNextTab(array(
	"showTitle" => false,
	"className" => "adm-detail-content-without-bg"
));
?>
<tr>
	<td>
		<div class="adm-composite-container">

			<div class="adm-composite-content adm-composite-first-block">
				<h2 class="adm-composite-title-container">
					<span class="adm-composite-subtitle"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_SUBTITLE")?></span>
					<span class="adm-composite-title"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_TITLE")?></span>
				</h2>
				<? if (LANGUAGE_ID === "ru" || LANGUAGE_ID === "ua"):?>
					<div class="adm-composite-video-container">
						<div class="adm-composite-video-block">
							<iframe class="adm-composite-video" src="https://www.youtube.com/embed/jo4A4Wqlksc" frameborder="0" allowfullscreen></iframe>
						</div>
					</div>
				<? endif ?>
				<div class="adm-composite-title-description"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_TITLE_DESC")?></div>
				<div class="adm-composite-blocks-content">
					<div class="adm-composite-blocks-content-part1"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_FEATURE1")?></div>
					<div class="adm-composite-blocks-content-part2"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_FEATURE2")?></div>
					<div class="adm-composite-blocks-content-part3"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_FEATURE3")?></div>
					<div class="clb"></div>
				</div>
			</div>

			<div class="adm-composite-content adm-composite-description-block">
				<h2 class="adm-composite-description-block-title"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_SIMPLE_TECH")?></h2>
				<p><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_UNIQUE_TECH")?></p>
				<table class="adm-composite-description-block-list">
					<tr>
						<td class="adm-composite-description-block-list-item-icon"><span class="adm-composite-description-block-list-item-setting"></span></td>
						<td class="adm-composite-description-block-list-item"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_AUTOMATION")?></td>
					</tr>
					<tr>
						<td class="adm-composite-description-block-list-item-icon"><span class="adm-composite-description-block-list-item-speed"></span></td>
						<td class="adm-composite-description-block-list-item"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_AUTOMATION2")?></td>
					</tr>
					<tr>
						<td class="adm-composite-description-block-list-item-icon"><span class="adm-composite-description-block-list-item-page"></span></td>
						<td class="adm-composite-description-block-list-item"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_FAST_PING")?></td>
					</tr>
				</table>
			</div>

			<? if ($autoCompositeMode):?>
				<div class="adm-composite-content adm-composite-status-block">
					<div class="adm-composite-status">
						<span class="adm-composite-on-icon"></span> <?=GetMessage("MAIN_COMPOSITE_AUTO_ON")?>
					</div>
				</div>

				<div class="adm-composite-content adm-composite-status-block">
					<input type="submit" class="adm-btn" name="autocomposite_mode_button"
						   title="<?=GetMessage("MAIN_COMPOSITE_AUTO_BUTTON_OFF")?>"
						   value="<?=GetMessage("MAIN_COMPOSITE_AUTO_BUTTON_OFF")?>"
						   <? if (!$isAdmin || (defined("FIRST_EDITION") && FIRST_EDITION == "Y")) echo " disabled" ?>
					>
					<input type="hidden" name="auto_composite" value="N">
				</div>
			<? else: ?>
				<div class="adm-composite-content adm-composite-status-block">
					<input type="submit" class="adm-btn-green" name="autocomposite_mode_button"
						   title="<?=GetMessage("MAIN_COMPOSITE_AUTO_BUTTON_ON")?>"
						   value="<?=GetMessage("MAIN_COMPOSITE_AUTO_BUTTON_ON")?>"
						   <? if (!$isAdmin || $compositeMode) echo " disabled" ?>
					>
					<input type="hidden" name="auto_composite" value="Y">
				</div>
			<? endif ?>

			<div class="adm-composite-content adm-composite-activate adm-composite-toparrow">
				<div class="adm-composite-activate-title"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_CHECKLIST_TITLE")?></div>
				<div class="adm-composite-activate-content">
					<p><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_CHECKLIST_SUBTITLE")?></p>
					<ul class="adm-composite-activate-content-task-list">
						<li class="adm-composite-activate-content-task-list-item"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_CHECKLIST_TASK1")?></li>
						<li class="adm-composite-activate-content-task-list-item"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_CHECKLIST_TASK2")?></li>
						<li class="adm-composite-activate-content-task-list-item"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_CHECKLIST_TASK3")?></li>
					</ul>
				</div>
			</div>
		</div>
	</td>
</tr>
<?
$tabControl->BeginNextTab(array(
	"showTitle" => false,
	"className" => "adm-detail-content-without-bg"
));
?>

<tr>
	<td>
		<div class="adm-composite-container">

			<div class="adm-composite-content adm-composite-first-block">
				<h2 class="adm-composite-title-container">
					<span class="adm-composite-subtitle"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_SUBTITLE")?></span>
					<span class="adm-composite-title"><?=GetMessage("MAIN_COMPOSITE_TITLE")?></span>
				</h2>
				<div class="adm-composite-title-description"><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_TITLE_DESC")?></div>
				<div class="adm-composite-blocks-content">
					<div class="adm-composite-blocks-content-part1"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_FEATURE1")?></div>
					<div class="adm-composite-blocks-content-part2"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_FEATURE2")?></div>
					<div class="adm-composite-blocks-content-part3"><?=GetMessage("MAIN_COMPOSITE_AUTO_COMPOSITE_FEATURE3")?></div>
					<div class="clb"></div>
				</div>
			</div>

			<div class="adm-composite-content adm-composite-activate">
				<div class="adm-composite-activate-title"><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_ON")?></div>
				<div class="adm-composite-activate-content">
					<p><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_DESC1")?></p>
					<p><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_DESC2")?></p>
					<p><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_DESC3")?></p>
					<ul class="adm-composite-activate-content-task-list">
						<li class="adm-composite-activate-content-task-list-item"><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_TASK1")?></li>
						<li class="adm-composite-activate-content-task-list-item"><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_TASK2")?></li>
						<li class="adm-composite-activate-content-task-list-item"><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_TASK3")?></li>
						<li class="adm-composite-activate-content-task-list-item"><?=GetMessage("MAIN_COMPOSITE_COMPOSITE_SWITCH_TASK4")?></li>
					</ul>
				</div>
			</div>

			<? if ($compositeMode):?>
				<div class="adm-composite-content adm-composite-status-block">
					<div class="adm-composite-status">
						<span class="adm-composite-on-icon"></span> <?=GetMessage("MAIN_COMPOSITE_ON")?>
					</div>
				</div>

				<div class="adm-composite-content adm-composite-status-block">
					<input type="submit" class="adm-btn" name="composite_mode_button"
						   title="<?=GetMessage("MAIN_COMPOSITE_BUTTON_OFF")?>"
						   value="<?=GetMessage("MAIN_COMPOSITE_BUTTON_OFF")?>"
						<? if (!$isAdmin || (defined("FIRST_EDITION") && FIRST_EDITION == "Y")) echo " disabled" ?>
					>
					<input type="hidden" name="composite" value="N">
				</div>
			<? else: ?>
				<div class="adm-composite-content adm-composite-status-block">
					<input type="submit" class="adm-btn-green" name="composite_mode_button"
						   title="<?=GetMessage("MAIN_COMPOSITE_BUTTON_ON")?>"
						   value="<?=GetMessage("MAIN_COMPOSITE_BUTTON_ON")?>"
						<? if (!$isAdmin || $autoCompositeMode) echo " disabled" ?>
					>
					<input type="hidden" name="composite" value="Y">
				</div>
			<? endif ?>

		</div>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
?>
<tr class="heading">
	<td colspan="2"><?=GetMessage("MAIN_COMPOSITE_VOTING_TITLE");?></td>
</tr>

<?
$frameMode = isset($compositeOptions["FRAME_MODE"]) && $compositeOptions["FRAME_MODE"] === "Y";

?>
<tr class="adm-detail-valign-top<?if ($autoCompositeMode):?> adm-composite-label-disabled<?endif?>"
	id="composite_frame_mode_row"
>
	<td width="40%"><?=GetMessage("MAIN_COMPOSITE_FRAME_MODE")?>:</td>
	<td width="60%">
		<div class="adm-list adm-list-radio">
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="radio"
						id="composite_frame_mode_pro"
						name="composite_frame_mode"
						value="Y"
						onclick="onFrameModeChanged(true)"
						<?if ($frameMode):?>checked<?endif?>
					>

				</div>
				<div class="adm-list-label">
					<label for="composite_frame_mode_pro"><?=GetMessage("MAIN_COMPOSITE_FRAME_MODE_PRO")?></label>
				</div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="radio"
						id="composite_frame_mode_contra"
						name="composite_frame_mode"
						value="N"
						style="opacity: 1"
						onclick="onFrameModeChanged(false)"
						<?if (!$frameMode):?>checked<?endif?>
						<?if ($autoCompositeMode):?>disabled<?endif?>
					>
				</div>
				<div class="adm-list-label">
					<label for="composite_frame_mode_contra"><?=GetMessage("MAIN_COMPOSITE_FRAME_MODE_CONTRA")?></label>
				</div>
			</div>
			<script>
				function onFrameModeChanged(pro)
				{
					var contentType = BX("composite_frame_type_row");
					contentType.style.display = pro ? "" : "none";
				}
			</script>
		</div>
	</td>
</tr>
<?
$frameType = "STATIC";
if (isset($compositeOptions["FRAME_TYPE"]) && in_array($compositeOptions["FRAME_TYPE"], AutomaticArea::getFrameTypes()))
{
	$frameType = $compositeOptions["FRAME_TYPE"];
}
?>
<tr class="adm-detail-valign-top<? if ($autoCompositeMode):?> adm-composite-label-disabled<?endif?>"
	id="composite_frame_type_row"
	<? if (!$frameMode):?>style="display: none"<?endif?>
>
	<td width="40%"><?=GetMessage("MAIN_COMPOSITE_FRAME_TYPE")?>:</td>
	<td width="60%">
		<div class="adm-list adm-list-radio">
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="radio"
						id="composite_frame_type_dynamic_with_stub"
						name="composite_frame_type"
						value="DYNAMIC_WITH_STUB"
						<? if ($frameType === "DYNAMIC_WITH_STUB"):?>checked<?endif?>
					>
				</div>
				<div class="adm-list-label">
					<label for="composite_frame_type_dynamic_with_stub"><?
						echo GetMessage("MAIN_COMPOSITE_FRAME_TYPE_DYNAMIC_WITH_STUB")
					?></label>
				</div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="radio"
						id="composite_frame_type_static"
						name="composite_frame_type"
						value="STATIC"
						style="opacity: 1"
						<? if ($frameType === "STATIC"):?>checked<?endif?>
						<? if ($autoCompositeMode):?>disabled<?endif?>
					>
				</div>
				<div class="adm-list-label">
					<label for="composite_frame_type_static"><?=GetMessage("MAIN_COMPOSITE_FRAME_TYPE_STATIC")?></label>
				</div>
			</div>
		</div>

	</td>
</tr>
<tr>
	<td width="40%">

	</td>
	<td width="60%">
		<i><?=GetMessage("MAIN_COMPOSITE_FRAME_DESC")?></i>
	</td>
</tr>


<tr class="heading">
	<td colspan="2"><?=GetMessage("MAIN_COMPOSITE_CACHE_REWRITING")?></td>
</tr>

<?
$autoUpdate = isset($compositeOptions["AUTO_UPDATE"]) && $compositeOptions["AUTO_UPDATE"] === "N" ? false : true;
$defaultAutoUpdateTTL = $autoUpdate ? 0 : 600;
$autoUpdateTTL = isset($compositeOptions["AUTO_UPDATE_TTL"]) ? intval($compositeOptions["AUTO_UPDATE_TTL"]) : $defaultAutoUpdateTTL;
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?=GetMessage("MAIN_COMPOSITE_CACHE_REWRITING")?>:</td>
	<td width="60%">
		<div class="adm-list adm-list-radio">
			<?
			$isTTLMode = $autoUpdate && $autoUpdateTTL > 0;
			?>
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="radio"
						id="composite_cache_mode_standard_ttl"
						name="composite_cache_mode"
						value="standard_ttl"
						onclick="onCacheModeChanged('standard_ttl')"
						<?if ($isTTLMode):?>checked<?endif?>
					>

				</div>
				<div class="adm-list-label">
					<label for="composite_cache_mode_standard_ttl">
						<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_STANDARD_TTL")?>
						<div class="adm-composite-cache-mode-hint">
							<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_STANDARD_TTL_DESC")?>
						</div>
					</label>

					<div class="adm-composite-cache-ttl<?if (!$isTTLMode):?> adm-composite-label-disabled<?endif?>">
						<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_TTL")?>:
						<input
							id="composite_standard_ttl"
							name="composite_standard_ttl"
							type="text"
							size="8"
							value="<?=($isTTLMode ? $autoUpdateTTL : 120)?>"
							<?if (!$isTTLMode):?>disabled<?endif?>
						>
						<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_TTL_UNIT_SEC")?>
					</div>
				</div>
			</div>
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="radio"
						id="composite_cache_mode_standard"
						name="composite_cache_mode"
						value="standard"
						onclick="onCacheModeChanged('standard')"
						<?if ($autoUpdate && $autoUpdateTTL <= 0):?>checked<?endif?>
					>
				</div>
				<div class="adm-list-label">
					<label for="composite_cache_mode_standard">
						<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_STANDARD")?>
						<div class="adm-composite-cache-mode-hint">
							<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_STANDARD_DESC")?>
						</div>
					</label>
				</div>
			</div>
			<?
			$isNoUpdateMode = !$autoUpdate;
			?>
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="radio"
						id="composite_cache_mode_no_update_ttl"
						name="composite_cache_mode"
						value="no_update"
						onclick="onCacheModeChanged('no_update')"
						<?if ($isNoUpdateMode):?>checked<?endif?>
						<?if ($autoCompositeMode):?>disabled<?endif?>
					>

				</div>
				<div class="adm-list-label<?if ($autoCompositeMode):?> adm-composite-label-disabled<?endif?>"
					 id="composite_cache_mode_no_update_option">
					<label for="composite_cache_mode_no_update_ttl">
						<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_NO_UPDATE")?>
						<div class="adm-composite-cache-mode-hint">
							<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_NO_UPDATE_DESC")?>
						</div>
					</label>
					<div class="adm-composite-cache-ttl<?if (!$isNoUpdateMode):?> adm-composite-label-disabled<?endif?>">
						<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_NO_UPDATE_TTL")?>:
						<input
							id="composite_no_update_ttl"
							name="composite_no_update_ttl"
							type="text"
							size="8"
							value="<?=($isNoUpdateMode ? $autoUpdateTTL : 600)?>"
							<?if (!$isNoUpdateMode):?>disabled<?endif?>
						>
						<?=GetMessage("MAIN_COMPOSITE_CACHE_MODE_TTL_UNIT_SEC")?>
					</div>
				</div>
			</div>
			<script>
				function onCacheModeChanged(mode)
				{
					var noUpdateTTL = BX("composite_no_update_ttl");
					var standardTTL = BX("composite_standard_ttl");

					if (mode === "standard_ttl")
					{
						standardTTL.disabled = false;
						noUpdateTTL.disabled = true;

						BX.removeClass(standardTTL.parentNode, "adm-composite-label-disabled");
						BX.addClass(noUpdateTTL.parentNode, "adm-composite-label-disabled");
					}
					else if (mode === "no_update")
					{
						standardTTL.disabled = true;
						noUpdateTTL.disabled = false;

						BX.addClass(standardTTL.parentNode, "adm-composite-label-disabled");
						BX.removeClass(noUpdateTTL.parentNode, "adm-composite-label-disabled");
					}
					else
					{
						standardTTL.disabled = true;
						noUpdateTTL.disabled = true;

						BX.addClass(standardTTL.parentNode, "adm-composite-label-disabled");
						BX.addClass(noUpdateTTL.parentNode, "adm-composite-label-disabled");

					}
				}
			</script>
		</div>
	</td>
</tr>



<tr class="heading">
	<td colspan="2"><?=GetMessage("MAIN_COMPOSITE_OPT")?></td>
</tr>
<?
if (!is_array($compositeOptions["DOMAINS"]) || count($compositeOptions["DOMAINS"]) < 1)
{
	$compositeOptions["DOMAINS"] = array(Helper::getHttpHost());
}
?>
<tr class="adm-detail-valign-top">
	<td width="40%" class="adm-required-field"><?=GetMessage("MAIN_COMPOSITE_DOMAINS")?>:</td>
	<td width="60%">
		<textarea name="composite_domains" rows="5" style="width:100%"><?echo htmlspecialcharsEx(implode("\n", $compositeOptions["DOMAINS"]))?></textarea><br>
	</td>
</tr>
<tr class="adm-detail-valign-top">
	<td width="40%"><?=GetMessage("MAIN_COMPOSITE_INC_MASK")?>:</td>
	<td width="60%">
		<textarea name="composite_include_mask" rows="5" style="width:100%"><?echo htmlspecialcharsEx($compositeOptions["INCLUDE_MASK"])?></textarea>
	</td>
</tr>
<tr class="adm-detail-valign-top">
	<td><?echo GetMessage("MAIN_COMPOSITE_EXC_MASK");?>:</td>
	<td>
		<textarea name="composite_exclude_mask" rows="5" style="width:100%"><?echo htmlspecialcharsEx($compositeOptions["EXCLUDE_MASK"])?></textarea>
	</td>
</tr>

<tr class="adm-detail-valign-top">
	<td><?=GetMessage("MAIN_COMPOSITE_IGNORED_PARAMETERS")?>:</td>
	<td>
		<textarea name="composite_ignored_parameters" rows="5" style="width:100%"><?echo htmlspecialcharsEx($compositeOptions["IGNORED_PARAMETERS"])?></textarea>
	</td>
</tr>

<tr>
	<td><label for="composite_no_parameters"><?=GetMessage("MAIN_COMPOSITE_NO_PARAMETERS")?>:</label></td>
	<td>
		<input type="hidden" name="composite_no_parameters" value="N">
		<input type="checkbox" name="composite_no_parameters" onclick="onParamsCheckboxClick(this.checked)"
			   id="composite_no_parameters" value="Y" <? if ($compositeOptions["NO_PARAMETERS"] === "Y")
			echo 'checked="checked"' ?>>
	</td>
</tr>

<tr>
	<td><?echo GetMessage("MAIN_COMPOSITE_ONLY_PARAMETERS");?>:</td>
	<td>
		<input type="text" size="45" style="width:100%" name="composite_only_parameters" id="composite_only_parameters"
			   value="<? echo htmlspecialcharsbx($compositeOptions["ONLY_PARAMETERS"]) ?>"
				<?if ($compositeOptions["NO_PARAMETERS"] !== "Y"):?>disabled<?endif?>
		>
	</td>
</tr>

<tr class="adm-detail-valign-top">
	<td><?=GetMessage("MAIN_COMPOSITE_EXCLUDE_BY_PARAMS")?>:</td>
	<td>
		<textarea name="composite_exclude_params" rows="5" style="width:100%"><?
			echo htmlspecialcharsEx($compositeOptions["EXCLUDE_PARAMS"])
		?></textarea>
	</td>
</tr>

<tr class="heading">
	<td colspan="2"><?=GetMessage("MAIN_COMPOSITE_STORAGE_TITLE")?></td>
</tr>
<?
$storages = array(
	"files" => array(
		"name" => GetMessage("MAIN_COMPOSITE_STORAGE_FILES")
	),

	"memcached" => array(
		"name" => "memcached",
		"extension" => "memcache"
	),

	"memcached_cluster" => array(
		"name" => "memcached cluster",
		"extension" => "memcache",
		"module" => "cluster"
	),
);

$currentStorage = "files";
if (isset($compositeOptions["STORAGE"]) && array_key_exists($compositeOptions["STORAGE"], $storages))
{
	$currentStorage = $compositeOptions["STORAGE"];
}

//Defaults for memcached
if (!isset($compositeOptions["MEMCACHED_HOST"]))
{
	$compositeOptions["MEMCACHED_HOST"] = "localhost";
}

if (!isset($compositeOptions["MEMCACHED_PORT"]))
{
	$compositeOptions["MEMCACHED_PORT"] = "11211";
}
?>
<tr>
	<td><?echo GetMessage("MAIN_COMPOSITE_STORAGE");?>:</td>
	<td>
		<script type="text/javascript">
			function onStorageSelect(select)
			{
				var hostRow = BX("composite_memcached_host_row", true);
				var portRow = BX("composite_memcached_port_row", true);
				var hintRow = BX("composite_memcached_hint_row", true);
				var clusterRow = BX("composite_cluster_hint_row", true);
				var quotaRow = BX("composite_quota_row", true);
				var quotaSizeRow = BX("composite_quota_size_row", true);
				if (select.value === "memcached")
				{
					hostRow.style.cssText = "";
					portRow.style.cssText = "";
					hintRow.style.cssText = "";
				}
				else
				{
					hostRow.style.display = "none";
					portRow.style.display = "none";
					hintRow.style.display = "none";
				}

				if (select.value === "memcached_cluster")
				{
					clusterRow.style.cssText = "";
				}
				else
				{
					clusterRow.style.display = "none";
				}

				if (select.value !== "files")
				{
					quotaRow.style.display = "none";
					quotaSizeRow && (quotaSizeRow.style.display = "none");
				}
				else
				{
					quotaRow.style.cssText = "";
					quotaSizeRow && (quotaSizeRow.style.cssText = "");
				}
			}
		</script>
		<select name="composite_storage" id="composite_storage" style="width:300px;" onchange="onStorageSelect(this)">
			<?
			foreach ($storages as $storageId => $storage):
				$disabled = "";
				$nameDesc = "";
				$selected = $currentStorage == $storageId ? " selected" : "";
				if (isset($storage["module"]) && !\Bitrix\Main\ModuleManager::isModuleInstalled($storage["module"]))
				{
					$disabled = " disabled";
					$nameDesc = " (".GetMessage("MAIN_COMPOSITE_MODULE_ERROR", array("#MODULE#" => $storage["module"])).")";
				}
				elseif (isset($storage["extension"]) && strlen($storage["extension"]) > 0 && !extension_loaded($storage["extension"]))
				{
					$disabled = " disabled";
					$nameDesc = " (".GetMessage("MAIN_COMPOSITE_EXT_ERROR", array("#EXTENSION#" => $storage["extension"])).")";
				}

				?>
				<option value="<?=htmlspecialcharsbx($storageId)?>"<?=$selected?><?=$disabled?>><?=htmlspecialcharsbx($storage["name"])?><?=$nameDesc?></option>
			<?endforeach?>
		</select>
	</td>
</tr>
<tr id="composite_memcached_host_row" <?if ($compositeOptions["STORAGE"] !== "memcached") echo 'style="display:none"'?>>
	<td class="adm-required-field"><?=GetMessage("MAIN_COMPOSITE_MEMCACHED_HOST")?>:</td>
	<td>
		<input type="text" size="45" style="width:300px" name="composite_memcached_host" value="<?echo htmlspecialcharsbx($compositeOptions["MEMCACHED_HOST"])?>">
	</td>
</tr>

<tr id="composite_memcached_port_row" <?if ($compositeOptions["STORAGE"] !== "memcached") echo 'style="display:none"'?>>
	<td class="adm-required-field"><?=GetMessage("MAIN_COMPOSITE_MEMCACHED_PORT")?>:</td>
	<td>
		<input type="text" size="45" style="width:50px" name="composite_memcached_port" value="<?echo htmlspecialcharsbx($compositeOptions["MEMCACHED_PORT"])?>">

	</td>
</tr>
<tr id="composite_memcached_hint_row" <?if ($compositeOptions["STORAGE"] !== "memcached") echo 'style="display:none"'?>>
	<td class="adm-required-field"></td>
	<td>
		<script type="text/javascript">
			function checkConnection()
			{
				BX.ajax({
					method: "POST",
					dataType: 'json',
					url: window.location.href,
					data: {
						sessid : BX.bitrix_sessid(),
						check_connection : "Y",
						host : document.forms["composite_form"].elements["composite_memcached_host"].value,
						port : document.forms["composite_form"].elements["composite_memcached_port"].value
					},
					onsuccess: function(result) {
						var status = BX("check_connection_status");
						if (result && result.text)
						{
							var color = "green";
							if (result.status && result.status === "error")
							{
								color = "red";
							}

							status.style.color = color;
							status.innerHTML = result.text;
						}
					}
				});
			}
		</script>
		<input type="button" name="" value="<?=GetMessage("MAIN_COMPOSITE_CHECK_CONNECTION")?>" onclick="checkConnection()" />&nbsp;<span id="check_connection_status"></span><br><br><br>
		<?=GetMessage("MAIN_COMPOSITE_HOST_HINT");?>
	</td>
</tr>
<tr id="composite_cluster_hint_row" <?if ($compositeOptions["STORAGE"] !== "memcached_cluster") echo 'style="display:none"'?>>
	<td class="adm-required-field"></td>
	<td><?=GetMessage("MAIN_COMPOSITE_CLUSTER_HINT", array(
			"#A_START#" => "<a href=\"/bitrix/admin/cluster_memcache_list.php?lang=".LANGUAGE_ID."&group_id=".(defined("BX_CLUSTER_GROUP") ? BX_CLUSTER_GROUP : 1)."\">",
			"#A_END#" => "</a>"
		));?></td>
</tr>

<tr id="composite_quota_row" <?if ($compositeOptions["STORAGE"] !== "files") echo 'style="display:none"'?>>
	<td><?=GetMessage("MAIN_COMPOSITE_QUOTA")?>:</td>
	<td>
		<input type="text" size="8" name="composite_quota" value="<?echo intval($compositeOptions["FILE_QUOTA"])?>">
	</td>
</tr>
<?
if(Helper::isOn())
{
	$cacheSize = Helper::getCacheFileSize();?>
	<tr id="composite_quota_size_row" <?if ($compositeOptions["STORAGE"] !== "files") echo 'style="display:none"'?>>
		<td><?=GetMessage("MAIN_COMPOSITE_STAT_FILE_SIZE")?></td>
		<td><?=CFile::FormatSize($cacheSize)?></td>
	</tr>
	<?
}
?>
<tr>
	<td></td>
	<td>
		<a href="/bitrix/admin/cache.php?lang=<?=LANGUAGE_ID?>&cachetype=html&tabControl_active_tab=fedit2"><?=GetMessage("MAIN_COMPOSITE_CLEAR_CACHE")?></a>
	</td>
</tr>
<?
$tabControl->BeginNextTab();
$arUsedGroups = array();
$groups = $compositeOptions["GROUPS"];
$arGROUPS = array();
$b = "";
$o = "";
$rsGroups = CGroup::GetList($b, $o, array("ACTIVE"=>"Y", "ADMIN"=>"N", "ANONYMOUS"=>"N"));
while ($arGroup = $rsGroups->Fetch())
{
	$arGROUPS[] = $arGroup;
}

?>

	<select style="width: 400px" disabled>
		<option value=""><?=GetMessage("MAIN_COMPOSITE_ANONYMOUS_GROUP")?></option>
	</select><br><br>

<?
if(is_array($groups))
{
	foreach($groups as $group)
	{
		?>

			<select style="width: 400px" name="group[]">
				<option value=""><?=GetMessage("MAIN_NO")?></option>
				<?
				foreach ($arGROUPS as $arGroup)
				{
					?>
					<option
						value="<? echo htmlspecialcharsbx($arGroup["ID"]) ?>"
						<? echo $group == $arGroup["ID"] ? 'selected="selected"' : '' ?>
						><? echo htmlspecialcharsEx($arGroup["NAME"] . " [" . $arGroup["ID"] . "]") ?></option>
				<?
				}
				?>
			</select><br><br>
	<?
	}
}
?>
	<div id="groups-select" style="display: none;">
		<select style="width: 400px" name="group[]">
			<option value=""><?=GetMessage("MAIN_COMPOSITE_SELECT_GROUP") ?></option>
			<?
			foreach ($arGROUPS as $arGroup)
			{
				?>
				<option
					value="<? echo htmlspecialcharsbx($arGroup["ID"]) ?>"
					><? echo htmlspecialcharsEx($arGroup["NAME"] . " [" . $arGroup["ID"] . "]") ?></option>
			<?
			}
			?>
		</select><br><br>
	</div>
	<div id="groups-add">
		<a class="bx-action-href" href="javascript:addGroups()"><?=GetMessage("MAIN_ADD")?></a>
		<script>
			function addGroups()
			{
				var groupsSelect = BX('groups-select');
				var row = BX.clone(groupsSelect);
				row.style.display = "block";
				groupsSelect.parentNode.insertBefore(row, BX('groups-add'));
			}
		</script>
	</div>
	<?
$tabControl->BeginNextTab();?>


<?
$showBanner = Composite\Engine::isBannerEnabled();
?>
<tr>
	<td colspan="2">
		<div class="adm-list adm-list-radio">
			<div class="adm-list-item">
				<div class="adm-list-control">
					<input
						type="checkbox"
						value="Y"
						id="composite_show_banner_checkbox"
						<?if ($showBanner):?>checked<?endif?>
						onclick="onShowBannerClick(this)"
					>
					<input
						type="hidden"
						name="composite_show_banner"
						id="composite_show_banner"
						value="<?=($showBanner ? "Y" : "N")?>"
					>
				</div>
				<div class="adm-list-label">
					<label for="composite_show_banner_checkbox"><?=GetMessage("MAIN_COMPOSITE_SHOW_BANNER")?></label>
				</div>
			</div>
		</div>
		<script>
			function onShowBannerClick(checkbox)
			{
				BX("composite_show_banner").value = checkbox.checked ? "Y" : "N";
				BX("composite_button_disclaimer_row").style.display = checkbox.checked ? "" : "none";
				BX("composite_button_row").style.display = checkbox.checked ? "" : "none";
			}
		</script>
	</td>
</tr>
<tr id="composite_button_disclaimer_row" <?if (!$showBanner):?>style="display: none"<?endif?>>
	<td colspan="2">
		<?=BeginNote()?><?=GetMessage("MAIN_COMPOSITE_BANNER_DISCLAIMER")?><?=EndNote()?>
	</td>
</tr>

<tr class="adm-detail-valign-top" id="composite_button_row" <?if (!$showBanner):?>style="display: none"<?endif?>>
	<td><?=GetMessage("MAIN_COMPOSITE_BANNER_SELECT_STYLE")?>:</td>
	<td>
		<div class="adm-composite-btn-wrap">
			<div class="adm-composite-btn-select-wrap">
			<span class="adm-composite-btn-select" onclick="showPopup(this)">
				<span id="composite-banner" class="bx-composite-btn bx-btn-white"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
				<span class="adm-composite-btn-select-icon"></span>
			</span>
			<span class="adm-composite-btn-checkbox-wrap">
				<input type="checkbox" id="composite_white_bgcolor" class="adm-composite-btn-checkbox" onclick="setWhiteBgColor(this.checked)"/><label class="adm-composite-btn-label-bg" for="composite_white_bgcolor"><?=GetMessage("MAIN_COMPOSITE_BANNER_STYLE_WHITE")?></label>
			</span>

			</div>
			<div class="adm-composite-btn-color">
				<div class="adm-composite-btn-label"><?=GetMessage("MAIN_COMPOSITE_BANNER_BGCOLOR")?></div>
				<input type="text" name="composite_banner_bgcolor" id="composite_banner_bgcolor" value="" class="adm-composite-btn-color-inp"/>
			</div>
			<div class="adm-composite-btn-logo-block">
				<div class="adm-composite-btn-label"><?=GetMessage("MAIN_COMPOSITE_BANNER_STYLE")?></div>
				<div class="adm-composite-btn-logo-list">
				<span class="adm-composite-btn-logo">
					<label class="adm-composite-btn-logo-img adm-composite-btn-logo-white" for="composite_banner_style_white"></label><input id="composite_banner_style_white" class="adm-composite-btn-logo-radio" type="radio" name="composite_banner_style" value="white" onclick="changeBannerType(null, 'white')" />
				</span><span class="adm-composite-btn-logo">
					<label class="adm-composite-btn-logo-img adm-composite-btn-logo-grey" for="composite_banner_style_grey"></label><input id="composite_banner_style_grey" class="adm-composite-btn-logo-radio" type="radio" name="composite_banner_style" value="grey" onclick="changeBannerType(null, 'grey')"/>
				</span><span class="adm-composite-btn-logo">
					<label class="adm-composite-btn-logo-img adm-composite-btn-logo-red" for="composite_banner_style_red"></label><input id="composite_banner_style_red" class="adm-composite-btn-logo-radio" type="radio" name="composite_banner_style" value="red" onclick="changeBannerType(null, 'red')" />
				</span><span class="adm-composite-btn-logo">
					<label class="adm-composite-btn-logo-img adm-composite-btn-logo-black" for="composite_banner_style_black"></label><input id="composite_banner_style_black" class="adm-composite-btn-logo-radio" type="radio" name="composite_banner_style" value="black" onclick="changeBannerType(null, 'black')"/>
				</span>
				</div>
			</div>
		</div>

		<div id="btn-popup" class="adm-composite-btn-popup" style="display: none;">
			<span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #000000;" href="#" onclick="selectPreset('#000000', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #e94524;" href="#" onclick="selectPreset('#E94524', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #3a424d;" href="#" onclick="selectPreset('#3A424D', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #d37222;" href="#" onclick="selectPreset('#D37222', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-grey" style="background-color: #dae1e5;" href="#" onclick="selectPreset('#DAE1E5', 'grey')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-grey bx-btn-border" style="background-color: #ffffff;" href="#" onclick="selectPreset('#FFFFFF', 'grey' , true)"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #59b7cf;" href="#" onclick="selectPreset('#59B7CF', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #2f6e73;" href="#" onclick="selectPreset('#2F6E73', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-red bx-btn-border" style="background-color: #ffffff;" href="#" onclick="selectPreset('#FFFFFF', 'red', true)"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #51626b;" href="#" onclick="selectPreset('#51626B', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #83a61a;" href="#" onclick="selectPreset('#83A61A', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-black bx-btn-border" style="background-color: #ffffff;" href="#" onclick="selectPreset('#FFFFFF', 'black', true)"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #b39c85;" href="#" onclick="selectPreset('#B39C85', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #ff8534;" href="#" onclick="selectPreset('#FF8534', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span><span class="adm-composite-btn-popup-wrap">
				<span class="bx-composite-btn bx-btn-white" style="background-color: #51c1ef;" href="#" onclick="selectPreset('#51C1EF', 'white')"><?=GetMessage("COMPOSITE_BANNER_TEXT")?></span>
			</span>
		</div>
		<script type="text/javascript">

			BX.ready(function() {

				var banner = BX("composite-banner");
				var bgcolorInput = BX("composite_banner_bgcolor");
				var whiteRadio = BX("composite_banner_style_white");
				var whiteBgCheckbox = BX("composite_white_bgcolor");
				var radio = document.forms["composite_form"].elements["composite_banner_style"];
				var lastStyle = "";
				var bgColorBeforeBorder = "";
				var styleBeforeBorder = "";

				window.changeBannerType = function(bgcolor, style, border)
				{
					if (border === true)
					{
						styleBeforeBorder = radio.value;
						bgColorBeforeBorder = bgcolorInput.value;

						bgcolorInput.disabled = true;
						whiteRadio.disabled = true;
						whiteBgCheckbox.checked = true;
						BX.addClass(banner, "bx-btn-border");
					}
					else if (border === false)
					{
						bgcolorInput.disabled = false;
						whiteRadio.disabled = false;
						whiteBgCheckbox.checked = false;
						BX.removeClass(banner, "bx-btn-border");
					}

					if (BX.type.isNotEmptyString(bgcolor))
					{
						banner.style.backgroundColor = bgcolor;
						bgcolorInput.value = bgcolor;
					}

					if (BX.type.isNotEmptyString(style))
					{
						BX.removeClass(banner, lastStyle);
						lastStyle = "bx-btn-" + style;
						BX.addClass(banner, lastStyle);
						BX("composite_banner_style_" + style, true).checked = true;
					}
				};

				window.selectPreset = function(bgcolor, style, border)
				{
					changeBannerType(bgcolor, style, border === true);
					window.bannerPopup.close();
				};

				window.onBgColorChanged = function()
				{
					banner.style.backgroundColor = bgcolorInput.value;
				};

				window.setWhiteBgColor = function(border)
				{
					if (border)
					{
						changeBannerType(
							"#FFFFFF",
							lastStyle == "bx-btn-white" || lastStyle == "" ? "red" : null,
							true
						);
					}
					else
					{
						if (bgColorBeforeBorder == "")
						{
							bgColorBeforeBorder = "#E94524";
						}

						if (styleBeforeBorder == "")
						{
							styleBeforeBorder = "white";
						}
						changeBannerType(bgColorBeforeBorder, styleBeforeBorder, false);
					}
				};

				window.showPopup = function(btn)
				{
					window.bannerPopup = BX.PopupWindowManager.create("adm-composite-btn-popup", btn, {
						content: BX("btn-popup"),
						lightShadow: true,
						closeByEsc : true,
						autoHide : true,
						offsetTop : 5
					});
					window.bannerPopup.show();
				};

				window.onParamsCheckboxClick = function(show)
				{
					var input = BX("composite_only_parameters", true);
					if (show)
					{
						input.disabled = false;
					}
					else
					{
						input.disabled = true;
					}
				};

				var bgcolor = "<?=CUtil::JSEscape($compositeOptions["BANNER_BGCOLOR"])?>";
				var style = "<?=CUtil::JSEscape($compositeOptions["BANNER_STYLE"])?>";
				if (!BX.type.isNotEmptyString(bgcolor))
				{
					bgcolor = "#E94524";
				}

				if (!BX.type.isNotEmptyString(style))
				{
					style = "white";
				}

				changeBannerType(bgcolor, style, BX.util.in_array(bgcolor.toUpperCase(), ["#FFF", "#FFFFFF", "WHITE"]));

				BX.bind(bgcolorInput, "change", onBgColorChanged);
				BX.bind(bgcolorInput, "cut", onBgColorChanged);
				BX.bind(bgcolorInput, "paste", onBgColorChanged);
				BX.bind(bgcolorInput, "drop", onBgColorChanged);
				BX.bind(bgcolorInput, "keyup", onBgColorChanged);
				BX.bind(document.forms["composite_form"], "submit", function() {  bgcolorInput.disabled = false; })
			});

		</script>
	</td>
</tr>


<? if (LANGUAGE_ID === "ru" || LANGUAGE_ID === "ua"):
	$tabControl->BeginNextTab();
?>

<tr>
	<td>
		<img class="adm-composite-patent" src="/bitrix/panel/main/images/composite/patent_composit.jpg" alt="">
	</td>
</tr>


<? endif ?>

<?
$tabControl->Buttons(array(
	"disabled" => !$isAdmin,
	"btnSave" => false,
	"btnApply" => false,
	"btnCancel" => false,
));


$hideButton = in_array($tabControl->GetSelectedTab(), array("autocomposite", "composite"));

?>
	<input type="submit" id="composite_save_button" name="composite_save_opt" class="adm-btn-save"
		   <? if ($hideButton): ?>style="visibility: hidden"<? endif ?>
		   value="<? echo GetMessage("MAIN_COMPOSITE_SAVE"); ?>"<? if (!$isAdmin) echo " disabled" ?>>
<?
$tabControl->End();
?>
<?echo bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>