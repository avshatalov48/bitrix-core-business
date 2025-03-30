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
 * @global CAdminPage $adminPage
 * @global CAdminMenu $adminMenu
 * @global CAdminMainChain $adminChain
 */

use Bitrix\Main\Type\Date;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Application;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetTitle() == '')
	$APPLICATION->SetTitle(GetMessage("MAIN_PROLOG_ADMIN_TITLE"));

$aUserOpt = CUserOptions::GetOption("admin_panel", "settings");
$aUserOptGlobal = CUserOptions::GetOption("global", "settings", []);

$isSidePanel = (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y");

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

$bShowAdminMenu = !empty($adminMenu->aGlobalMenu);

global $adminSidePanelHelper;
if (!is_object($adminSidePanelHelper))
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
	$adminSidePanelHelper = new CAdminSidePanelHelper();
}

$aOptMenuPos = array();
if($bShowAdminMenu && class_exists("CUserOptions"))
{
	$aOptMenuPos = CUserOptions::GetOption("admin_menu", "pos", array());
	$bOptMenuMinimized = isset($aOptMenuPos['ver']) && $aOptMenuPos['ver'] == 'off';
}

if (!defined('ADMIN_SECTION_LOAD_AUTH') || !ADMIN_SECTION_LOAD_AUTH):

	$direction = \Bitrix\Main\Context::getCurrent()->getCulture()->getDirection() ? '' : ' dir="rtl"';
?>
<!DOCTYPE html>
<html<?= isset($aUserOpt['fix']) && $aUserOpt['fix'] == 'on' ? ' class="adm-header-fixed"' : ''?><?= $direction ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialcharsbx(LANG_CHARSET)?>">
<meta name="viewport" content="initial-scale=1.0, width=device-width">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?$adminPage->ShowTitle()?> - <?= htmlspecialcharsbx(COption::GetOptionString("main","site_name", $_SERVER["SERVER_NAME"])) ?></title>
<?
else:
?>
<script>
<?
	if (isset($aUserOpt['fix']) && $aUserOpt['fix'] == 'on'):
?>
document.documentElement.className = 'adm-header-fixed';
<?
	endif;
?>
window.document.title = '<?$adminPage->ShowJsTitle()?> - <?echo CUtil::JSEscape(COption::GetOptionString("main","site_name", $_SERVER["SERVER_NAME"]));?>';
</script>
<?
endif;

$APPLICATION->AddBufferContent(array($adminPage, "ShowCSS"));
echo $adminPage->ShowScript();
$APPLICATION->ShowHeadStrings();
$APPLICATION->ShowHeadScripts();
?>
<script>
BX.message({MENU_ENABLE_TOOLTIP: <?=(!isset($aUserOptGlobal['start_menu_title']) || $aUserOptGlobal['start_menu_title'] <> 'N' ? 'true' : 'false')?>});
BX.InitializeAdmin();

var topWindow = BX.PageObject.getRootWindow();
if (
	BX.Reflection.getClass('topWindow.BX.adminSidePanel')
	&& (
		!topWindow.window["adminSidePanel"]
		|| !BX.is_subclass_of(topWindow.window["adminSidePanel"], topWindow.BX.adminSidePanel)
	)
)
{
	topWindow.window["adminSidePanel"] = new topWindow.BX.adminSidePanel();
}
</script>
<?
if (!defined('ADMIN_SECTION_LOAD_AUTH') || !ADMIN_SECTION_LOAD_AUTH):
?>
</head>
<body id="bx-admin-prefix">
<!--[if lte IE 7]>
<style type="text/css">
#bx-panel {display:none !important;}
.adm-main-wrap { display:none !important; }
</style>
<div id="bx-panel-error">
<?echo GetMessage("admin_panel_browser")?>
</div><![endif]-->
<?
endif;

