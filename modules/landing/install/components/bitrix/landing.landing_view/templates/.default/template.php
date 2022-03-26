<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \LandingViewComponent $component */
/** @var \CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

use \Bitrix\Landing\Config;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Assets;
use \Bitrix\Landing\Site;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\UI\Extension;
use \Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);
Loc::loadMessages(Manager::getDocRoot() . '/bitrix/modules/landing/lib/mutator.php');

// assets, extensions
Extension::load([
	'ui.buttons',
	'ui.buttons.icons',
	'ui.alerts',
	'ui.icons',
	'ui.fonts.opensans',
	'ui.info-helper',
	'ui.notification',
	'sidepanel',
	'popup_menu',
	'marketplace',
	'applayout',
	'landing_master',
	'helper',
	'landing.metrika',
	'main.qrcode',
	'ui.hint'
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
			<div class="main-ui-loader main-ui-show" data-is-shown="true">
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
		// send to user all others errors
		foreach ($errors as $errorCode => $errorMessage)
		{
			$errorMessage .= $component->getSettingLinkByError(
				$errorCode
			);
			if ($arResult['FATAL'])
			{
				?>
				<div class="landing-error-page">
					<div class="landing-error-page-inner">
						<div class="landing-error-page-title"><?= $errorMessage;?></div>
						<div class="landing-error-page-img">
							<div class="landing-error-page-img-inner"></div>
						</div>
					</div>
				</div>
				<?
			}
			else
			{
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
					});
				</script>
				<?
			}
			break;
		}
	}
}

if ($arResult['FATAL'])
{
	return;
}


$site = $arResult['SITE'];
$siteId = $arResult['LANDING']->getSiteId();
$folderId = $arResult['LANDING']->getFolderId();
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$successSave = $arResult['SUCCESS_SAVE'];
$curUrl = $arResult['CUR_URI'];
$urls = $arResult['TOP_PANEL_CONFIG']['urls'];
$this->getComponent()->initAPIKeys();
$formEditor = $arResult['SPECIAL_TYPE'] == Site\Type::PSEUDO_SCOPE_CODE_FORMS;

$urlLandingAdd = str_replace(['#site_show#', '#landing_edit#'], [$siteId, 0], $arParams['~PARAMS']['sef_url']['landing_edit'] ?? '');
$urlFolderAdd = str_replace(['#site_show#', '#landing_edit#'], [$siteId, 0], $arParams['~PARAMS']['sef_url']['site_show'] ?? '');
$urlLandingAdd = $component->getPageParam($urlLandingAdd, ['folderId' => $folderId]);
$urlFolderAdd = $component->getPageParam($urlFolderAdd, ['folderId' => $folderId, 'folderNew' => 'Y']);

if ($formEditor)
{
	$arParams['PAGE_URL_URL_SITES'] = '/crm/webform/';
	Extension::load([
		'landing.ui.panel.formsettingspanel',
		'crm.form.embed',
	]);
}

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
		<div class="main-ui-loader main-ui-show" data-is-shown="true">
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
	// tpl vars
	$startChain = $component->getMessageType('LANDING_TPL_START_PAGE');
	$lightMode = $arParams['PANEL_LIGHT_MODE'] == 'Y';
	$panelModifier = $lightMode ? ' landing-ui-panel-top-light' : '';
	$panelModifier .= $formEditor ? ' landing-ui-panel-top-form' : '';
	// feedback form
	$formCode = '';
	if (!isset($arResult['LICENSE']) || $arResult['LICENSE'] != 'nfr')
	{
		$formCode = ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP') ? 'knowledge' : 'developer';
		?>
		<div style="display: none">
			<?$APPLICATION->includeComponent(
				'bitrix:ui.feedback.form',
				'',
				$component->getFeedbackParameters($formCode)
			);?>
		</div>
		<?
	}
	?>
	<div class="landing-ui-panel landing-ui-panel-top<?= $panelModifier;?>">
		<!-- region Logotype -->
		<div class="landing-ui-panel-top-logo">
			<a href="<?= $arParams['PAGE_URL_URL_SITES']?>" class="landing-ui-panel-top-logo-link" data-slider-ignore-autobinding="true">
				<span class="landing-ui-panel-top-logo-home-btn">
					<svg class='landing-ui-panel-top-logo-home-btn-icon' width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M11.902 19.6877V15.8046C11.902 15.5837 12.0811 15.4046 12.302 15.4046H14.5087C14.7296 15.4046 14.9087 15.5837 14.9087 15.8046V19.6877C14.9089 19.9086 15.0879 20.0876 15.3087 20.0878L18.8299 20.0891C19.0508 20.0893 19.2299 19.9103 19.23 19.6894C19.23 19.6893 19.23 19.6893 19.2299 19.6892V13.4563C19.2299 13.4365 19.2275 13.4142 19.2275 13.3943H20.4332C20.6633 13.3943 20.8604 13.2883 20.9909 13.0932C21.1189 12.9005 21.1425 12.6747 21.0581 12.4561C20.9519 12.1816 14.2383 5.92948 14.2047 5.90379C13.7957 5.59077 13.3216 5.58796 12.9131 5.89536C12.8759 5.92337 6.15525 12.1815 6.04901 12.4561C5.96462 12.6729 5.99059 12.9011 6.11629 13.0932C6.24671 13.2859 6.44145 13.3943 6.67162 13.3943H7.87965C7.87729 13.4142 7.87729 13.4365 7.87729 13.4563V19.6846C7.8776 19.9054 8.0565 20.0844 8.27729 20.0849L11.502 20.0874C11.7229 20.0879 11.9021 19.9089 11.9023 19.688C11.9023 19.6879 11.9023 19.6878 11.902 19.6877Z" fill="#525C69"/>
					</svg>
				</span>
				<?
				if (Manager::isB24() && $formEditor)
				{
					echo '<span class="landing-ui-panel-top-logo-text">'.Loc::getMessage("LANDING_TPL_START_PAGE_LOGO").'</span>'
						.'<span class="landing-ui-panel-top-logo-color">'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24').'</span>'
						.'<span class="landing-ui-panel-top-logo-text">.'.Loc::getMessage('LANDING_TPL_START_PAGE_FORM_LOGO_SMN').'</span>';
				}
				else if (!Manager::isB24() && $formEditor)
				{
					echo '<span class="landing-ui-panel-top-logo-text">'.Loc::getMessage("LANDING_TPL_START_PAGE_FORM_LOGO_SMN").'</span>'
						.'<span class="landing-ui-panel-top-logo-color">'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24').'</span>';
				}
				else if(Manager::isB24() && $arParams['PANEL_LIGHT_MODE'] == 'Y')
				{
					echo '<span class="landing-ui-panel-top-logo-text">'.Loc::getMessage("LANDING_TPL_START_PAGE_LOGO").'</span>'
						.'<span class="landing-ui-panel-top-logo-color">'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24').'</span>'
						.'<span class="landing-ui-panel-top-logo-text">.'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_KB').'</span>';
				}
				else if (Manager::isB24())
				{
					echo '<span class="landing-ui-panel-top-logo-text">'.Loc::getMessage("LANDING_TPL_START_PAGE_LOGO").'</span>'
					.'<span class="landing-ui-panel-top-logo-color">'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24').'</span>'
					.'<span class="landing-ui-panel-top-logo-text">.'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_SMN').'</span>';
				}
				else if (!Manager::isB24() && $arParams['PANEL_LIGHT_MODE'] == 'Y')
				{
					echo '<span class="landing-ui-panel-top-logo-text">'.Loc::getMessage("LANDING_TPL_START_PAGE_LOGO_KB").'</span>'
						.'<span class="landing-ui-panel-top-logo-color">'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24').'</span>';
				}
				else if (!Manager::isB24())
				{
					echo '<span class="landing-ui-panel-top-logo-text">'.Loc::getMessage("LANDING_TPL_START_PAGE_LOGO_SMN").'</span>'
						.'<span class="landing-ui-panel-top-logo-color">'.Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24').'</span>';
				}
				?>
			</a>
		</div>
		<!-- endregion -->

		<!-- region landing.selector -->
		<?if (!$formEditor):?>
			<div class="landing-ui-panel-top-selector">
				<?$APPLICATION->includeComponent('bitrix:landing.selector', '', [
					'TYPE' => $arParams['TYPE'],
					'SITE_ID' => $siteId,
					'FOLDER_ID' => $folderId,
					'LANDING_ID' => $arResult['LANDING']->getId(),
					'INPUT_VALUE' => $arResult['LANDING']->getTitle(),
					'PAGE_URL_LANDING_VIEW' => $arParams['~PARAMS']['sef_url']['landing_view'] ?? '',
					'PAGE_URL_LANDING_ADD' => $urlLandingAdd,
					'PAGE_URL_FOLDER_ADD' => $urlFolderAdd
				]);?>
			</div>
		<?else:?>
			<div class="landing-ui-panel-top-form-name">
				<span
					class="landing-ui-panel-top-form-name-inner"
					title="<?=htmlspecialcharsbx($arResult['FORM_NAME'])?>"><?php
						echo htmlspecialcharsbx($arResult['FORM_NAME']);
				?></span>
			</div>
		<?endif;?>
		<!--  endregion -->

		<?
		// region Autopub
		$panelBtnAutoPubClass = 'landing-ui-panel-top-pub-btn';
		$panelBtnAutoPubClass .= (!$arResult['FAKE_PUBLICATION']) ? ' landing-ui-panel-top-pub-btn-error' : '';
		$panelBtnAutoPubClass .= ($arResult['TOP_PANEL_CONFIG']['autoPublicationEnabled'] == 1) ? ' landing-ui-panel-top-pub-btn-auto' : '';

		if ($arParams['DRAFT_MODE'] != 'Y'):
			?>
			<button class="<?=$panelBtnAutoPubClass?>" id="landing-popup-publication-btn" data-hint="<?=Loc::getMessage('LANDING_SITE_HINT_AUTOPUBLISHING_OPTIONS')?>" data-hint-no-icon>
				<svg class="landing-ui-panel-top-pub-btn-icon" width="25" height="25" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill="#C6CDD3" class="landing-ui-panel-top-pub-btn-icon-defs-cloud"  d="M18.5075 18.8896H10.4177C10.3485 18.8896 10.2799 18.887 10.2119 18.882C8.38363 18.8398 6.91434 17.3271 6.91434 15.4671C6.91487 14.5606 7.27128 13.6914 7.90517 13.0507C8.2301 12.7223 8.61429 12.4678 9.03227 12.2978C9.02528 12.2055 9.02172 12.1123 9.02172 12.0182C9.02229 11.0617 9.39838 10.1446 10.0672 9.46862C10.7361 8.79266 11.6429 8.41324 12.5883 8.41382C13.7992 8.41531 14.8683 9.02804 15.5108 9.96325C15.816 9.85386 16.1444 9.79441 16.4866 9.79459C17.9982 9.79643 19.2397 10.9624 19.3836 12.4534C20.832 12.7729 21.9159 14.0785 21.9146 15.6395C21.9131 17.4385 20.4711 18.8958 18.6932 18.895C18.6309 18.895 18.569 18.8932 18.5075 18.8896Z" fill-rule="evenodd" clip-rule="evenodd"/>
					<path fill="#FFFFFF" class="landing-ui-panel-top-pub-btn-icon-defs-success" d="M7.46967 13.782L9.1093 12.14L12.2726 15.2532L18.6078 8.91091L20.2474 10.5529L12.2881 18.5218L7.46967 13.782Z" fill-rule="evenodd" clip-rule="evenodd"/>
					<path fill="#FF7975" class="landing-ui-panel-top-pub-btn-icon-defs-error"  d="M19.8991 9.8334L17.607 7.54126L7.00036 18.1479L9.2925 20.44L19.8991 9.8334Z"/>
					<path fill="#FFFFFF" class="landing-ui-panel-top-pub-btn-icon-defs-error"  d="M19.9323 10.0725C20.2657 9.73913 20.2657 9.19867 19.9323 8.86532C19.599 8.53198 19.0585 8.53198 18.7252 8.86532L8.6579 18.9326C8.32455 19.266 8.32455 19.8064 8.6579 20.1398C8.99124 20.4731 9.5317 20.4731 9.86505 20.1398L19.9323 10.0725Z"/>
					<path fill="#2FC6F6" class="landing-ui-panel-top-pub-btn-icon-defs-loader" d="M14 7C14.0668 7 14.1335 7.00094 14.1998 7.0028L14.1998 8.85103C14.1335 8.8485 14.0669 8.84723 14 8.84723C11.1542 8.84723 8.84723 11.1542 8.84723 14C8.84723 16.8458 11.1542 19.1528 14 19.1528C16.8458 19.1528 19.1528 16.8458 19.1528 14L19.1478 13.7993H20.9968L21 14C21 17.7871 17.9926 20.8718 14.2358 20.9961L14 21C10.134 21 7 17.866 7 14C7 10.134 10.134 7 14 7Z" />
				</svg>
			</button>
			<?
			if ($arResult['FAKE_PUBLICATION']):
				?><div id="landing-popup-publication-error-area" style="display: none;"></div><?
			else:
				$errorCode = array_key_first($arResult['ERRORS']);
				$errDesc = $arResult['ERRORS'][$errorCode];
				if ($errorCode === 'PUBLIC_SITE_REACHED_FREE')
				{
					$errTitle = $arResult['ERRORS'][$errorCode];
					$errDesc = null;
				}
				?>
				<div id="landing-popup-publication-error-area"
					style="display: none;"
					data-error="<?=$errorCode?>"
					<?=($errTitle ? " data-error-title=\"{$errTitle}\"" : '')?>
					<?=($errDesc ? " data-error-description=\"{$errDesc}\"" : '')?>
				>
				</div><?
			endif;
		endif;
		// endregion

		?><div style="flex:1"></div>

		<!-- region History-->
		<div class="landing-ui-panel-top-history">
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-undo landing-ui-disabled"></span>
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-redo landing-ui-disabled"></span>
		</div>
		<!-- endregion -->

		<div class="landing-ui-panel-top-menu" id="landing-panel-settings">
			<?if ($arParams['DRAFT_MODE'] != 'Y'):?>
			<?if ($formEditor):?>
				<span class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link landing-btn-menu landing-ui-panel-top-menu-link-settings"><?=
					Loc::getMessage('LANDING_FORM_EDITOR_TOP_PANEL_SETTINGS');
				?></span>
			<?endif;?>
			<a href="<?= $urls['preview']->getUri();?>" <?
				?>id="landing-popup-preview-btn" <?
				?>data-domain="<?= $site['DOMAIN_NAME']?>" <?
				?>class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link landing-btn-menu">
				<?= Loc::getMessage('LANDING_TPL_PREVIEW_URL_OPEN');?>
			</a>

				<?if (!$formEditor):?>
					<input type="button" id="landing-popup-features-btn"<?
						?>class="ui-btn ui-btn-light-border ui-btn-round landing-ui-panel-top-menu-link-features" <?
						?><?if ($formCode){?> data-feedback="landing-feedback-<?= $formCode?>-button"<?}?><?
						?> value="<?= $component->getMessageType('LANDING_TPL_FEATURES')?>"<?
						?> />
				<?else:?>
					<span class="ui-btn ui-btn-light-border ui-btn-round ui-btn-icon-share landing-form-editor-share-button"><?
						echo Loc::getMessage('LANDING_FORM_EDITOR_SHARE_BUTTON')
					?></span>
				<?endif;?>
			<?else:?>
				<div id="landing-panel-settings-kb"></div>
			<?endif;?>
		</div>
	</div>
	<div class="landing-ui-view-container">
<?endif;?>

<script type="text/javascript">
	var landingParams = <?= \CUtil::phpToJSObject($arParams);?>;
	var landingSiteType = '<?= $arParams['TYPE'];?>';
	BX.ready(function()
	{
		BX.UI.Hint.init(document.querySelector('.landing-ui-panel'));

		BX.message({
			LANDING_SITE_TYPE: '<?= $arParams['TYPE'];?>',
			LANDING_PUBLIC_PAGE_REACHED: '<?= \CUtil::jsEscape(\Bitrix\Landing\Restriction\Manager::getSystemErrorMessage('limit_sites_number_page'));?>',
			LANDING_TPL_SETTINGS_SITE_URL: '<?= \CUtil::jsEscape($component->getMessageType('LANDING_TPL_SETTINGS_SITE_URL'));?>',
			LANDING_TPL_SETTINGS_SITE_DIZ_URL: '<?= \CUtil::jsEscape($component->getMessageType('LANDING_TPL_SETTINGS_SITE_DIZ_URL'));?>',
			LANDING_TPL_SETTINGS_CATALOG_URL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SETTINGS_CATALOG_URL'));?>',
			LANDING_TPL_SETTINGS_PAGE_URL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SETTINGS_PAGE_URL'));?>',
			LANDING_TPL_SETTINGS_PAGE_DIZ_URL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SETTINGS_PAGE_DIZ_URL'));?>',
			LANDING_PREVIEW_MOBILE_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MOBILE_TITLE'));?>',
			LANDING_PREVIEW_MOBILE_TEXT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MOBILE_TEXT'));?>',
			LANDING_PREVIEW_MOBILE_NEW_TAB: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MOBILE_NEW_TAB'));?>',
			LANDING_PREVIEW_MOBILE_COPY_LINK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PREVIEW_MOBILE_COPY_LINK'));?>',
			LANDING_PUBLICATION_SUBMIT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_SUBMIT'));?>',
			LANDING_PUBLICATION_AUTO: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_AUTO'));?>',
			LANDING_PUBLICATION_AUTO_OFF: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_AUTO_OFF'));?>',
			LANDING_PUBLICATION_AUTO_TOGGLE_ON: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_AUTO_TOGGLE_ON'));?>',
			LANDING_PUBLICATION_AUTO_TOGGLE_OFF: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_AUTO_TOGGLE_OFF'));?>',
			LANDING_TPL_FEATURES_FORMS_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_FEATURES_FORMS_TITLE'));?>',
			LANDING_TPL_FEATURES_FORMS_PROMO_LINK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_FEATURES_FORMS_PROMO_LINK'));?>',
			LANDING_TPL_FEATURES_SETTINGS: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_FEATURES_SETTINGS'));?>',
			LANDING_TPL_FEATURES_OL_TITLE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_FEATURES_OL_TITLE'));?>',
			LANDING_TPL_FEATURES_OL_PROMO_LINK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_FEATURES_OL_PROMO_LINK'));?>',
			LANDING_TPL_FEATURES_HELP_TITLE: '<?= \CUtil::jsEscape($component->getMessageType('LANDING_TPL_FEATURES_HELP_TITLE'));?>',
			LANDING_TPL_FEATURES_HELP_PROMO_LINK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_FEATURES_HELP_PROMO_LINK'));?>',
			LANDING_PAGE_STATUS_PUBLIC: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PAGE_STATUS_PUBLIC'));?>',
			LANDING_PAGE_STATUS_PUBLIC_NOW: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PAGE_STATUS_PUBLIC_NOW'));?>',
			LANDING_PAGE_STATUS_UPDATED_ORIG: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PAGE_STATUS_UPDATED_ORIG'));?>',
			LANDING_PAGE_STATUS_UPDATED_NOW_ORIG: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PAGE_STATUS_UPDATED_NOW_ORIG'));?>',
			LANDING_PUBLICATION_BUY_RENEW: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_BUY_RENEW'));?>',
			LANDING_PUBLICATION_CONFIRM_EMAIL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_CONFIRM_EMAIL'));?>',
			LANDING_PUBLICATION_GOTO_BLOCK: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_PUBLICATION_GOTO_BLOCK'));?>',
			LANDING_SITE_TILE_POPUP_COPY_LINK_COMPLETE: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_SITE_TILE_POPUP_COPY_LINK_COMPLETE'));?>',
			LANDING_TPL_PREVIEW_URL: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_PREVIEW_URL'));?>',
			LANDING_TPL_PREVIEW_URL_HINT: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_PREVIEW_URL_HINT'));?>',
			LANDING_PAR_PAGE_URL_SITE_EDIT: '<?= \CUtil::jsEscape($arParams['PAGE_URL_SITE_EDIT']);?>',
		});
	});
</script>

<?
// editor frame
if ($request->offsetExists('landing_mode'))
{
	if (SITE_TEMPLATE_ID == 'bitrix24')
	{
		Manager::getCacheManager()->clean('b_site_template');
		$component->refresh();
	}
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
	<style>
		.bx-session-message {
			display: none;
		}
	</style>
	<script type="text/javascript">
		BX.ready(function()
		{
			<?if ($arParams['DRAFT_MODE'] != 'Y'):?>
			new BX.Landing.Component.View.AutoPublication({
				pageIsUnActive: <?= $arResult['LANDING']->isActive() ? 'false' : 'true';?>
			});
			<?endif;?>
			BX.Landing.Component.View.create(
				<?= \CUtil::phpToJSObject($arResult['TOP_PANEL_CONFIG']);?>
			);
		});
	</script>
	<?php if ($request->get('forceLoad') == 'true'):?>
		<script type="text/javascript">
			BX.namespace('BX.Landing');
			BX.Landing.Block = function(element) {
				BX.onCustomEvent(window, 'BX.Landing.Block:init', [
					new BX.Landing.Event.Block({
						block: element,
						node: null,
						card: null,
						data: {},
						onForceInit: function() {}
					})
				]);
			};
			BX.Landing.Main = function() {};
			BX.Landing.Main.createInstance = function() {};
		</script>
	<?php endif;?>

	<?php if ($request->get('IS_AJAX') != 'Y'):?>
	<script>
		top.BX.addCustomEvent(
			'BX.Rest.Configuration.Install:onFinish',
			function(event)
			{
				if (!!event.data.elementList && event.data.elementList.length > 0)
				{
					var gotoSiteButton = null;
					for (var i = 0; i < event.data.elementList.length; i++)
					{
						gotoSiteButton = event.data.elementList[i];
						var replaces = [];
						var landingPath = '<?= CUtil::jsEscape($arParams['SEF']['landing_view']);?>';

						if (gotoSiteButton.dataset.siteId)
						{
							replaces.push([/#site_show#/, gotoSiteButton.dataset.siteId]);
						}
						if (gotoSiteButton.dataset.isLanding === 'Y' && gotoSiteButton.dataset.landingId)
						{
							replaces.push([/#landing_edit#/, gotoSiteButton.dataset.landingId]);
						}

						if (gotoSiteButton.getAttribute('href').substr(0, 1) === '#')
						{
							replaces.forEach(function(replace) {
								landingPath = landingPath.replace(replace[0], replace[1]);
							});
							gotoSiteButton.setAttribute('href', landingPath);
							top.window.location.href = landingPath;
						}
					}
				}
			}
		);
	</script>
	<?php endif?>

	<?php
}
// top panel
else
{
	// exec theme-hooks for design panel
	$hooksLanding = \Bitrix\Landing\Hook::getForLanding($arResult['LANDING']->getId());
	$hooksSite = \Bitrix\Landing\Hook::getForSite($arResult['LANDING']->getSiteId());
	if (isset($hooksLanding['THEME']) && $hooksLanding['THEME']->enabled())
	{
		$hooksLanding['THEME']->exec();
	}
	elseif (isset($hooksSite['THEME']) && $hooksSite['THEME']->enabled())
	{
		$hooksSite['THEME']->exec();
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
	<style>
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
