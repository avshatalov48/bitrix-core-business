<?php
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

use Bitrix\Main\Web\Uri;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

IncludeModuleLangFile(__FILE__);

if(isset($_GET["back_url_pub"]) && !is_array($_GET["back_url_pub"]) && mb_strpos($_GET["back_url_pub"], "/") === 0)
	\Bitrix\Main\Application::getInstance()->getSession()["BACK_URL_PUB"] = $_GET["back_url_pub"];

$params = DeleteParam(array("logout", "back_url_pub", "sessid"));

$arPanelButtons = array();

function _showTopPanelButtonsSection($arPanelButtons, $hkInstance, $section = null)
{
	global $USER;

	foreach ($arPanelButtons as $item):
		if (isset($item["SEPARATOR"]) && $item["SEPARATOR"])
			continue;
		if ($section == null && isset($item['SECTION']))
			continue;
		if ($section != null && (!isset($item['SECTION']) || $item['SECTION'] != $section))
			continue;

		$id = $item['ID'] ?? 'bx_top_panel_button_'.RandString();
		$bHasMenu = (isset($item["MENU"]) && is_array($item["MENU"]) && !empty($item["MENU"]));

		if($USER->IsAuthorized())
			echo $hkInstance->PrintTPButton($item);

		if ($item['LINK']):

?><a id="<?=htmlspecialcharsEx($id)?>" href="<?=htmlspecialcharsEx($item['LINK'])?>" class="<?=$item['ICON']?>"<?=isset($item["TITLE"])?' title="'.htmlspecialcharsEx($item["TITLE"]).'"':''?><?=isset($item["TARGET"])?' target="'.htmlspecialcharsEx($item["TARGET"]).'"':''?> hidefocus="true" onfocus="this.blur();"><?=htmlspecialcharsbx($item["TEXT"])?></a><?

		else:

?><span id="<?=htmlspecialcharsEx($id)?>" class="<?=$item['ICON']?>"<?=isset($item["TITLE"])?'title="'.htmlspecialcharsEx($item["TITLE"]).'"':''?>><?=htmlspecialcharsbx($item["TEXT"])?></span><?

		endif;

		if ($bHasMenu || (isset($item['TOOLTIP']) && $item['TOOLTIP'] && $item['TOOLTIP_ID'])):
?><script type="text/javascript"><?

			if (isset($item['TOOLTIP']) && $item['TOOLTIP']):
				if (isset($item['TOOLTIP_ID']) && $item['TOOLTIP_ID']):

?>
BX.ready(function() {BX.hint(BX('<?=CUtil::JSEscape($id)?>'), '<?=CUtil::JSEscape($item["TITLE"])?>', '<?=CUtil::JSEscape($item['TOOLTIP'])?>', '<?=CUtil::JSEscape($item['TOOLTIP_ID'])?>')});
<?

				endif;
			endif;
			if ($bHasMenu):

?>
BX.adminPanel.registerButton('<?=CUtil::JSEscape($id)?>', {MENU: <?=CUtil::PhpToJsObject($item['MENU'])?>});
<?

			endif;

?></script><?

		endif;
	endforeach;
}

if($USER->IsAuthorized())
{
	$bCanViewSettings = (is_callable(array($USER,'CanDoOperation')) && ($USER->CanDoOperation('view_other_settings') || $USER->CanDoOperation('edit_other_settings')));
	if($bCanViewSettings)
	{
		//Settings
		$settingsUrl = BX_ROOT."/admin/settings.php?lang=".LANG."&mid=".(defined("ADMIN_MODULE_NAME")? ADMIN_MODULE_NAME:"main").($APPLICATION->GetCurPage() <> BX_ROOT."/admin/settings.php"? "&back_url_settings=".urlencode($_SERVER["REQUEST_URI"]):"");
		$arPanelButtons[] = array(
			"TEXT"=>GetMessage("top_panel_settings"),
			"TITLE"=>GetMessage("button_settings"),
			"LINK"=>$settingsUrl,
			"ICON"=>"adm-header-setting-btn",
			"HK_ID"=>"top_panel_settings",
		);
	}

	//Help
	$module = (defined("ADMIN_MODULE_NAME")? ADMIN_MODULE_NAME: "main");
	$page = (defined("HELP_FILE") && strpos(HELP_FILE, '/') === false? HELP_FILE : basename($APPLICATION->GetCurPage()));

	$aActiveSection = $adminMenu->ActiveSection();
	$section = $aActiveSection["help_section"]."/";
	if (defined("HELP_FILE") && mb_strpos(HELP_FILE, $section) === 0)
		$section = "";
}


