<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Notifications\VendorNotificationTable;
use Bitrix\Main\Security\W\Rules\RuleRecordTable;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class CAdminTopPanel
{
	public static function Show(CAdminPage $adminPage, CAdminMenu $adminMenu)
	{
		global $USER, $APPLICATION;

		$session = \Bitrix\Main\Application::getInstance()->getSession();

		if (isset($_GET["back_url_pub"]) && !is_array($_GET["back_url_pub"]) && str_starts_with($_GET["back_url_pub"], "/"))
		{
			$session["BACK_URL_PUB"] = $_GET["back_url_pub"];
		}

		$arPanelButtons = [];

		//Help
		$module = (defined("ADMIN_MODULE_NAME") ? ADMIN_MODULE_NAME : "main");
		$page = (defined("HELP_FILE") && !str_contains(HELP_FILE, '/') ? HELP_FILE : basename($APPLICATION->GetCurPage()));

		$aActiveSection = $adminMenu->ActiveSection();
		$section = $aActiveSection["help_section"] . "/";
		if (defined("HELP_FILE") && str_starts_with(HELP_FILE, $section))
		{
			$section = "";
		}

		if ($USER->IsAuthorized())
		{
			$settingsButton = static::getSettingsButton();
			if (!empty($settingsButton))
			{
				$arPanelButtons[] = $settingsButton;
			}
		}

		$arPanelButtons[] = static::getLanguageButton();

		if ($USER->IsAuthorized())
		{
			$hkInstance = CHotKeys::getInstance();
			$Execs = $hkInstance->GetCodeByClassName("top_panel_menu", GetMessage("admin_panel_menu"));
			echo $hkInstance->PrintJSExecs($Execs);
			$Execs = $hkInstance->GetCodeByClassName("admin_panel_site", GetMessage("admin_panel_site"));
			echo $hkInstance->PrintJSExecs($Execs);
			$Execs = $hkInstance->GetCodeByClassName("admin_panel_admin", GetMessage("admin_panel_admin"));
			echo $hkInstance->PrintJSExecs($Execs);
		}

		// security status
		$context = \Bitrix\Main\Context::getCurrent();
		$modulesToUpdateCount = RuleRecordTable::getCount([], ['ttl' => 60]);
		$isPortal = \Bitrix\Main\ModuleManager::isModuleInstalled('intranet');

		$showWwallPopup = false;
		$wwallPopupLastView = $context->getRequest()->getCookie('WWALL_POPUP_LAST_VIEW');
		if ($USER->isAdmin() && $modulesToUpdateCount && empty($wwallPopupLastView))
		{
			$showWwallPopup = true;
			$context->getResponse()->addCookie(new \Bitrix\Main\Web\Cookie(
				'WWALL_POPUP_LAST_VIEW', '1', time() + 3600 * 48
			));
		}

		// security notifications
		$vendorNotification = static::getVendorNotification();

		// js for notifications
		if ($showWwallPopup || $vendorNotification)
		{
			Extension::load([
				'main.wwallpopup',
				'main.coreAjax',
			]);
		}
		?>

		<?php
		if ($showWwallPopup): // wwall popup ?>
			<script>
				BX.ready(function () {
					let popup = new BX.Main.WwallPopup({
						colorTheme: 'danger',
						isSuccess: false,
						isPortal: <?= Json::encode($isPortal) ?>,
						closeIcon: true,
						isToolTipShow: true
					});
					popup.show();
				});
			</script>
		<?php
		endif;
		?>


		<?php
		if (!$showWwallPopup && !empty($vendorNotification)): // security popup ?>
			<script>
				BX.ready(function () {

					let popup = new BX.Main.WwallPopup({
						colorTheme: '<?=CUtil::JSescape($vendorNotification['colorTheme'])?>',
						title: '<?=CUtil::JSescape(Loc::getMessage('SEC_WWALL_POPUP_NOTIFICATION_TITLE'))?>',
						subtitle: '<?=CUtil::JSescape($vendorNotification['title'])?>',
						text: '<?=CUtil::JSescape($vendorNotification['text'])?>',
						closeIcon: <?=!empty($vendorNotification['allowClose']) ? 'true' : 'false'?>,
						buttons: {
							primary: {
								text: '<?=CUtil::JSescape(Loc::getMessage('SEC_WWALL_POPUP_ACTION_ACCEPT'))?>',
								type: 'accept',
								onclick: () => {
									popup.close();

									BX.ajax.runAction('main.security.vendorNotification.sign', {
										data: {
											notificationId: '<?=CUtil::JSescape($vendorNotification['vendorId'])?>'
										}
									});
								}
							},
							<?php if (!empty($vendorNotification['allowClose'])): ?>
							secondary: {
								text: '<?=CUtil::JSescape(Loc::getMessage('SEC_WWALL_POPUP_ACTION_CLOSE'))?>',
								type: 'close',
								onclick: () => {
									popup.close();
								}
							},
							<?php endif; ?>
						}
					});

					popup.show();

					popup.popup.subscribeOnce('onClose', function () {
						BX.setCookie(
							BX.message('COOKIE_PREFIX') + '_SECURITY_POPUP_LAST_VIEW_<?=CUtil::JSescape($vendorNotification['vendorId'])?>',
							'1',
							{expires: 3600 * 48, path: '/'}
						);
					});
				});
			</script>
		<?php
		endif;
		?>

		<div id="bx-panel" class="adm-header">
			<div class="adm-header-left">
				<div class="adm-header-btn-wrap">
					<?php
					$params = DeleteParam(["logout", "back_url_pub", "sessid"]);
					$sPubUrl = (
							$session["BACK_URL_PUB"] <> ""
							? htmlspecialcharsbx($session["BACK_URL_PUB"]) . (str_contains($session["BACK_URL_PUB"], "?") ? "&amp;" : "?")
							: '/?'
						)
						. 'back_url_admin=' . urlencode($APPLICATION->GetCurPage() . ($params <> "" ? "?" . $params : ""));

					$adminPanelSiteList = [];

					if (Option::get("sale", "~IS_SALE_CRM_SITE_MASTER_FINISH") === "Y" || Option::get('sale', '~IS_SALE_BSM_SITE_MASTER_FINISH') === 'Y')
					{
						$adminPanelSiteIdList = [];
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
							$adminPanelDefaultServerName = Option::get("main", "server_name");
							$isAdminPanelDefaultSiteExists = (bool)array_filter($adminPanelSiteList, static function ($adminPanelSite) use ($adminPanelDefaultServerName) {
								return $adminPanelSite['SERVER_NAME'] === $adminPanelDefaultServerName;
							});
							if (!$isAdminPanelDefaultSiteExists)
							{
								array_unshift(
									$adminPanelSiteList,
									[
										'NAME' => Option::get("main", "site_name"),
										'SERVER_NAME' => $adminPanelDefaultServerName,
										'DEF' => 'Y',
										'SORT' => 1,
									]
								);
							}
							unset($adminPanelDefaultServerName);
						}
						unset($isAdminPanelDefaultSiteExists);

						if (count($adminPanelSiteList) > 1)
						{
							\Bitrix\Main\Type\Collection::sortByColumn($adminPanelSiteList, ['SORT' => SORT_ASC]);

							$adminPanelSiteMenu = [];
							$adminPanelDefaultButtonTitle = '';
							$adminPanelDefaultButtonLink = '';
							$adminPanelProtocol = $context->getRequest()->isHttps() ? "https://" : "http://";
							foreach ($adminPanelSiteList as $adminPanelSite)
							{
								$adminPanelSiteId = $adminPanelSite['ID'] ?? null;
								$adminPanelMenuItemTitle =
									$adminPanelSite['NAME']
									. ' (' . $adminPanelSite['SERVER_NAME'] . ')'
									. ($adminPanelSiteId ? ' [' . $adminPanelSiteId . ']' : '');
								$adminPanelMenuItemLink = $adminPanelProtocol . $adminPanelSite['SERVER_NAME'];

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
							<a id="bx-panel-view-site-btn" class="adm-header-notif-block"
							   href="<?= $adminPanelDefaultButtonLink ?>" title="<?= $adminPanelDefaultButtonTitle ?>"
							   hidefocus="true" onfocus="this.blur();">
								<strong>
									<span id="bx-panel-view-tab-select"
										  class="adm-header-notif-counter"><?= $adminPanelDefaultButtonTitle ?></span>
								</strong>
							</a>
							<script>
								BX.adminPanel.registerButton(
									"bx-panel-view-site-btn",
									{
										MENU: <?= Json::encode($adminPanelSiteMenu) ?>,
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
							<a hidefocus="true" href="<?= $sPubUrl ?>" id="bx-panel-view-tab"
							   class="adm-header-btn adm-header-btn-site"
							   title="<?= GetMessage("adm_top_panel_view_title") ?>"><?= GetMessage("admin_panel_site") ?></a>
						<?php
						}
					}
					else
					{
					?>
						<a hidefocus="true" href="<?= $sPubUrl ?>" id="bx-panel-view-tab"
						   class="adm-header-btn adm-header-btn-site"
						   title="<?= GetMessage("adm_top_panel_view_title") ?>"><?= GetMessage("admin_panel_site") ?></a>
						<?php
					}

					if (count($adminPanelSiteList) > 1)
					{
						?>
						<a hidefocus="true" href="<?= BX_ROOT . "/admin/index.php?lang=" . LANGUAGE_ID ?>"
						   class="adm-header-notif-block"><span
								class="adm-header-notif-counter"><?= GetMessage("admin_panel_admin") ?></span></a>
						<?php
					}
					else
					{
						?>
						<a hidefocus="true" href="<?= BX_ROOT . "/admin/index.php?lang=" . LANGUAGE_ID ?>"
						   class="adm-header-btn adm-header-btn-admin"><?= GetMessage("admin_panel_admin") ?></a>
						<?php
					}

					unset($adminPanelSiteList);
					?>
				</div>

				<?php
				if (\Bitrix\Main\Loader::includeModule('security')): ?>
					<?php
					if ($modulesToUpdateCount): ?>
						<a href="/bitrix/admin/security_dashboard.php"><div
							class="adm-header-btn adm-security-btn --warning"><?= Loc::getMessage('adm_top_panel_wwall_unsec') ?></div></a>
					<?php
					else: ?>
						<a href="/bitrix/admin/security_dashboard.php"><div class="adm-header-btn adm-security-btn"><?=
							$isPortal
								? Loc::getMessage('adm_top_panel_wwall_sec_cp')
								: Loc::getMessage('adm_top_panel_wwall_sec') ?></div></a>
					<?php
					endif; ?>
				<?php
				endif; ?>
				<?php
				$informerItemsCount = CAdminInformer::InsertMainItems();

				if ($USER->IsAuthorized() && $informerItemsCount > 0):

					?><span class="adm-header-notif-block" id="adm-header-notif-block"
							onclick="BX.adminInformer.Toggle(this);"
							title="<?= GetMessage("admin_panel_notif_block_title") ?>"><span
						class="adm-header-notif-icon"></span><span class="adm-header-notif-counter"
																   id="adm-header-notif-counter"><?= CAdminInformer::$alertCounter ?></span>
					</span><?
				endif;

				static::showTopPanelButtonsSection($arPanelButtons, $hkInstance);

				?></div>
			<div class="adm-header-right"><?
				if ($USER->IsAuthorized() && IsModuleInstalled("search")):

					?>
					<div class="adm-header-search-block" id="bx-search-box"><input class="adm-header-search"
					   id="bx-search-input"
					   onfocus="if (this.value=='<?= GetMessage("top_panel_search_def") ?>') {this.value=''; BX.addClass(this.parentNode,'adm-header-search-block-active');}"
					   value="<?= GetMessage("top_panel_search_def") ?>"
					   onblur="if (this.value==''){this.value='<?= GetMessage("top_panel_search_def") ?>'; BX.removeClass(this.parentNode,'adm-header-search-block-active');}"
					   type="text" autocomplete="off"/><a
							href="#" onclick="BX('bx-search-input').value=''; BX('bx-search-input').onblur();"
							class="adm-header-search-block-btn"></a></div>
					<script>
						let jsControl = new JCAdminTitleSearch({
							'AJAX_PAGE': '/bitrix/admin/get_search.php?lang=<?=LANGUAGE_ID?>',
							'CONTAINER_ID': 'bx-search-box',
							'INPUT_ID': 'bx-search-input',
							'MIN_QUERY_LEN': 1
						});
					</script><?

					$Execs = $hkInstance->GetCodeByClassName("bx-search-input", GetMessage("top_panel_search_def"));
					echo $hkInstance->PrintJSExecs($Execs);

				endif;
				?>
				<div class="adm-header-right-block"><?

					if ($USER->IsAuthorized()):
						$ssoSwitcher = $adminPage->getSSOSwitcherButton();
						$bShowSSO = is_array($ssoSwitcher) && !empty($ssoSwitcher);

						$userName = $USER->GetFormattedName();
						if ($bShowSSO)
						{
							$userName = '<span class="adm-header-separate-left">' . $userName . '</span><span class="adm-header-separate-right" id="bx-panel-sso"></span>';
						}

						if ($USER->CanDoOperation('view_own_profile') || $USER->CanDoOperation('edit_own_profile')):

							?><a hidefocus="true"
								 href="/bitrix/admin/user_edit.php?lang=<?= LANGUAGE_ID ?>&amp;ID=<?= $USER->GetID() ?>"
								 class="adm-header-user-block<?= $bShowSSO ? ' adm-header-separate' : '' ?>"
								 onfocus="this.blur()"><?= $userName; ?></a><?

						else:

							?><span class="adm-header-user-block<?= $bShowSSO ? ' adm-header-separate' : '' ?>"
									id="bx-panel-user"><?= $userName ?></span><?

						endif;

						if ($bShowSSO)
						{
						?>
							<script>BX.adminPanel.registerButton('bx-panel-sso', {MENU: <?= Json::encode($ssoSwitcher) ?>});</script>
							<?
						}

						?><a hidefocus="true"
							 href="<?= htmlspecialcharsbx((defined('BX_ADMIN_SECTION_404') && BX_ADMIN_SECTION_404 == 'Y' ? '/bitrix/admin/' : $APPLICATION->GetCurPage()) . '?' . CUser::getLogoutParams()) ?>"
							 class="adm-header-exit" id="bx-panel-logout"
							 title="<?= GetMessage('admin_panel_logout_title') ?>"><?= GetMessage("admin_panel_logout") ?></a><?

						$Execs = $hkInstance->GetCodeByClassName("bx-panel-logout", GetMessage('admin_panel_logout'));
						echo $hkInstance->PrintJSExecs($Execs);

					endif;

					static::showTopPanelButtonsSection($arPanelButtons, $hkInstance, 1);

					if ($USER->IsAuthorized()):
						if ($hkInstance->IsActive()):

							?><a hidefocus="true" id="bx-panel-hotkeys" href="javascript:void(0)"
								 onclick="BXHotKeys.ShowSettings();" class="header-keyboard"
								 title="<?= GetMessage('admin_panel_hotkeys_title') ?>"></a><?

						endif;

						$aUserOpt = CUserOptions::GetOption("admin_panel", "settings");

						?><a hidefocus="true" href="javascript:void(0)" id="bx-panel-pin" class="adm-header-pin"
							 onclick="BX.adminPanel.Fix(this)"
							 title="<?= GetMessage('top_panel_pin_' . (isset($aUserOpt['fix']) && $aUserOpt['fix'] == 'on' ? 'off' : 'on')) ?>"></a><?

						if (LANGUAGE_ID == "ru")
						{
							CJSCore::Init(['helper']);
							$helpUrl = (new Uri('https://helpdesk.bitrix24.ru/widget2/dev/'))->addParams([
								"url" => "https://" . $_SERVER["HTTP_HOST"] . $APPLICATION->GetCurPageParam(),
								"user_id" => $USER->GetID(),
								"is_admin" => $USER->IsAdmin() ? 1 : 0,
								"help_url" => "https://dev.1c-bitrix.ru/user_help/" . $section . (defined("HELP_FILE") && str_contains(HELP_FILE, '/') ? HELP_FILE : $module . "/" . $page),
							]);

							$frameOpenUrl = (clone $helpUrl)->addParams([
								"action" => "open",
							])->getUri();
							?>
								<span class="adm-header-help-btn" id="bx_top_panel_button_helper"
									  <? if (!isset($helperHeroOption["show"])): ?>onclick="BX.userOptions.save('main', 'helper_hero_admin',  'show', 'Y');"<?endif ?>>
						   <span class="adm-header-help-btn-icon"></span>
						   <span class="adm-header-help-btn-text"><?= GetMessage("top_panel_help") ?></span>
						</span>
								<script>
									BX.Helper.init({
										frameOpenUrl: '<?=$frameOpenUrl?>',
										helpBtn: BX('bx_top_panel_button_helper'),
										langId: '<?=LANGUAGE_ID?>',
										needCheckNotify: 'N',
										isAdmin: 'Y'
									});
								</script>
							<?
						}
						else
						{
							$helpLink = "https://www.bitrixsoft.com/help/index.html?page=" . urlencode("source/" . $module . "/help/en/" . $page . ".html");
							?>
								<span onclick="document.location.href = '<?= $helpLink ?>';" class="adm-header-help-btn"
									  id="bx_top_panel_button_helper">
						   <span class="adm-header-help-btn-icon"></span>
						   <span class="adm-header-help-btn-text"><?= GetMessage("top_panel_help") ?></span>
						</span>
							<?
						}

						$Execs = $hkInstance->GetCodeByClassName("bx-panel-pin", GetMessage('top_panel_pin'));
						echo $hkInstance->PrintJSExecs($Execs);

					endif;
					?></div>
			</div>
			<div class="adm-header-bottom"></div><?

			if ($USER->IsAdmin())
			{
				echo CAdminNotify::GetHtml();
			}

			?></div>

		<?php
	}

	protected static function showTopPanelButtonsSection($arPanelButtons, $hkInstance, $section = null)
	{
		global $USER;

		foreach ($arPanelButtons as $item):
			if (isset($item["SEPARATOR"]) && $item["SEPARATOR"])
			{
				continue;
			}
			if ($section == null && isset($item['SECTION']))
			{
				continue;
			}
			if ($section != null && (!isset($item['SECTION']) || $item['SECTION'] != $section))
			{
				continue;
			}

			$id = $item['ID'] ?? 'bx_top_panel_button_' . RandString();
			$bHasMenu = (!empty($item["MENU"]) && is_array($item["MENU"]));

			if ($USER->IsAuthorized())
			{
				echo $hkInstance->PrintTPButton($item);
			}

			if ($item['LINK']):

				?><a id="<?= htmlspecialcharsEx($id) ?>"
					 href="<?= htmlspecialcharsEx($item['LINK']) ?>" class="<?= $item['ICON'] ?>"<?= isset($item["TITLE"]) ? ' title="' . htmlspecialcharsEx($item["TITLE"]) . '"' : '' ?><?= isset($item["TARGET"]) ? ' target="' . htmlspecialcharsEx($item["TARGET"]) . '"' : '' ?>
					 hidefocus="true" onfocus="this.blur();"><?= htmlspecialcharsbx($item["TEXT"]) ?></a><?

			else:

				?><span
				id="<?= htmlspecialcharsEx($id) ?>" class="<?= $item['ICON'] ?>"<?= isset($item["TITLE"]) ? 'title="' . htmlspecialcharsEx($item["TITLE"]) . '"' : '' ?>><?= htmlspecialcharsbx($item["TEXT"]) ?></span><?

			endif;

			if ($bHasMenu || (isset($item['TOOLTIP']) && $item['TOOLTIP'] && $item['TOOLTIP_ID'])):
				?>
				<script><?

					if (isset($item['TOOLTIP']) && $item['TOOLTIP']):
						if (isset($item['TOOLTIP_ID']) && $item['TOOLTIP_ID']):

						?>
						BX.ready(function () {
							BX.hint(BX('<?=CUtil::JSEscape($id)?>'), '<?=CUtil::JSEscape($item["TITLE"])?>', '<?=CUtil::JSEscape($item['TOOLTIP'])?>', '<?=CUtil::JSEscape($item['TOOLTIP_ID'])?>')
						});
						<?

						endif;
					endif;
					if ($bHasMenu):

					?>
					BX.adminPanel.registerButton('<?=CUtil::JSEscape($id)?>', {MENU: <?= Json::encode($item['MENU']) ?>});
					<?

					endif;

					?></script><?

			endif;
		endforeach;
	}

	protected static function getLanguageButton(): array
	{
		$arLangs = CLanguage::GetLangSwitcherArray();

		$arLangButton = [];
		$arLangMenu = [];

		foreach ($arLangs as $adminLang)
		{
			if ($adminLang['SELECTED'])
			{
				$arLangButton = [
					"TEXT" => strtoupper($adminLang["LID"]),
					"TITLE" => GetMessage("top_panel_lang") . " " . $adminLang["NAME"],
					"LINK" => htmlspecialcharsback($adminLang["PATH"]),
					"SECTION" => 1,
					"ICON" => "adm-header-language",
				];
			}

			$arLangMenu[] = [
				"TEXT" => '(' . $adminLang["LID"] . ') ' . $adminLang["NAME"],
				"TITLE" => GetMessage("top_panel_lang") . " " . $adminLang["NAME"],
				"LINK" => htmlspecialcharsback($adminLang["PATH"]),
			];
		}

		if (count($arLangMenu) > 1)
		{
			CJSCore::Init(['admin_interface']);
			$arLangButton['MENU'] = $arLangMenu;
		}
		return $arLangButton;
	}

	protected static function getSettingsButton(): array
	{
		global $USER, $APPLICATION;

		$settingsButton = [];
		$bCanViewSettings = (is_callable([$USER, 'CanDoOperation']) && ($USER->CanDoOperation('view_other_settings') || $USER->CanDoOperation('edit_other_settings')));
		if ($bCanViewSettings)
		{
			$settingsUrl = BX_ROOT . "/admin/settings.php?lang=" . LANG
				. "&mid=" . (defined("ADMIN_MODULE_NAME") ? ADMIN_MODULE_NAME : "main")
				. ($APPLICATION->GetCurPage() <> BX_ROOT . "/admin/settings.php" ? "&back_url_settings=" . urlencode($_SERVER["REQUEST_URI"]) : "");

			$settingsButton = [
				"TEXT" => GetMessage("top_panel_settings"),
				"TITLE" => GetMessage("button_settings"),
				"LINK" => $settingsUrl,
				"ICON" => "adm-header-setting-btn",
				"HK_ID" => "top_panel_settings",
			];
		}
		return $settingsButton;
	}

	protected static function getVendorNotification()
	{
		global $USER;

		$vendorNotification = null;
		$context = \Bitrix\Main\Context::getCurrent();

		$notifications = VendorNotificationTable::query()
			->addSelect('*')
			->where('NOT_SIGNED', true)
			->fetchAll()
		;

		foreach ($notifications as $notification)
		{
			try
			{
				$notificationData = Json::decode($notification['DATA']);

				// admin filter
				if (!empty($notificationData['forAdmin']) && !$USER->isAdmin())
				{
					continue;
				}

				// last view timeout
				$securityPopupLastView = $context->getRequest()->getCookie('SECURITY_POPUP_LAST_VIEW_' . $notification['VENDOR_ID']);

				if (!empty($securityPopupLastView))
				{
					continue;
				}

				// sanitize
				$sanitizer = new CBXSanitizer();
				$sanitizer->setLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);

				$notificationData['title'] = strip_tags($notificationData['title']);
				$notificationData['text'] = $sanitizer->SanitizeHtml($notificationData['text']);

				if (empty($notificationData['colorTheme'])
					&& !in_array($notificationData['colorTheme'], ['danger', 'warning', 'success']))
				{
					$notificationData['colorTheme'] = 'warning';
				}

				$vendorNotification = $notificationData;
				$vendorNotification['vendorId'] = $notification['VENDOR_ID'];

				break;
			}
			catch (Throwable)
			{
			}
		}
		return $vendorNotification;
	}
}