if(($adminHeader = getLocalPath("php_interface/admin_header.php", BX_PERSONAL_ROOT)) !== false)
{
	include($_SERVER["DOCUMENT_ROOT"].$adminHeader);
}

?>
	<table class="adm-main-wrap">
		<?if (!$isSidePanel):?>
		<tr>
			<td class="adm-header-wrap" colspan="2">
<?
CAdminTopPanel::Show($adminPage, $adminMenu);
echo CAdminPage::ShowSound();
?>
			</td>
		</tr>
		<?endif;?>
		<tr>
<?

	CJSCore::Init(array('admin_interface'));
	$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');

	$aActiveSection = $adminMenu->ActiveSection();

	if(isset($GLOBALS["BX_FAVORITE_MENU_ACTIVE_ID"]) && $GLOBALS["BX_FAVORITE_MENU_ACTIVE_ID"])
		$openedSection ="desktop";
	else
		$openedSection = CUtil::JSEscape($aActiveSection["menu_id"]);

	$favOptions = CUserOptions::GetOption('favorite', 'favorite_menu', array("stick" => "N"));
	$stick = (array_key_exists("global_menu_desktop", $adminMenu->aActiveSections) || $openedSection =="desktop" ) ? "Y" : "N";
	if($stick <> $favOptions["stick"])
	{
		CUserOptions::SetOption('favorite', 'favorite_menu', array('stick' => $stick));
	}
?>
			<?if (!$isSidePanel):?>
			<td class="adm-left-side-wrap" id="menu_mirrors_cont">

<script>
BX.adminMenu.setMinimizedState(<?=$bOptMenuMinimized ? 'true' : 'false'?>);
BX.adminMenu.setActiveSection('<?=$openedSection?>');
BX.adminMenu.setOpenedSections('<?=CUtil::JSEscape($adminMenu->GetOpenedSections());?>');
</script>
				<div class="adm-left-side<?=$bOptMenuMinimized ? ' adm-left-side-wrap-close' : ''?>"<?if(isset($aOptMenuPos["width"]) && intval($aOptMenuPos["width"]) > 0) echo ' style="width:'.($bOptMenuMinimized ? 15 : intval($aOptMenuPos["width"])).'px" data-width="'.intval($aOptMenuPos["width"]).'"'?> id="bx_menu_panel"><div class="adm-menu-wrapper<?=$bOptMenuMinimized ? ' adm-main-menu-close' : ''?>" style="overflow:hidden; min-width:300px;">
						<div class="adm-main-menu">
<?
	$menuScripts = "";

	foreach($adminMenu->aGlobalMenu as $menu):

		$menuClass = "adm-main-menu-item adm-".$menu["menu_id"];

		if(($menu["items_id"] == $aActiveSection["items_id"] && $openedSection !="desktop" )|| $menu["menu_id"] == $openedSection)
			$menuClass .=' adm-main-menu-item-active';

		if (isset($menu['url']) && $menu['url']):
?>
						<a href="<?=htmlspecialcharsbx($menu["url"])?>" class="adm-default <?=$menuClass?>" onclick="BX.adminMenu.GlobalMenuClick('<?echo $menu["menu_id"]?>'); return false;" onfocus="this.blur();" id="global_menu_<?echo $menu["menu_id"]?>">
							<div class="adm-main-menu-item-icon"></div>
							<div class="adm-main-menu-item-text"><?echo htmlspecialcharsbx($menu["text"])?></div>
							<div class="adm-main-menu-hover"></div>
						</a>
<?
		else:
?>
						<span class="adm-default <?=$menuClass?>" onclick="BX.adminMenu.GlobalMenuClick('<?echo $menu["menu_id"]?>'); return false;" id="global_menu_<?echo $menu["menu_id"]?>">
							<div class="adm-main-menu-item-icon"></div>
							<div class="adm-main-menu-item-text"><?echo htmlspecialcharsbx($menu["text"])?></div>
							<div class="adm-main-menu-hover"></div>
						</span>
<?
		endif;
	endforeach;