/*
 * @global \CAdminPage $adminPage
 */

$arLangs = CLanguage::GetLangSwitcherArray();

$arLangButton = array();
$arLangMenu = array();

foreach($arLangs as $adminLang)
{
	if ($adminLang['SELECTED'])
	{
		$arLangButton = array(
			"TEXT"=>strtoupper($adminLang["LID"]),
			"TITLE"=>GetMessage("top_panel_lang")." ".$adminLang["NAME"],
			"LINK"=>htmlspecialcharsback($adminLang["PATH"]),
			"SECTION" => 1,
			"ICON" => "adm-header-language",
		);
	}

	$arLangMenu[] = array(
		"TEXT" => '('.$adminLang["LID"].') '.$adminLang["NAME"],
		"TITLE"=> GetMessage("top_panel_lang")." ".$adminLang["NAME"],
		"LINK"=>htmlspecialcharsback($adminLang["PATH"]),
	);
}

if (count($arLangMenu) > 1)
{
	CJSCore::Init(array('admin_interface'));
	$arLangButton['MENU'] = $arLangMenu;
}

$arPanelButtons[] = $arLangButton;

$sPubUrl = (\Bitrix\Main\Application::getInstance()->getSession()["BACK_URL_PUB"] <> ""?
	htmlspecialcharsbx(\Bitrix\Main\Application::getInstance()->getSession()["BACK_URL_PUB"]).(strpos(\Bitrix\Main\Application::getInstance()->getSession()["BACK_URL_PUB"], "?") !== false? "&amp;":"?") : '/?').
	'back_url_admin='.urlencode($APPLICATION->GetCurPage().($params<>""? "?".$params:""));

$aUserOptGlobal = CUserOptions::GetOption("global", "settings");

