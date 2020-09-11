<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** var LandingViewComponent $component */

use Bitrix\Landing\Config;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Assets;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);
Loc::loadMessages(Manager::getDocRoot() . '/bitrix/modules/landing/lib/mutator.php');

// assets, extensions
Extension::load([
	'ui.buttons',
	'ui.buttons.icons',
	'ui.alerts',
	'ui.fonts.opensans',
	'ui.info-helper',
	'sidepanel',
	'popup_menu',
	'marketplace',
	'applayout',
	'landing_master'
]);
$assets = Assets\Manager::getInstance();
$assets->addAsset(
	'landing_master',
	Assets\Location::LOCATION_KERNEL
);
$assets->addAsset(
	Config::get('js_core_edit'),
	Assets\Location::LOCATION_KERNEL
);

// errors output
if ($arResult['ERRORS'])
{
	$errors = $arResult['ERRORS'];
	if (isset($errors['LICENSE_EXPIRED']))
	{
		$link = Manager::isB24()
				? 'https://www.bitrix24.ru/prices/self-hosted.php'
				: 'https://www.1c-bitrix.ru/buy/cms.php#tab-updates-link';
		?>
		<div class="landing-license-wrapper">
			<div class="landing-license-inner">
				<div class="landing-license-icon-container">
					<div class="landing-license-icon"></div>
				</div>
				<div class="landing-license-info">
					<span class="landing-license-info-text"><?= $errors['LICENSE_EXPIRED'];?></span>
					<div class="landing-license-info-btn">
						<?= Loc::getMessage('LANDING_TPL_BUY_RENEW', array(
							'#LINK1#' => '<a href="' . $link . '" target="_blank" class="landing-license-info-link">',
							'#LINK2#' => '</a>'
						));?>
					</div>
				</div>
			</div>
		</div>
		<?
		return;
	}
	elseif (isset($errors['SITE_IS_NOW_CREATING']))
	{
		?>
		<div class="landing-view-loader-container">
			<div class="main-ui-loader main-ui-show" data-is-shown="true" style="">
				<svg class="main-ui-loader-svg" viewBox="25 25 50 50">
					<circle class="main-ui-loader-svg-circle" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle>
				</svg>
			</div>
			<div class="landing-view-loader-text"><?= Loc::getMessage('LANDING_WAIT_WHILE_CREATING');?></div>
		</div>
		<script type="text/javascript">
			BX.ready(function()
			{
				setTimeout(function() {
					window.location.href = "<?= \CUtil::jsEscape($arResult['LANDING_FULL_URL']);?>"
				}, 3000);
			});
		</script>
		<?
		return;
	}
	else
	{
		// send to user all other errors
		foreach ($errors as $errorCode => $errorMessage)
		{
			$errorMessage .= $component->getSettingLinkByError(
				$errorCode
			);
			?>
			<script type="text/javascript">
				BX.ready(function()
				{
					if (
						top.window.opener &&
						typeof top.window.opener.landingAlertMessage !== 'undefined'
					)
					{
						top.window.opener.landingAlertMessage(
							'<?= \CUtil::jsEscape($errorMessage);?>',
							<?= $component->isTariffError($errorCode) ? 'true' : 'false';?>,
							'<?= $errorCode;?>'
						);
						top.window.close();
					}
					else if (typeof landingAlertMessage !== 'undefined')
					{
						landingAlertMessage(
							'<?= \CUtil::jsEscape($errorMessage);?>',
							<?= $component->isTariffError($errorCode) ? 'true' : 'false';?>,
							'<?= $errorCode;?>'
						);
					}
				});
			</script>
			<?
			break;
		}
		unset($errorCode, $errorMessage);
	}
}

if ($arResult['FATAL'])
{
	return;
}

$site = $arResult['SITE'];
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$successSave = $arResult['SUCCESS_SAVE'];
$curUrl = $arResult['CUR_URI'];
$urls = $arResult['TOP_PANEL_CONFIG']['urls'];
$this->getComponent()->initAPIKeys();

