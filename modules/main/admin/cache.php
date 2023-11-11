<?

use Bitrix\Main\Application;

if(
	isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST"
	&& isset($_REQUEST["ajax"]) && $_REQUEST["ajax"]=="y"
	&& isset($_REQUEST["clearcache"]) && $_REQUEST["clearcache"] == "Y"
)
{
	define("STOP_STATISTICS", true);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "settings/settings/cache.php");
/** @var CUser $USER */
/** @var CMain $APPLICATION */

$isAdmin = $USER->CanDoOperation('cache_control');

IncludeModuleLangFile(__FILE__);
if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& isset($_REQUEST["ajax"])
	&& $_REQUEST["ajax"]=="y"
	&& isset($_REQUEST["clearcache"])
	&& $_REQUEST["clearcache"] == "Y"
)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	if(!check_bitrix_sessid() || !$isAdmin)
	{
		?>
		<script>
			window.location = '/bitrix/admin/cache.php?lang=<?echo LANGUAGE_ID?>&tabControl_active_tab=fedit2';
		</script>
		<?
		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
	}

	if(
		isset($_REQUEST["cachetype"])
		&& $_REQUEST["cachetype"] == "landing"
		&& \Bitrix\Main\Loader::includeModule("landing")
	)
	{
		\Bitrix\Landing\Block::clearRepositoryCache();
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("main_cache_finished"),
			"HTML" => true,
			"TYPE" => "OK",
		));
		?>
		<script type="text/javascript">
			CloseWaitWindow();
			EndClearCache();
		</script>
		<?
		require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
	}
	else if(
		(isset($_REQUEST["cachetype"]) && $_REQUEST["cachetype"] === "html")
		|| \Bitrix\Main\Data\Cache::getCacheEngineType() == "cacheenginefiles"
	)
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/cache_files_cleaner.php");

		if(isset($_POST["path"]) && is_string($_POST["path"]) && mb_strlen($_POST["path"]))
		{
			$path = $_POST["path"];
		}
		else
		{
			$path = "";
			Application::getInstance()->getSession()["CACHE_STAT"] = array();
		}

		$bDoNotCheckExpiredDate =
			isset($_REQUEST["cachetype"])
			&& (
				$_REQUEST["cachetype"] === "all"
				|| $_REQUEST["cachetype"] === "menu"
				|| $_REQUEST["cachetype"] === "managed"
				|| $_REQUEST["cachetype"] === "html"
			)
		;

		$curentTime = time();
		$endTime = time()+5;

		$obCacheCleaner = new CFileCacheCleaner($_REQUEST["cachetype"] ?? '');
		if(!$obCacheCleaner->InitPath($path))
		{
			ShowError(GetMessage("main_cache_wrong_cache_path"));
			?>
			<script>
				CloseWaitWindow();
			</script>
			<?
			require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
		}
	}

	if (isset($_REQUEST["cachetype"]) && $_REQUEST["cachetype"] === "html")
	{

		$obCacheCleaner->Start();
		$space_freed = 0;
		while($file = $obCacheCleaner->GetNextFile())
		{
			if(
				is_string($file)
				&& !preg_match("/(\\.enabled|\\.size|.config\\.php)\$/", $file)
			)
			{
				$file_size = filesize($file);

				if (!isset(Application::getInstance()->getSession()["CACHE_STAT"]["scanned"]))
				{
					Application::getInstance()->getSession()["CACHE_STAT"]["scanned"] = 0;
				}
				Application::getInstance()->getSession()["CACHE_STAT"]["scanned"]++;


				if (!isset(Application::getInstance()->getSession()["CACHE_STAT"]["space_total"]))
				{
					Application::getInstance()->getSession()["CACHE_STAT"]["space_total"] = 0;
				}

				Application::getInstance()->getSession()["CACHE_STAT"]["space_total"]+=$file_size;

				if(@unlink($file))
				{
					if (!isset(Application::getInstance()->getSession()["CACHE_STAT"]["deleted"]))
					{
						Application::getInstance()->getSession()["CACHE_STAT"]["deleted"] = 0;
					}
					Application::getInstance()->getSession()["CACHE_STAT"]["deleted"]++;

					if (!isset(Application::getInstance()->getSession()["CACHE_STAT"]["space_freed"]))
					{
						Application::getInstance()->getSession()["CACHE_STAT"]["space_freed"] = 0;
					}

					Application::getInstance()->getSession()["CACHE_STAT"]["space_freed"]+=$file_size;
					$space_freed+=$file_size;
				}
				else
				{
					if (!isset(Application::getInstance()->getSession()["CACHE_STAT"]["errors"]))
					{
						Application::getInstance()->getSession()["CACHE_STAT"]["errors"] = 0;
					}

					Application::getInstance()->getSession()["CACHE_STAT"]["errors"]++;
				}

				if(time() >= $endTime)
					break;
			}

			//no more than 200 files per second
			usleep(5000);
		}
		\Bitrix\Main\Composite\Helper::updateCacheFileSize(-$space_freed);
	}
	elseif(\Bitrix\Main\Data\Cache::getCacheEngineType() == "cacheenginefiles")
	{
		$obCacheCleaner->Start();
		while($file = $obCacheCleaner->GetNextFile())
		{
			if(is_string($file))
			{
				$date_expire = $obCacheCleaner->GetFileExpiration($file);
				if($date_expire)
				{
					$file_size = filesize($file);

					Application::getInstance()->getSession()["CACHE_STAT"]["scanned"]++;
					Application::getInstance()->getSession()["CACHE_STAT"]["space_total"]+=$file_size;

					if(
						$bDoNotCheckExpiredDate
						|| ($date_expire < $curentTime)
					)
					{
						if(@unlink($file))
						{
							Application::getInstance()->getSession()["CACHE_STAT"]["deleted"]++;
							Application::getInstance()->getSession()["CACHE_STAT"]["space_freed"]+=$file_size;
						}
						else
						{
							Application::getInstance()->getSession()["CACHE_STAT"]["errors"]++;
						}
					}
				}

				if(time() >= $endTime)
					break;
			}

			//no more than 200 files per second
			usleep(5000);
		}
	}
	else
	{
		$file = false;
		Application::getInstance()->getSession()["CACHE_STAT"] = array();
	}

	if(is_string($file))
	{
		$currentPath = mb_substr($file, mb_strlen($_SERVER["DOCUMENT_ROOT"]));
		_CFileTree::ExtractFileFromPath($currentPath);
		CAdminMessage::ShowMessage(array(
			"MESSAGE"=>GetMessage("main_cache_in_progress"),
			"DETAILS"=> ""
				.GetMessage("main_cache_files_scanned_count", array("#value#" => "<b>".intval(Application::getInstance()->getSession()["CACHE_STAT"]["scanned"])."</b>"))."<br>"
				.GetMessage("main_cache_files_scanned_size", array("#value#" => "<b>".CFile::FormatSize(Application::getInstance()->getSession()["CACHE_STAT"]["space_total"])."</b>"))."<br>"
				.GetMessage("main_cache_files_deleted_count", array("#value#" => "<b>".intval(Application::getInstance()->getSession()["CACHE_STAT"]["deleted"])."</b>"))."<br>"
				.GetMessage("main_cache_files_deleted_size", array("#value#" => "<b>".CFile::FormatSize(Application::getInstance()->getSession()["CACHE_STAT"]["space_freed"])."</b>"))."<br>"
				.GetMessage("main_cache_files_delete_errors", array("#value#" => "<b>".intval(Application::getInstance()->getSession()["CACHE_STAT"]["errors"])."</b>"))."<br>"
				.GetMessage("main_cache_files_last_path", array("#value#" => "<b>".htmlspecialcharsbx($currentPath)."</b>"))."<br>"
			,
			"HTML"=>true,
			"TYPE"=>"OK",
		));
		?>
		<script>
			CloseWaitWindow();
			DoNext(<?echo CUtil::PhpToJSObject(mb_substr($file, mb_strlen($_SERVER["DOCUMENT_ROOT"])))?>);
		</script>
		<?
	}
	else
	{
		if (isset($_REQUEST["cachetype"]) && $_REQUEST["cachetype"] == "menu")
		{
			$GLOBALS["CACHE_MANAGER"]->CleanDir("menu");
			CBitrixComponent::clearComponentCache("bitrix:menu");
		}
		elseif (isset($_REQUEST["cachetype"]) && $_REQUEST["cachetype"] == "managed")
		{
			$GLOBALS["CACHE_MANAGER"]->CleanAll();
			$GLOBALS["stackCacheManager"]->CleanAll();
		}
		elseif (isset($_REQUEST["cachetype"]) && $_REQUEST["cachetype"] == "html")
		{
			$page = \Bitrix\Main\Composite\Page::getInstance();
			$page->deleteAll();
		}
		elseif (isset($_REQUEST["cachetype"]) && $_REQUEST["cachetype"] == "all")
		{
			BXClearCache(true);
			$GLOBALS["CACHE_MANAGER"]->CleanAll();
			$GLOBALS["stackCacheManager"]->CleanAll();
			$taggedCache = Application::getInstance()->getTaggedCache();
			$taggedCache->deleteAllTags();
			$page = \Bitrix\Main\Composite\Page::getInstance();
			$page->deleteAll();
		}

		if (Application::getInstance()->getSession()["CACHE_STAT"])
		{
			CAdminMessage::ShowMessage(array(
				"MESSAGE"=>GetMessage("main_cache_finished"),
				"DETAILS"=> ""
					.GetMessage("main_cache_files_scanned_count", array("#value#" => "<b>".intval(Application::getInstance()->getSession()["CACHE_STAT"]["scanned"])."</b>"))."<br>"
					.GetMessage("main_cache_files_scanned_size", array("#value#" => "<b>".CFile::FormatSize(Application::getInstance()->getSession()["CACHE_STAT"]["space_total"])."</b>"))."<br>"
					.GetMessage("main_cache_files_deleted_count", array("#value#" => "<b>".intval(Application::getInstance()->getSession()["CACHE_STAT"]["deleted"])."</b>"))."<br>"
					.GetMessage("main_cache_files_deleted_size", array("#value#" => "<b>".CFile::FormatSize(Application::getInstance()->getSession()["CACHE_STAT"]["space_freed"])."</b>"))."<br>"
					.GetMessage("main_cache_files_delete_errors", array("#value#" => "<b>".intval(Application::getInstance()->getSession()["CACHE_STAT"]["errors"])."</b>"))."<br>"
				,
				"HTML"=>true,
				"TYPE"=>"OK",
			));
		}
		else
		{
			CAdminMessage::ShowMessage(array(
				"MESSAGE"=>GetMessage("main_cache_finished"),
				"HTML"=>true,
				"TYPE"=>"OK",
			));
		}
		?>
		<script>
			CloseWaitWindow();
			EndClearCache();
		</script>
		<?
	}

	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