if($USER->IsAuthorized())
{
	$hkInstance = CHotKeys::getInstance();
	$Execs=$hkInstance->GetCodeByClassName("top_panel_menu",GetMessage("admin_panel_menu"));
	echo $hkInstance->PrintJSExecs($Execs);
	$Execs=$hkInstance->GetCodeByClassName("admin_panel_site",GetMessage("admin_panel_site"));
	echo $hkInstance->PrintJSExecs($Execs);
	$Execs=$hkInstance->GetCodeByClassName("admin_panel_admin",GetMessage("admin_panel_admin"));
	echo $hkInstance->PrintJSExecs($Execs);
}
?>
<div id="bx-panel" class="adm-header"><div class="adm-header-left">
	<div class="adm-header-btn-wrap">
		<?php
		$adminPanelSiteList = [];

		if (\Bitrix\Main\Config\Option::get("sale", "~IS_SALE_CRM_SITE_MASTER_FINISH") === "Y"
			|| \Bitrix\Main\Config\Option::get('sale', '~IS_SALE_BSM_SITE_MASTER_FINISH') === 'Y'
		)
		{
			$adminPanelSiteIdList = [];
			$adminPanelSiteList = [];
			$isAdminPanelDefaultSiteExists = false;

			$adminPanelSiteIterator = \Bitrix\Main\SiteTable::getList([
				'select' => ['LID', 'NAME', 'DEF', 'SITE_NAME', 'SERVER_NAME', 'SORT'],
				'filter' => [
					'=ACTIVE' => 'Y',
				],
				'cache' => ['ttl' => 86400],
			]);
			while ($adminPanelSiteData = $adminPanelSiteIterator->fetch())
			{
				$adminPanelSiteIdList[] = $adminPanelSiteData['LID'];

				if (empty($adminPanelSiteData['SERVER_NAME']))
				{
					continue;
				}

				$adminPanelSiteList[] = [
					'ID' => $adminPanelSiteData['LID'],
					'NAME' => $adminPanelSiteData['SITE_NAME'] ?: $adminPanelSiteData['NAME'],
					'SERVER_NAME' => $adminPanelSiteData['SERVER_NAME'],
					'DEF' => $adminPanelSiteData['DEF'],
					'SORT' => $adminPanelSiteData['SORT'],
				];

				if ($adminPanelSiteData['DEF'] === 'Y')
				{
					$isAdminPanelDefaultSiteExists = true;
				}
			}
			unset($adminPanelSiteData, $adminPanelSiteIterator);

			if ($adminPanelSiteIdList)
			{
				$adminPanelSiteDomainIterator = \Bitrix\Main\SiteDomainTable::getList([
					'select' => [
						'LID',
						'DOMAIN',
						'NAME' => 'SITE.NAME',
						'SITE_NAME' => 'SITE.SITE_NAME',
						'DEF' => 'SITE.DEF',
						'SORT' => 'SITE.SORT',
					],
					'filter' => [
						'=LID' => $adminPanelSiteIdList,
					],
					'cache' => ['ttl' => 86400],
				]);
				while ($adminPanelSiteDomainData = $adminPanelSiteDomainIterator->fetch())
				{
					$isAdminDomainExists = (bool)array_filter($adminPanelSiteList, static function ($site) use ($adminPanelSiteDomainData) {
						return $site['SERVER_NAME'] === $adminPanelSiteDomainData['DOMAIN'];
					});

					if (!$isAdminDomainExists)
					{
						$adminPanelSiteList[] = [
							'ID' => $adminPanelSiteDomainData['LID'],
							'NAME' => $adminPanelSiteDomainData['SITE_NAME'] ?: $adminPanelSiteDomainData['NAME'],
							'SERVER_NAME' => $adminPanelSiteDomainData['DOMAIN'],
							'DEF' => $adminPanelSiteDomainData['DEF'],
							'SORT' => $adminPanelSiteDomainData['SORT'],
						];

						if ($adminPanelSiteDomainData['DEF'] === 'Y')
						{
							$isAdminPanelDefaultSiteExists = true;
						}
					}
				}
				unset($adminPanelSiteDomainData, $adminPanelSiteDomainIterator, $isAdminDomainExists);
			}
			unset($adminPanelSiteIdList);

			if (!$isAdminPanelDefaultSiteExists)
			{
				$adminPanelDefaultServerName = \Bitrix\Main\Config\Option::get("main", "server_name");
				$isAdminPanelDefaultSiteExists = (bool)array_filter($adminPanelSiteList, static function ($adminPanelSite) use ($adminPanelDefaultServerName) {
					return $adminPanelSite['SERVER_NAME'] === $adminPanelDefaultServerName;
				});
				if (!$isAdminPanelDefaultSiteExists)
				{
					array_unshift(
						$adminPanelSiteList,
						[
							'NAME' => \Bitrix\Main\Config\Option::get("main", "site_name"),
							'SERVER_NAME' => $adminPanelDefaultServerName,
							'DEF' => 'Y',
							'SORT' => 1,
						]
					);
				}
				unset($adminPanelDefaultServerName);
			}
			unset($isAdminPanelDefaultSiteExists);

			\Bitrix\Main\Type\Collection::sortByColumn($adminPanelSiteList, ['SORT' => SORT_ASC]);

			if (count($adminPanelSiteList) > 1)
			{
				$adminPanelSiteMenu = [];
				$adminPanelDefaultButtonTitle = '';
				$adminPanelDefaultButtonLink = '';
				$adminPanelProtocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https://" : "http://";
				foreach ($adminPanelSiteList as $adminPanelSite)
				{
					$adminPanelSiteId = $adminPanelSite['ID'] ?? null;
					$adminPanelMenuItemTitle =
						$adminPanelSite['NAME']
						. ' (' . $adminPanelSite['SERVER_NAME'] . ')'
						. ($adminPanelSiteId ? ' [' . $adminPanelSiteId . ']' : '')
					;
					$adminPanelMenuItemLink = $adminPanelProtocol.$adminPanelSite['SERVER_NAME'];

					$adminPanelSiteMenu[] = [
						'TEXT' => $adminPanelMenuItemTitle,
						'LINK' => $adminPanelMenuItemLink,
					];

					if ($adminPanelSite['DEF'] === 'Y')
					{
						$adminPanelDefaultButtonTitle = $adminPanelMenuItemTitle;
						$adminPanelDefaultButtonLink = $adminPanelMenuItemLink;
					}
				}
				unset($adminPanelSite, $adminPanelSiteId, $adminPanelMenuItemTitle, $adminPanelMenuItemLink);

				if (!$adminPanelDefaultButtonTitle || !$adminPanelDefaultButtonLink)
				{
					$adminPanelDefaultButtonTitle = current($adminPanelSiteMenu)['TEXT'];
					$adminPanelDefaultButtonLink = current($adminPanelSiteMenu)['LINK'];
				}

				$adminPanelDefaultButtonTitle = htmlspecialcharsbx($adminPanelDefaultButtonTitle);
				$adminPanelDefaultButtonLink = htmlspecialcharsbx($adminPanelDefaultButtonLink);

				if (mb_strlen($adminPanelDefaultButtonTitle) > 30)
				{
					$adminPanelDefaultButtonTitle = mb_substr($adminPanelDefaultButtonTitle, 0, 30) . '...';
				}
				?>
				<a id="bx-panel-view-site-btn" class="adm-header-notif-block" href="<?= $adminPanelDefaultButtonLink ?>" title="<?= $adminPanelDefaultButtonTitle ?>" hidefocus="true" onfocus="this.blur();">
					<strong>
						<span id="bx-panel-view-tab-select" class="adm-header-notif-counter"><?= $adminPanelDefaultButtonTitle ?></span>
					</strong>
				</a>
				<script type="text/javascript">
						BX.adminPanel.registerButton(
							"bx-panel-view-site-btn",
							{
								MENU: <?=CUtil::PhpToJsObject($adminPanelSiteMenu)?>,
							}
						)
				</script>
				<?php
				unset(
					$adminPanelSiteMenu,
					$adminPanelDefaultButtonTitle,
					$adminPanelDefaultButtonLink,
					$adminPanelProtocol
				);
			}
			else
			{
				?>
				<a hidefocus="true" href="<?= $sPubUrl ?>" id="bx-panel-view-tab" class="adm-header-btn adm-header-btn-site" title="<?=GetMessage("adm_top_panel_view_title")?>"><?=GetMessage("admin_panel_site")?></a>
				<?php
			}
		}
		else
		{
			?>
			<a hidefocus="true" href="<?= $sPubUrl ?>" id="bx-panel-view-tab" class="adm-header-btn adm-header-btn-site" title="<?=GetMessage("adm_top_panel_view_title")?>"><?=GetMessage("admin_panel_site")?></a>
			<?php
		}

		if (count($adminPanelSiteList) > 1)
		{
			?>
			<a hidefocus="true" href="<?=BX_ROOT."/admin/index.php?lang=".LANGUAGE_ID?>" class="adm-header-notif-block"><span class="adm-header-notif-counter"><?=GetMessage("admin_panel_admin")?></span></a>
			<?php
		}
		else
		{
			?>
			<a hidefocus="true" href="<?=BX_ROOT."/admin/index.php?lang=".LANGUAGE_ID?>" class="adm-header-btn adm-header-btn-admin"><?=GetMessage("admin_panel_admin")?></a>
			<?php
		}

		unset($adminPanelSiteList);
		?>
	</div>