if ($request->get('close') == 'Y')
{
	?>
	<script type="text/javascript">
		if (top.window !== window)
		{
			top.window.location.reload();
		}
	</script>
	<div class="landing-view-loader-container">
		<div class="main-ui-loader main-ui-show" data-is-shown="true" style="">
			<svg class="main-ui-loader-svg" viewBox="25 25 50 50">
				<circle class="main-ui-loader-svg-circle" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle>
			</svg>
		</div>
	</div>
	<?
	return;
}

// top panel
if (!$request->offsetExists('landing_mode')):
	// b24 title
	$b24Title = \Bitrix\Main\Config\Option::get('bitrix24', 'site_title', '');
	$b24Logo = \Bitrix\Main\Config\Option::get('bitrix24', 'logo24show', 'Y');
	if (!$b24Title)
	{
		$b24Title = Loc::getMessage(
			'LANDING_TPL_START_PAGE_LOGO' . (!Manager::isB24() ? '_SMN' : '')
		);
	}
	// tpl vars
	$helpUrl = \Bitrix\Landing\Help::getHelpUrl('LANDING_EDIT');
	$startChain = $component->getMessageType('LANDING_TPL_START_PAGE');
	$lightMode = $arParams['PANEL_LIGHT_MODE'] == 'Y';
	$panelModifier = $lightMode ? ' landing-ui-panel-top-light' : '';
	// informer
	if (\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
	{
		$APPLICATION->includeComponent('bitrix:ui.info.helper', '', []);
	}
	?>
	<div class="landing-ui-panel landing-ui-panel-top<?= $panelModifier;?>">
		<div class="landing-ui-panel-top-logo">
		<?if ($arParams['PAGE_URL_URL_SITES']):?>
			<a href="<?= $arParams['PAGE_URL_URL_SITES'];?>" data-slider-ignore-autobinding="true"><?
				?><span class="landing-ui-panel-top-logo-text"><?= \htmlspecialcharsbx($b24Title);?></span><?
				if ($b24Logo != 'N'):
					?><span class="landing-ui-panel-top-logo-color"><?= Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24');?></span><?
				endif;?>
			</a>
		<?else:?>
			<span class="landing-ui-panel-top-logo-text"><?= \htmlspecialcharsbx($b24Title);?></span><?
			if ($b24Logo != 'N'):
				?><span class="landing-ui-panel-top-logo-color"><?= Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24');?></span>
			<?endif;?>
		<?endif;?>
		</div>
		<div class="landing-ui-panel-top-chain">
			<?if ($arParams['PAGE_URL_URL_SITES']):?>
				<a href="<?= $arParams['PAGE_URL_URL_SITES'];?>" data-slider-ignore-autobinding="true" class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-chain-link-sites" title="<?= $startChain;?>">
					<?= $startChain;?>
				</a><strong class="landing-ui-panel-top-chain-link-separator"><span></span></strong>
			<?endif;?>
			<a href="<?= ($arResult['SITES_COUNT'] <= 1) ? $arParams['PAGE_URL_LANDINGS'] : '#';?>" <?
				?>id="landing-navigation-site" <?
				echo ($arResult['SITES_COUNT'] > 1) ? ' data-slider-ignore-autobinding="true"' : ''
				?><?
				?>class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-chain-link-site<?= (($arResult['SITES_COUNT'] <= 1) ? ' landing-ui-no-icon' : '');?>" <?
				?>title="<?= \htmlspecialcharsbx($site['TITLE']);?>">
				<span class="ui-btn-text"><?= \htmlspecialcharsbx($site['TITLE']);?></span>
			</a>
			<strong class="landing-ui-panel-top-chain-link-separator"><span></span></strong>
			<a href="<?= ($arResult['PAGES_COUNT'] <= 1) ? $arParams['PAGE_URL_LANDINGS'] : '#';?>" <?
				?>id="landing-navigation-page" <?
				echo ($arResult['PAGES_COUNT'] > 1) ? ' data-slider-ignore-autobinding="true"' : ''
				?>class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-chain-link-page<?= (($arResult['PAGES_COUNT'] <= 1) ? ' landing-ui-no-icon' : '');?>" <?
				?>title="<?= \htmlspecialcharsbx($arResult['LANDING']->getTitle());?>">
				<span class="ui-btn-text"><?= \htmlspecialcharsbx($arResult['LANDING']->getTitle());?></span>
			</a>
		</div>
		<div class="landing-ui-panel-top-devices">
			<div class="landing-ui-panel-top-devices-inner">
				<button class="landing-ui-button landing-ui-button-desktop active" data-id="desktop_button"></button>
				<button class="landing-ui-button landing-ui-button-tablet" data-id="tablet_button"></button>
				<button class="landing-ui-button landing-ui-button-mobile" data-id="mobile_button"></button>
			</div>
		</div>
		<div class="landing-ui-panel-top-history">
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-undo landing-ui-disabled"></span>
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-redo landing-ui-disabled"></span>
		</div>
		<div class="landing-ui-panel-top-menu" id="landing-panel-settings">
			<span class="ui-btn ui-btn-light-border ui-btn-icon-setting landing-ui-panel-top-menu-link landing-ui-panel-top-menu-link-settings" title="<?= Loc::getMessage('LANDING_TPL_SETTINGS_URL');?>"></span>
			<span class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-ui-panel-top-chain-link landing-ui-panel-top-menu-link-settings">
				<?= Loc::getMessage('LANDING_TPL_SETTINGS_URL');?>
			</span>

			<?if ($arParams['DRAFT_MODE'] != 'Y'):?>
			<a href="<?= $urls['preview']->getUri();?>" id="landing-urls-preview" <?
				?>data-slider-ignore-autobinding="true" <?
				?>class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link landing-btn-menu" <?
				?>target="<?= ($arParams['DONT_LEAVE_AFTER_PUBLICATION'] == 'Y') ? '_self' : '_blank';?>">
				<?= Loc::getMessage('LANDING_TPL_PREVIEW_URL');?>
			</a>
			<?endif;?>

			<?if (!$lightMode && $arParams['DRAFT_MODE'] != 'Y'):?>
				<div class="ui-btn-split ui-btn-primary landing-btn-menu<?= !$arResult['CAN_PUBLIC_SITE'] ? ' ui-btn-disabled' : '';?>">
					<a href="<?= ($arParams['FULL_PUBLICATION'] == 'Y') ? $urls['publicationAll']->getUri() : $urls['publication']->getUri();?>" <?
					?>id="landing-publication" data-slider-ignore-autobinding="true" <?
					?>class="ui-btn-main" <?
					?>target="<?= ($arParams['DONT_LEAVE_AFTER_PUBLICATION'] == 'Y') ? '_self' : '_blank';?>">
						<?= Loc::getMessage('LANDING_TPL_PUBLIC_URL');?>
					</a>
					<span id="landing-publication-submenu" class="ui-btn-extra"></span>
				</div>
			<?elseif ($arParams['DRAFT_MODE'] != 'Y'):?>
				<a href="<?= ($arParams['FULL_PUBLICATION'] == 'Y') ? $urls['publicationAll']->getUri() : $urls['publication']->getUri();?>" id="landing-publication"
					class="ui-btn ui-btn-primary landing-btn-menu<?= !$arResult['CAN_PUBLIC_SITE'] ? ' ui-btn-disabled' : '';?>" <?
					?>target="<?= ($arParams['DONT_LEAVE_AFTER_PUBLICATION'] == 'Y') ? '_self' : '_blank';?>">
					<?= Loc::getMessage('LANDING_TPL_PUBLIC_URL');?>
				</a>
			<?endif;?>
			<?if ($helpUrl):?>
			<a href="<?= $helpUrl;?>" class="ui-btn ui-btn-light ui-btn-round landing-ui-panel-top-menu-link landing-ui-panel-top-menu-link-help" target="_blank">
				<span class="landing-ui-panel-top-menu-link-help-icon">?</span>
			</a>
			<?endif;?>
		</div>
	</div>
	<div class="landing-ui-view-container">
<?endif;?>

<script type="text/javascript">
	var landingParams = <?= \CUtil::phpToJSObject($arParams);?>;
	BX.ready(function()
	{
		BX.message({
			LANDING_SITE_TYPE: '<?= $arParams['TYPE'];?>',
			LANDING_PUBLIC_PAGE_REACHED: '<?= \CUtil::jsEscape(\Bitrix\Landing\Restriction\Manager::getSystemErrorMessage('limit_sites_number_page'));?>',
			LANDING_TPL_SETTINGS_SITE_URL: '<?= \CUtil::jsEscape($component->getMessageType('LANDING_TPL_SETTINGS_SITE_URL'));?>',
			LANDING_TPL_SETTINGS_CATALOG_URL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SETTINGS_CATALOG_URL'));?>',
			LANDING_TPL_SETTINGS_UNPUBLIC: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SETTINGS_UNPUBLIC'));?>',
			LANDING_TPL_PUBLIC_URL_PAGE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_PUBLIC_URL_PAGE'));?>',
			LANDING_TPL_PUBLIC_URL_ALL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_PUBLIC_URL_ALL'));?>',
			LANDING_TPL_SETTINGS_PAGE_URL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SETTINGS_PAGE_URL'));?>'
		});
	});
</script>

<?
// editor frame
if ($request->offsetExists('landing_mode'))
{
	if ($request->get('landing_mode') == 'edit')
	{
		Manager::setPageView('MainClass', 'landing-edit-mode');
		if (!$arResult['CAN_EDIT_SITE'])
		{
			Manager::setPageView('MainClass', 'landing-ui-hide-controls');
		}
	}
	$arResult['LANDING']->view();
	?>
	<style type="text/css">
		.bx-session-message {
			display: none;
		}
	</style>
	<script type="text/javascript">
		BX.Landing.Component.View.create(
			<?= \CUtil::phpToJSObject($arResult['TOP_PANEL_CONFIG']);?>
		);
	</script>
	<?if ($request->get('forceLoad') == 'true'):?>
		<script type="text/javascript">
			BX.namespace('BX.Landing');
			BX.Landing.Block = function() {};
			BX.Landing.Main = function() {};
			BX.Landing.Main.createInstance = function() {};
		</script>
	<?endif;
}
// top panel
else
{
	// exec theme-hooks for correct assets
	$hooksSite = \Bitrix\Landing\Hook::getForSite($arResult['LANDING']->getSiteId());
	$hooksLanding = \Bitrix\Landing\Hook::getForLanding($arResult['LANDING']->getId());
	if (
		isset($hooksSite['THEME']) &&
		$hooksSite['THEME']->enabled()
	)
	{
		$hooksSite['THEME']->exec();
	}
	if (
		isset($hooksLanding['THEME']) &&
		$hooksLanding['THEME']->enabled()
	)
	{
		$hooksLanding['THEME']->exec();
	}
	// title
	Manager::setPageTitle(
		\htmlspecialcharsbx($arResult['LANDING']->getTitle())
	);
	// available view
	$check = \Bitrix\Landing\Restriction\Manager::isAllowed(
		'limit_knowledge_base_number_page_view',
		['ID' => $arResult['LANDING']->getSiteId()]
	);
	if (!$check)
	{
		?>
		<script>
			BX.ready(function()
			{
				document.body.style.opacity = 0.1;
				top.BX.UI.InfoHelper.show('limit_knowledge_base_number_page_view');
			});
		</script>
		<?
	}
	?>
	<style type="text/css">
		html, body {
			height: 100%;
			overflow: hidden;
		}
	</style>
	<script type="text/javascript">
		BX.ready(function() {
			<?if ($successSave):?>
			if (typeof BX.SidePanel !== 'undefined')
			{
				BX.SidePanel.Instance.close();
			}
			<?endif;?>
			BX.Landing.Component.View.create(
				<?= \CUtil::phpToJSObject($arResult['TOP_PANEL_CONFIG']);?>,
				true
			);
		});
	</script>
	<div class="landing-ui-view-wrapper">
		<div class="landing-editor-loader-container"></div>
			<div class="landing-editor-required-user-action">
				<h3></h3>
				<p></p>
				<div>
					<a href="" class="ui-btn"></a>
				</div>
			</div>
			<div class="landing-ui-view-iframe-wrapper">
				<iframe src="<?= $urls['landingFrame']->getUri();?>" class="landing-ui-view" id="landing-view-frame" allowfullscreen></iframe>
			</div>
		</div>
	</div>
<?
}