else
{

if(!$USER->CanDoOperation('cache_control') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$errorMessage = "";
$okMessage = "";

if ($REQUEST_METHOD=="POST" && ($cache_on=="Y" || $cache_on=="N") && check_bitrix_sessid() && $isAdmin)
{
	if(COption::GetOptionString("main", "component_cache_on", "Y")=="Y")
	{
		if ($cache_on=="N")
		{
			COption::SetOptionString("main", "component_cache_on", "N");
			$okMessage .= GetMessage("MAIN_OPTION_CACHE_SUCCESS").". ";
		}
	}
	else
	{
		if ($cache_on=="Y")
		{
			COption::SetOptionString("main", "component_cache_on", "Y");
			$okMessage .= GetMessage("MAIN_OPTION_CACHE_SUCCESS").". ";
		}
	}
}

if($REQUEST_METHOD=="POST" && ($managed_cache_on=="Y" || $managed_cache_on=="N") && check_bitrix_sessid() && $isAdmin)
{
	COption::SetOptionString("main", "component_managed_cache_on", $managed_cache_on);
	if($managed_cache_on == "N")
	{
		$taggedCache = Application::getInstance()->getTaggedCache();
		$taggedCache->clearByTag(true);
	}
	LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&tabControl_active_tab=fedit4&res=managed_saved");
}
if (isset($_REQUEST["res"]) && $_REQUEST["res"] == "managed_saved")
	$okMessage .= GetMessage("main_cache_managed_saved");

$APPLICATION->SetTitle(GetMessage("MCACHE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?
if($errorMessage <> '')
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$errorMessage, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SAE_ERROR"), "HTML"=>true));
if($okMessage <> '')
	echo CAdminMessage::ShowNote($okMessage);
?>

<script language="JavaScript">
var stop;
var last_path;
var cache_types_cnt = 0;

function StartClearCache()
{
	stop=false;
	document.getElementById('clear_result_div').innerHTML='';
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	for(var i=1;i<=cache_types_cnt;i++)
		document.getElementById('cachetype'+i).disabled=true;

	DoNext('');
}
function DoNext(path)
{
	var queryString = 'ajax=y'
		+ '&clearcache=Y'
		+ '&lang=<?echo htmlspecialcharsbx(LANG)?>'
		+ '&<?echo bitrix_sessid_get()?>'
	;

	var cachetype = '';
	for(var i=1;i<=cache_types_cnt;i++)
	{
		var radio = document.getElementById('cachetype'+i);
		if(radio.checked)
			cachetype = radio.value;
	}

	last_path = path;

	if(!stop)
	{
		ShowWaitWindow();
		BX.ajax.post(
			'cache.php?'+queryString,
			{'path': path, 'cachetype': cachetype},
			function(result){
				document.getElementById('clear_result_div').innerHTML = result;
				CloseWaitWindow();
			}
		);
	}

	return false;
}
function StopClearCache()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=false;
	for(var i=1;i<=cache_types_cnt;i++)
		document.getElementById('cachetype'+i).disabled=false;
}
function ContinueClearCache()
{
	stop=false;
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	for(var i=1;i<=cache_types_cnt;i++)
		document.getElementById('cachetype'+i).disabled=true;
	DoNext(last_path);
}
function EndClearCache()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=true;
	for(var i=1;i<=cache_types_cnt;i++)
		document.getElementById('cachetype'+i).disabled=false;
}
</script>

<div id="clear_result_div" style="margin:0px">
</div>

<?
$aTabs = array(
	array(
		"DIV" => "fedit1",
		"TAB" => GetMessage("MAIN_TAB_4"),
		"ICON" => "main_settings",
		"TITLE" => GetMessage("MAIN_OPTION_PUBL"),
	),
	array(
		"DIV" => "fedit4",
		"TAB" => GetMessage("main_cache_managed"),
		"ICON" => "main_settings",
		"TITLE" => GetMessage("main_cache_managed_sett"),
	),
);

$aTabs[] = array(
	"DIV" => "fedit2",
	"TAB" => GetMessage("MAIN_TAB_3"),
	"ICON" => "main_settings",
	"TITLE" => GetMessage("MAIN_OPTION_CLEAR_CACHE"),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>">
<?=bitrix_sessid_post()?>
<?$tabControl->BeginNextTab();?>
<tr>
	<td valign="top" colspan="2" align="left">
		<?if(COption::GetOptionString("main", "component_cache_on", "Y")=="Y"):?>
			<span style="color:green;"><b><?echo GetMessage("MAIN_OPTION_CACHE_ON")?>.</b></span>
		<?else:?>
			<span style="color:red;"><b><?echo GetMessage("MAIN_OPTION_CACHE_OFF")?>.</b></span>
		<?endif?>
		<br><br>
	</td>
</tr>
<tr>
	<td valign="top" colspan="2" align="left">
		<?if(COption::GetOptionString("main", "component_cache_on", "Y")=="Y"):?>
			<input type="hidden" name="cache_on" value="N">
			<input type="submit" name="cache_siteb" value="<?echo GetMessage("MAIN_OPTION_CACHE_BUTTON_OFF")?>"<?if(!$isAdmin) echo " disabled"?>>
		<?else:?>
			<input type="hidden" name="cache_on" value="Y">
			<input type="submit" name="cache_siteb" value="<?echo GetMessage("MAIN_OPTION_CACHE_BUTTON_ON")?>"<?if(!$isAdmin) echo " disabled"?> class="adm-btn-save">
		<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?><?echo GetMessage("cache_admin_note1")?>
		<?echo EndNote(); ?>
	</td>
</tr>
</form>

<?$tabControl->EndTab()?>
<?$tabControl->BeginNextTab()?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>&amp;tabControl_active_tab=fedit4">
<?=bitrix_sessid_post()?>

<?
	$component_managed_cache = COption::GetOptionString("main", "component_managed_cache_on", "Y");
?>
<tr>
	<td valign="top" colspan="2" align="left">
		<?if($component_managed_cache <> "N" || defined("BX_COMP_MANAGED_CACHE")):?>
			<span style="color:green;"><b><?echo GetMessage("main_cache_managed_on")?></b></span>
		<?else:?>
			<span style="color:red;"><b><?echo GetMessage("main_cache_managed_off")?></b></span>
		<?endif?>
		<br><br>
	</td>
</tr>
<tr>
	<td valign="top" colspan="2" align="left">
		<?if($component_managed_cache <> "N" || defined("BX_COMP_MANAGED_CACHE")):?>
			<input type="hidden" name="managed_cache_on" value="N">
			<input type="submit" name="" value="<?echo GetMessage("main_cache_managed_turn_off")?>"<?if(!$isAdmin || $component_managed_cache == "N") echo " disabled"?>>
			<?if($component_managed_cache == "N"):?><br><br><?echo GetMessage("main_cache_managed_const")?><?endif?>
		<?else:?>
			<input type="hidden" name="managed_cache_on" value="Y">
			<input type="submit" name="" value="<?echo GetMessage("main_cache_managed_turn_on")?>"<?if(!$isAdmin) echo " disabled"?> class="adm-btn-save">
		<?endif?>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?>
		<?echo GetMessage("main_cache_managed_note")?>
		<?echo EndNote(); ?>
	</td>
</tr>
</form>
<?$tabControl->EndTab()?>
<? $tabControl->BeginNextTab(); ?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANG?>">
<?=bitrix_sessid_post()?>
<tr>
	<td colspan="2" valign="top" align="left">
		<input type="hidden" name="clearcache" value="Y">
		<input type="radio" class="cache-types" name="cachetype" id="cachetype1" value="expired"<?if($cachetype!="all" && $cachetype!="menu" && $cachetype!="managed")echo " checked"?>> <label for="cachetype1"><?echo GetMessage("MAIN_OPTION_CLEAR_CACHE_OLD")?></label><br>
		<input type="radio" class="cache-types" name="cachetype" id="cachetype2" value="all"<?if($cachetype=="all")echo " checked"?>> <label for="cachetype2"><?echo GetMessage("MAIN_OPTION_CLEAR_CACHE_ALL")?></label><br>
		<input type="radio" class="cache-types" name="cachetype" id="cachetype3" value="menu"<?if($cachetype=="menu")echo " checked"?>> <label for="cachetype3"><?echo GetMessage("MAIN_OPTION_CLEAR_CACHE_MENU")?></label><br>
		<input type="radio" class="cache-types" name="cachetype" id="cachetype4" value="managed"<?if($cachetype=="managed")echo " checked"?>> <label for="cachetype4"><?echo GetMessage("MAIN_OPTION_CLEAR_CACHE_MANAGED")?></label><br>
		<input type="radio" class="cache-types" name="cachetype" id="cachetype5" value="html"<?if($cachetype=="html")echo " checked"?>> <label for="cachetype5"><?echo GetMessage("MAIN_OPTION_CLEAR_CACHE_STATIC")?></label><br>
		<?if (\Bitrix\Main\ModuleManager::isModuleInstalled("landing")):?>
		<input type="radio" class="cache-types" name="cachetype" id="cachetype6" value="landing"<?if($cachetype=="landing")echo " checked"?>> <label for="cachetype6"><?echo GetMessage("MAIN_OPTION_CLEAR_CACHE_LANDING")?></label><br>
		<?endif;?>
		<br>
		<script type="text/javascript">
			cache_types_cnt = document.getElementsByClassName('cache-types').length;
		</script>
	</td>
</tr>
<tr>
	<td valign="top" colspan="2" align="left">
		<input type="button" id="start_button" value="<?echo GetMessage("main_cache_files_start")?>" OnClick="StartClearCache();"<?if(!$isAdmin) echo " disabled"?> class="adm-btn-save">
		<input type="button" id="stop_button" value="<?echo GetMessage("main_cache_files_stop")?>" OnClick="StopClearCache();" disabled>
		<input type="button" id="continue_button" value="<?echo GetMessage("main_cache_files_continue")?>" OnClick="ContinueClearCache();" disabled>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?echo BeginNote();?>
		<?echo GetMessage("cache_admin_note2")?>
		<?echo EndNote(); ?>
	</td>
</tr>
</form>
<?$tabControl->End();?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>