?>
					</div>
					<div class="adm-submenu" id="menucontainer">
<?
		foreach($adminMenu->aGlobalMenu as $menu):

			if(
				(
					(
						$menu["menu_id"] == $aActiveSection["menu_id"]
						|| $menu["items_id"] == $aActiveSection["items_id"]

					)
					&& $openedSection !="desktop"
				)
				|| $menu["menu_id"] == $openedSection

			)
				$subMenuDisplay = "block";
			else
				$subMenuDisplay = "none";

?>
						<div class="adm-global-submenu<?=($subMenuDisplay == "block" ? " adm-global-submenu-active" : "")?>" id="global_submenu_<?echo $menu["menu_id"]?>">
<?
		if ($menu['menu_id'] == 'desktop')
		{
			if ($USER->IsAuthorized())
			{
				CDesktopMenu::Show();
			}
?>
<script>
	BX.addCustomEvent(BX.adminMenu, 'onMenuChange', BX.delegate(BX.adminFav.onMenuChange, this));
</script>
<?
			$favMenu = new CBXFavAdmMenu;
			$menu["text"] = GetMessage("MAIN_PR_ADMIN_FAV");
			$menu["items"] = $favMenu->GenerateItems();
		}
?>
							<div class="adm-submenu-items-wrap">
								<div class="adm-submenu-items-stretch-wrap" onscroll="BX.adminMenu.itemsStretchScroll()">
									<table class="adm-submenu-items-stretch">
										<tr>
											<td class="adm-submenu-items-stretch-cell">
												<div class="adm-submenu-items-block">
													<div class="adm-submenu-items-title adm-submenu-title-<?=$menu['menu_id']?>"><?=htmlspecialcharsbx($menu["text"])?></div>
													<div id='<?="_".$menu['items_id']?>'>
<?
		if(!empty($menu["items"]))
		{
			foreach($menu["items"] as $submenu)
			{
				$menuScripts .= $adminMenu->Show($submenu);
			}
		}
		elseif ($menu['menu_id'] == 'desktop')
		{
			echo CBXFavAdmMenu::GetEmptyMenuHTML();
		}

		if($menu['menu_id'] == 'desktop')
		{
			echo CBXFavAdmMenu::GetMenuHintHTML(empty($menu["items"]));
		}

?>
													</div>
												</div>
											</td>
										</tr>
									</table>
								</div>
							</div>
						</div>
<?
	endforeach;
?>
						<div class="adm-submenu-separator"></div>
<?
	if ($menuScripts != ""):
?>
<script><?=$menuScripts?></script>
<?
	endif;

	if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/this_site_logo.php"))
	{
		include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/this_site_logo.php");
	}
?>
					</div>
				</div></div>
			</td>
			<?endif;?>
			<td class="adm-workarea-wrap <?=defined('BX_ADMIN_SECTION_404') && BX_ADMIN_SECTION_404 == 'Y' ? 'adm-404-error' : 'adm-workarea-wrap-top'?>">
				<div class="adm-workarea adm-workarea-page" id="adm-workarea">
<?
//Title
$curPage = $APPLICATION->GetCurPage(true);
if ($curPage != "/bitrix/admin/index.php")
{
	$currentFavId = null;

	if (!defined('BX_ADMIN_SECTION_404') || BX_ADMIN_SECTION_404 != 'Y')
	{
		if ($isSidePanel)
		{
			$requestUri = (new Uri($_SERVER["REQUEST_URI"]))
				->deleteParams(["IFRAME", "IFRAME_TYPE"])
				->getUri()
			;
			$currentFavId = CFavorites::getIDByUrl($requestUri);
		}
		else
		{
			$arLastItem = null;
			//Navigation chain
			$adminChain->Init();
			$arLastItem = $adminChain->Show();

			$currentFavId = CFavorites::GetIDByUrl($_SERVER["REQUEST_URI"]);
		}
	}
}