<?php
$informerItemsCount = CAdminInformer::InsertMainItems();

if ($USER->IsAuthorized() && $informerItemsCount>0):

?><span class="adm-header-notif-block" id="adm-header-notif-block" onclick="BX.adminInformer.Toggle(this);" title="<?=GetMessage("admin_panel_notif_block_title")?>"><span class="adm-header-notif-icon"></span><span class="adm-header-notif-counter" id="adm-header-notif-counter"><?=CAdminInformer::$alertCounter?></span></span><?
endif;

_showTopPanelButtonsSection($arPanelButtons, $hkInstance)

?></div><div class="adm-header-right"><?
if($USER->IsAuthorized() && IsModuleInstalled("search")):

?><div class="adm-header-search-block" id="bx-search-box"><input class="adm-header-search" id="bx-search-input" onfocus="if (this.value=='<?=GetMessage("top_panel_search_def")?>') {this.value=''; BX.addClass(this.parentNode,'adm-header-search-block-active');}" value="<?=GetMessage("top_panel_search_def")?>" onblur="if (this.value==''){this.value='<?=GetMessage("top_panel_search_def")?>'; BX.removeClass(this.parentNode,'adm-header-search-block-active');}" type="text" autocomplete="off" /><a href="#" onclick="BX('bx-search-input').value=''; BX('bx-search-input').onblur();" class="adm-header-search-block-btn"></a></div><script type="text/javascript">
var jsControl = new JCAdminTitleSearch({
	'AJAX_PAGE' : '/bitrix/admin/get_search.php?lang=<?=LANGUAGE_ID?>',
	'CONTAINER_ID': 'bx-search-box',
	'INPUT_ID': 'bx-search-input',
	'MIN_QUERY_LEN': 1
});
</script><?

	$Execs = $hkInstance->GetCodeByClassName("bx-search-input", GetMessage("top_panel_search_def"));
	echo $hkInstance->PrintJSExecs($Execs);

