<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \LandingDesignBlockComponent $component */
/** @var \Bitrix\Landing\Landing $landing */
/** @var \CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\UI\Extension;
use \Bitrix\Landing\Config;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Assets;

Loc::loadMessages(__FILE__);
Loc::loadMessages(Manager::getDocRoot() . '/bitrix/modules/landing/lib/mutator.php');

$frameUri = $component->getUri([
	'design_block' => $arParams['BLOCK_ID'],
	'landing_mode' => 'edit'
]);

// assets, extensions
Extension::load([
	'ui.buttons',
	'ui.buttons.icons',
	'ui.fonts.opensans',
	'landing_master',
	'ui.alerts',
	'sidepanel'
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
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.landing_view/templates/.default/style.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.landing_view/templates/.default/script.js');

$blockManifest = $arResult['BLOCK_MANIFEST'];
$landing = $arResult['LANDING'];

// errors output
if ($arResult['ERRORS'])
{
	$errors = $arResult['ERRORS'];
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
				landingAlertMessage(
					'<?= \CUtil::jsEscape($errorMessage);?>',
					<?= $component->isTariffError($errorCode) ? 'true' : 'false';?>,
					'<?= $errorCode;?>'
				);
			});
		</script>
		<?
		break;
	}
}

if ($arResult['FATAL'])
{
	return;
}

// top panel
if (!$component->request('landing_mode')):
	$helpUrl = \Bitrix\Landing\Help::getHelpUrl('DESIGN_BLOCK');
	$b24Title = \Bitrix\Main\Config\Option::get('bitrix24', 'site_title', '');
	$b24Logo = \Bitrix\Main\Config\Option::get('bitrix24', 'logo24show', 'Y');
	if (!$b24Title)
	{
		$b24Title = Loc::getMessage(
			'LANDING_TPL_START_PAGE_LOGO' . (!Manager::isB24() ? '_SMN' : '')
		);
	}
	?>
	<div class="landing-ui-panel landing-ui-panel-top">
		<div class="landing-ui-panel-top-logo">
			<a href="<?= SITE_DIR;?>" data-slider-ignore-autobinding="true"><?
				?><span class="landing-ui-panel-top-logo-text"><?= \htmlspecialcharsbx($b24Title);?></span><?
				if ($b24Logo != 'N'):
					?><span class="landing-ui-panel-top-logo-color"><?= Loc::getMessage('LANDING_TPL_START_PAGE_LOGO_24');?></span><?
				endif;?>
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
			<div style="display: none">
				<?$APPLICATION->includeComponent(
					'bitrix:ui.feedback.form',
					'',
					$component->getFeedbackParameters('designblock', [
						'siteUrl' => $landing->getPublicUrl(),
						'blockName' => $blockManifest['block']['name'] ?? null
					])
				);?>
			</div>
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-undo landing-ui-disabled"></span>
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-redo landing-ui-disabled"></span>
			<span onclick="BX.fireEvent(BX('landing-feedback-designblock-button'), 'click');" class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link landing-btn-menu">
				<?= Loc::getMessage('LANDING_TPL_FEEDBACK');?>
			</span>
			<span id="landing-design-block-close" class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link landing-btn-menu">
				<?= Loc::getMessage('LANDING_TPL_CLOSE');?>
			</span>
			<span id="landing-design-block-save" class="ui-btn ui-btn-primary">
				<?= Loc::getMessage('LANDING_TPL_SAVE');?>
			</span>
			<?if ($helpUrl):?>
			<a href="<?= $helpUrl;?>" class="ui-btn ui-btn-light ui-btn-round landing-ui-panel-top-menu-link landing-ui-panel-top-menu-link-help" target="_blank">
				<span class="landing-ui-panel-top-menu-link-help-icon">?</span>
			</a>
			<?endif;?>
		</div>
	</div>
	<div class="landing-ui-view-container">
<?endif;?>

<?
// editor frame
if ($component->request('landing_mode'))
{
	Manager::setPageView('MainClass', 'landing-design-mode');
	$arResult['LANDING_ZERO']->view();
	$arResult['DESIGNER']->execHooks();
	?>
	<style>
		.bx-session-message {
			display: none;
		}
	</style>
	<?
}
// top panel
else
{
	// exec theme-hooks for design panel
	$hooksLanding = \Bitrix\Landing\Hook::getForLanding($arParams['LANDING_ID']);
	$hooksSite = \Bitrix\Landing\Hook::getForSite($arParams['SITE_ID']);
	if (isset($hooksLanding['THEME']) && $hooksLanding['THEME']->enabled())
	{
		$hooksLanding['THEME']->exec();
	}
	elseif (isset($hooksSite['THEME']) && $hooksSite['THEME']->enabled())
	{
		$hooksSite['THEME']->exec();
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
			BX.Landing.Component.View.loadEditor();
			BX.Landing.History.getInstance().removePageHistory(0);
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
				<iframe src="<?= $frameUri;?>" class="landing-ui-view" id="landing-view-frame" allowfullscreen></iframe>
			</div>
		</div>
	</div>
<?
}