foreach (GetModuleEvents("main", "OnPrologAdminTitle", true) as $arEvent)
{
	$arPageParams = array();
	$arPageParams[] = $curPage;
	if (isset($_GET["pageid"]))
		$arPageParams[] = $_GET["pageid"];

	ExecuteModuleEventEx($arEvent, $arPageParams);
}

if ($curPage != "/bitrix/admin/index.php" && !$adminPage->isHideTitle())
{
	$isFavLink = !defined('BX_ADMIN_SECTION_404') || BX_ADMIN_SECTION_404 != 'Y';
	if ($adminSidePanelHelper->isPublicSidePanel())
	{
		$isFavLink = false;
	}
	?>
		<h1 class="adm-title" id="adm-title">
			<?$adminPage->ShowTitle()?>
			<?if($isFavLink):?>
			<a href="javascript:void(0)" class="adm-fav-link<?=$currentFavId>0?' adm-fav-link-active':''?>" onclick="
				BX.adminFav.titleLinkClick(this, <?=intval($currentFavId)?>, '')" title="
				<?= $currentFavId ? GetMessage("MAIN_PR_ADMIN_FAV_DEL") : GetMessage("MAIN_PR_ADMIN_FAV_ADD")?>"></a>
			<?endif;?>
			<a id="navchain-link" href="<?echo htmlspecialcharsbx($_SERVER["REQUEST_URI"])?>" title="
			<?echo GetMessage("MAIN_PR_ADMIN_CUR_LINK")?>"></a>
		</h1>
	<?
}

//Content

if($USER->IsAuthorized()):
	$license = Application::getInstance()->getLicense();
	$eulaLink = $license->getEulaLink();
	$textMessage = '';
	$showProlongMenu = false;

	if(defined("DEMO") && DEMO == "Y"):

		$vendor = COption::GetOptionString("main", "vendor", "1c_bitrix");

		$delta = $license->getExpireDate()?->getTimestamp() - time();
		$daysToExpire = ($delta < 0? 0 : ceil($delta/86400));

		echo BeginNote('style="position: relative; top: -15px;"');
?>
	<span class="required"><?echo GetMessage("TRIAL_ATTENTION") ?></span>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT1_".$vendor) ?>
	<?if ($daysToExpire >= 0):?>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT2") ?> <span class="required"><b><?echo $daysToExpire?></b></span> <?echo GetMessage("TRIAL_ATTENTION_TEXT3") ?>.
	<?else:?>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT4_".$vendor) ?>
	<?endif;?>
	<?echo GetMessage("TRIAL_ATTENTION_TEXT5_".$vendor) ?>