endif;
?><div class="adm-header-right-block"><?

if ($USER->IsAuthorized()):

/*
 * @global \CAdminPage $adminPage
 */

	$ssoSwitcher = $adminPage->getSSOSwitcherButton();
	$bShowSSO = is_array($ssoSwitcher) && !empty($ssoSwitcher);

	$userName = $USER->GetFormattedName();
	if($bShowSSO)
	{
		$userName = '<span class="adm-header-separate-left">'.$userName.'</span><span class="adm-header-separate-right" id="bx-panel-sso"></span>';
	}

	if ($USER->CanDoOperation('view_own_profile') || $USER->CanDoOperation('edit_own_profile')):

?><a hidefocus="true" href="/bitrix/admin/user_edit.php?lang=<?=LANGUAGE_ID?>&amp;ID=<?=$USER->GetID()?>" class="adm-header-user-block<?=$bShowSSO ? ' adm-header-separate' : ''?>" onfocus="this.blur()"><?=$userName;?></a><?

	else:

?><span class="adm-header-user-block<?=$bShowSSO ? ' adm-header-separate' : ''?>" id="bx-panel-user"><?=$userName?></span><?

	endif;

	if($bShowSSO)
	{
?>
<script>BX.adminPanel.registerButton('bx-panel-sso', {MENU: <?=CUtil::PhpToJsObject($ssoSwitcher)?>});</script>
<?
	}