<?
		echo EndNote();

	elseif(defined("TIMELIMIT_EDITION") && TIMELIMIT_EDITION == "Y"):

		$expireDate = $license->getExpireDate();
		$delta = $expireDate?->getTimestamp() - time();
		$daysToExpire = $delta / 86400;

		if ($daysToExpire >= 0 && $daysToExpire < 60)
		{
			$textMessage = GetMessage('prolog_main_timelimit_almost_expire', [
				'#FINISH_DATE#' => $expireDate,
				'#LINK#' => $eulaLink,
			]);
			$showProlongMenu = true;
		}
		elseif ($daysToExpire < 0)
		{
			$blockDate = ($expireDate ? (clone $expireDate)->add('+15 days') : new Date());
			$textMessage = GetMessage('prolog_main_timelimit_expired', [
				'#FINISH_DATE#' => ($expireDate ?: $blockDate),
				'#BLOCK_DATE#' => $blockDate,
				'#LINK#' => $eulaLink,
			]);
		};

	elseif($USER->CanDoOperation('install_updates')):
		//show support ending warning

		$supportFinishDate = $license->getSupportExpireDate();
		if ($supportFinishDate !== null)
		{
			$aGlobalOpt = CUserOptions::GetOption("global", "settings", array());
			if(!isset($aGlobalOpt['messages']['support']) || $aGlobalOpt['messages']['support'] <> 'N')
			{
				$supportFinishStamp = $supportFinishDate->getTimestamp();
				$supportDateDiff = ceil(($supportFinishStamp - time())/86400);

				if($supportDateDiff >= 0 && $supportDateDiff <= 30)
				{
					$textMessage = GetMessage(
						'prolog_main_support_almost_expire',
						['#FINISH_DATE#' => GetTime($supportFinishStamp)]
					);
					$showProlongMenu = true;
				}
				elseif($supportDateDiff < 0)
				{
					$textMessage = GetMessage(
						'prolog_main_support_expired',
						['#FINISH_DATE#' => GetTime($supportFinishStamp)]
					);
				}
			}
		}
	endif;

	if ($textMessage !== '')
	{
		$userOption = CUserOptions::GetOption("main", "admSupInf", []);

		if (!isset($userOption["showInformerDate"]) || time() > $userOption["showInformerDate"])
		{
			if (LANGUAGE_ID == "ru" && $license->getPartnerId() > 0)
			{
				$prolongUrl = "/bitrix/admin/buy_support.php?lang=" . LANGUAGE_ID;
			}
			else
			{
				$prolongUrl = $license->getBuyLink();
			}

			echo BeginNote('style="position: relative; top: -15px;"');
			?>
			<style>
				#menu-popup-prolong-popup .popup-window-hr { display:none;}
				#menu-popup-prolong-popup .menu-popup .menu-popup-item {min-width: 100px; margin-top: 7px;}
				#menu-popup-prolong-popup .menu-popup-item:hover {background-color: #fff !important;}
			</style>
			<script>
				function showProlongMenu(bindElement)
				{
					BX.PopupMenu.show("prolong-popup", bindElement, [
							{
								html : '<b><?=GetMessageJS("prolog_main_support_menu1")?></b>'
							},
							{
								html : '<?=GetMessageJS("prolog_main_support_menu2")?>',
								onclick : function() {prolongRemind('<?=AddToTimeStamp(array("DD" => 7));?>', this)}
							},
							{
								html : '<?=GetMessageJS("prolog_main_support_menu3")?>',
								onclick : function() {prolongRemind('<?=AddToTimeStamp(array("DD" => 14));?>', this)}
							},
							{
								html : '<?=GetMessageJS("prolog_main_support_menu4")?>',
								onclick : function() {prolongRemind('<?=AddToTimeStamp(array("MM" => 1));?>', this)}
							}
						],
						{
							offsetTop : 5,
							offsetLeft : 13,
							angle : true
						});

					return false;
				}

				function prolongRemind(tt, el)
				{
					BX.userOptions.save('main', 'admSupInf', 'showInformerDate', tt);
					el.popupWindow.close();
					BX.hide(BX('prolongmenu').parentNode);
				}
			</script>
			<div style="float: right; padding-left: 50px; margin-top: -5px; text-align: center;">
				<a href="<?=$prolongUrl?>" target="_blank" class="adm-btn adm-btn-save" style="margin-bottom: 4px;"><?=GetMessage("prolog_main_support_button_prolong")?></a><br />
				<?php if ($showProlongMenu): ?>
					<a href="javascript:void(0)" id="prolongmenu" onclick="showProlongMenu(this)" style="color: #716536;"><?=GetMessage("prolog_main_support_button_no_prolong2")?></a>
				<?php endif; ?>
			</div>
			<?= $textMessage ?>
			<div id="supdescr" style="display: none;"><br /><br /><b><?=GetMessage("prolog_main_support_wit_descr1")?></b><hr><?=GetMessage("prolog_main_support_wit_description" . (IsModuleInstalled("intranet") ? "_cp" : "_bus") . '_MSGVER_1', ['#LINK#' => $eulaLink])?></div>
			<?php
			echo EndNote();
		}
	}
endif;