?><a hidefocus="true" href="<?=htmlspecialcharsbx((defined('BX_ADMIN_SECTION_404') && BX_ADMIN_SECTION_404 == 'Y' ? '/bitrix/admin/' : $APPLICATION->GetCurPage()).'?'.CUser::getLogoutParams())?>" class="adm-header-exit" id="bx-panel-logout" title="<?=GetMessage('admin_panel_logout_title')?>"><?=GetMessage("admin_panel_logout")?></a><?

	$Execs = $hkInstance->GetCodeByClassName("bx-panel-logout",GetMessage('admin_panel_logout'));
	echo $hkInstance->PrintJSExecs($Execs);

endif;


_showTopPanelButtonsSection($arPanelButtons, $hkInstance, 1);

if ($USER->IsAuthorized()):
	if($hkInstance->IsActive()):

?><a hidefocus="true" id="bx-panel-hotkeys" href="javascript:void(0)" onclick="BXHotKeys.ShowSettings();" class="header-keyboard" title="<?=GetMessage('admin_panel_hotkeys_title')?>"></a><?

	endif;

	$aUserOpt = CUserOptions::GetOption("admin_panel", "settings");

?><a hidefocus="true" href="javascript:void(0)" id="bx-panel-pin" class="adm-header-pin" onclick="BX.adminPanel.Fix(this)" title="<?=GetMessage('top_panel_pin_'.(isset($aUserOpt['fix']) && $aUserOpt['fix'] == 'on' ? 'off' : 'on'))?>"></a><?

	if(LANGUAGE_ID == "ru")
	{
		CJSCore::Init(array('helper'));
		$helpUrl = (new Uri('https://helpdesk.bitrix24.ru/widget2/dev/'))->addParams([
			"url" => "https://".$_SERVER["HTTP_HOST"].$APPLICATION->GetCurPageParam(),
			"user_id" => $USER->GetID(),
			"is_admin" => $USER->IsAdmin() ? 1 : 0,
			"help_url" => "http://dev.1c-bitrix.ru/user_help/".$section.(defined("HELP_FILE") && strpos(HELP_FILE, '/') !== false?  HELP_FILE : $module."/".$page),
		]);

		$frameOpenUrl = (clone $helpUrl)->addParams([
			"action" => "open",
		])->getUri();

		$frameCloseUrl = (clone $helpUrl)->addParams([
			"action" => "close",
		])->getUri();
		?>
		<span class="adm-header-help-btn" id="bx_top_panel_button_helper" <?if (!isset($helperHeroOption["show"])):?>onclick="BX.userOptions.save('main', 'helper_hero_admin',  'show', 'Y');"<?endif?>>
		   <span class="adm-header-help-btn-icon"></span>
		   <span class="adm-header-help-btn-text"><?=GetMessage("top_panel_help")?></span>
		</span>
		<script>
			BX.Helper.init({
				frameOpenUrl : '<?=$frameOpenUrl?>',
				helpBtn : BX('bx_top_panel_button_helper'),
				langId: '<?=LANGUAGE_ID?>',
				needCheckNotify: 'N',
				isAdmin: 'Y'
			});
		</script>
		<?
	}
	else
	{
		$helpLink = "http://www.bitrixsoft.com/help/index.html?page=" . urlencode("source/" . $module . "/help/en/" . $page . ".html");
		?>
		<span onclick="document.location.href = '<?=$helpLink?>';" class="adm-header-help-btn" id="bx_top_panel_button_helper">
		   <span class="adm-header-help-btn-icon"></span>
		   <span class="adm-header-help-btn-text"><?=GetMessage("top_panel_help")?></span>
		</span>
		<?
	}
?>

<?
	$Execs = $hkInstance->GetCodeByClassName("bx-panel-pin",GetMessage('top_panel_pin'));
	echo $hkInstance->PrintJSExecs($Execs);

endif;
?></div></div><div class="adm-header-bottom"></div><?

if ($USER->IsAdmin())
	echo CAdminNotify::GetHtml();

?></div><?

echo CAdminPage::ShowSound();
