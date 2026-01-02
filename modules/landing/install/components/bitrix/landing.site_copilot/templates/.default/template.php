<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var array $arResult */
/** @var string $templateFolder */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Copilot;

Extension::load([
	'landing_master',
	'landing.ui.button.basebutton',
	'landing.copilot.chat',
	'landing.copilot.generation-observer',
	'ui.icon-set.actions',
	'ui.design-tokens',
	'intranet.sidepanel.bindings',
]);

$allMess = Loc::loadLanguageFile(__FILE__);
$keysForMessJs = [
	'LANDING_SITE_COPILOT_CREATED_1',
	'LANDING_SITE_COPILOT_CREATED_2',
	'LANDING_SITE_COPILOT_CREATED_3',
	'LANDING_SITE_COPILOT_CREATED_4',
	'LANDING_SITE_COPILOT_CREATED_5',
	'LANDING_SITE_COPILOT_CREATED_6',
	'LANDING_SITE_COPILOT_CREATED_7',
	'LANDING_SITE_COPILOT_CREATED_8',
	'LANDING_SITE_COPILOT_CREATED_9',
	'LANDING_SITE_COPILOT_CREATED_10',
];
$setMessForJS = array_reduce($keysForMessJs, static function($carry, $key) use ($allMess) {
	$carry[$key] = $allMess[$key];
	return $carry;
}, []);

Manager::setPageTitle(Loc::getMessage('LANDING_SITE_PAGE_TITLE'));

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'landing-site-copilot-body');

$pathTemplate24 = getLocalPath('templates/' . Manager::getTemplateId(Manager::getMainSiteId()));

$asset = Asset::getInstance();
$cssFiles = [
	$pathTemplate24 . '/theme.css',
	$pathTemplate24 . '/assets/vendor/bootstrap/bootstrap.css',
	'/bitrix/components/bitrix/landing.landing_view/templates/.default/style.css',
	'/bitrix/components/bitrix/landing.selector/templates/.default/style.css',
];

foreach ($cssFiles as $cssFile) {
	$asset->addCSS($cssFile);
}

$previewImagePath = 'background-image: url(' . $templateFolder . '/image/landing-site-ai-icon.svg);';
$helperFrameOpenUrl = $arResult['HELPER_FRAME_OPEN_URL'] ?? null;
?>

<div class="landing-ui-panel landing-ui-panel-top">
	<div class="landing-ui-panel-top-logo">
		<a href="/sites/" class="landing-ui-panel-top-logo-link" data-slider-ignore-autobinding="true" target="_top">
			<span class="landing-ui-panel-top-logo-home-btn" data-hint="Выйти из редактора" data-hint-no-icon="" data-hint-init="y">
				<svg class="landing-ui-panel-top-logo-home-btn-icon" width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M11.902 19.6877V15.8046C11.902 15.5837 12.0811 15.4046 12.302 15.4046H14.5087C14.7296 15.4046 14.9087 15.5837 14.9087 15.8046V19.6877C14.9089 19.9086 15.0879 20.0876 15.3087 20.0878L18.8299 20.0891C19.0508 20.0893 19.2299 19.9103 19.23 19.6894C19.23 19.6893 19.23 19.6893 19.2299 19.6892V13.4563C19.2299 13.4365 19.2275 13.4142 19.2275 13.3943H20.4332C20.6633 13.3943 20.8604 13.2883 20.9909 13.0932C21.1189 12.9005 21.1425 12.6747 21.0581 12.4561C20.9519 12.1816 14.2383 5.92948 14.2047 5.90379C13.7957 5.59077 13.3216 5.58796 12.9131 5.89536C12.8759 5.92337 6.15525 12.1815 6.04901 12.4561C5.96462 12.6729 5.99059 12.9011 6.11629 13.0932C6.24671 13.2859 6.44145 13.3943 6.67162 13.3943H7.87965C7.87729 13.4142 7.87729 13.4365 7.87729 13.4563V19.6846C7.8776 19.9054 8.0565 20.0844 8.27729 20.0849L11.502 20.0874C11.7229 20.0879 11.9021 19.9089 11.9023 19.688C11.9023 19.6879 11.9023 19.6878 11.902 19.6877Z" fill="#525C69"></path>
				</svg>
			</span>
			<span class="landing-ui-panel-top-logo-text">
				<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_LOGO') ?>
			</span>
			<span class="landing-ui-panel-top-logo-color">
				<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_LOGO_24') ?>
			</span>
			<span class="landing-ui-panel-top-logo-icon far fa-clock-three"></span>
			<span class="landing-ui-panel-top-logo-text left-spaced">
				<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_LOGO_SITES_MSGVER_1') ?>
			</span>
		</a>
	</div>
	<div class="landing-ui-panel-disabled">
		<div class="landing-ui-panel-top-selector">
			<label class="landing-selector-container" id="landing-selector">
				<span class="landing-selector-result-picture" id="landing-selector-picture" style="<?= $previewImagePath ?>"></span>
				<input
					class="landing-selector-input-text"
					id="landing-selector-input"
					value="<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_SITE_NAME') ?>">
			</label>
		</div>
		<button class="landing-ui-panel-top-pub-btn landing-ui-panel-top-pub-btn-auto landing-ui-panel-top-pub-btn-enable" id="landing-popup-publication-btn" data-hint="Параметры автопубликации" data-hint-no-icon="" data-hint-init="y">
			<svg class="landing-ui-panel-top-pub-btn-icon" width="25" height="25" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill="#C6CDD3" class="landing-ui-panel-top-pub-btn-icon-defs-cloud" d="M18.5075 18.8896H10.4177C10.3485 18.8896 10.2799 18.887 10.2119 18.882C8.38363 18.8398 6.91434 17.3271 6.91434 15.4671C6.91487 14.5606 7.27128 13.6914 7.90517 13.0507C8.2301 12.7223 8.61429 12.4678 9.03227 12.2978C9.02528 12.2055 9.02172 12.1123 9.02172 12.0182C9.02229 11.0617 9.39838 10.1446 10.0672 9.46862C10.7361 8.79266 11.6429 8.41324 12.5883 8.41382C13.7992 8.41531 14.8683 9.02804 15.5108 9.96325C15.816 9.85386 16.1444 9.79441 16.4866 9.79459C17.9982 9.79643 19.2397 10.9624 19.3836 12.4534C20.832 12.7729 21.9159 14.0785 21.9146 15.6395C21.9131 17.4385 20.4711 18.8958 18.6932 18.895C18.6309 18.895 18.569 18.8932 18.5075 18.8896Z" fill-rule="evenodd" clip-rule="evenodd"></path>
			</svg>
		</button>
		<div class="landing-ui-panel-top-devices">
			<div class="landing-ui-panel-top-devices-inner">
				<button class="landing-ui-button landing-ui-button-desktop active" data-id="desktop_button"></button>
				<button class="landing-ui-button landing-ui-button-tablet active" data-id="tablet_button"></button>
				<button class="landing-ui-button landing-ui-button-mobile active" data-id="mobile_button">
					<span class="landing-ui-button-label">
						<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_LABEL_TEXT') ?>
					</span>
				</button>
			</div>
		</div>
		<div class="landing-ui-panel-top-history">
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-undo"></span>
			<span class="landing-ui-panel-top-history-button landing-ui-panel-top-history-redo"></span>
		</div>
		<div class="landing-ui-panel-top-menu" id="landing-panel-settings">
			<div id="landing-popup-preview-btn" class="ui-btn ui-btn-light-border landing-ui-panel-top-menu-link landing-btn-menu">
				<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_PREVIEW_BTN_TEXT') ?>
			</div>
			<input
				class="ui-btn ui-btn-light-border ui-btn-round landing-ui-panel-top-menu-link-features"
				id="landing-popup-features-btn"
				type="button"
				value="<?= Loc::getMessage('LANDING_CMP_TOP_PANEL_FEATURES_BTN_TEXT') ?>">
		</div>
	</div>
</div>

<div id="landing-site-copilot-status" class="landing-site-copilot-status"></div>

<script>
	BX.message(<?= Json::encode($setMessForJS)?>);
	const template = new BX.Landing.SiteCopilotTemplateCreator({
		container: BX('landing-site-copilot-status'),
		rootPath: '<?= $templateFolder ?>',
		helperFrameOpenUrl: '<?= $helperFrameOpenUrl ?>',
	});
	template.init();

	BX.ready(() => {
		const agreementPopup = BX('landing-agreement-popup');
		if (agreementPopup && BX.hasClass(agreementPopup, "--open"))
		{
			return;
		}

		const chat = BX.Landing.Copilot.Chat.getCopilotChatInstance({
			entityId: '<?= Copilot\Connector\Chat\Chat::createChatEntityId() ?>',
			scenario: 'site_with_ai',
		});
		if (!chat)
		{
			return;
		}
		setTimeout(() => {
			chat.show();
		}, 500);

		let isPreviewImageCreated = false;
		let isGenerationStarted = false;
		let isBlocksCreated = false;
		let siteId = null;
		let landingId = null;
		let generationId = null;

		BX.PULL.subscribe({
			type: 'server',
			moduleId: 'landing',
			callback: (eventData) => {
				if (
					eventData.params.generationId !== undefined
					&& generationId !== null
					&& eventData.params.generationId !== generationId
				)
				{
					return;
				}

				if (eventData.command === 'LandingCopilotGeneration:onGenerationCreate')
				{
					generationId = eventData.params.generationId;

					const observer = new BX.Landing.Copilot.GenerationObserver(generationId);
					observer.observe();
				}

				if (eventData.command === 'LandingCopilotGeneration:onGenerationError')
				{
					stopSiteGenerating();
				}

				if (eventData.command === 'LandingCopilotGeneration:onPreviewImageCreate')
				{
					isPreviewImageCreated = true;
					if (isBlocksCreated && siteId && landingId)
					{
						redirectToEditor(siteId, landingId, generationId);
					}
				}

				if (eventData.command === 'LandingCopilotGeneration:onBlocksCreated')
				{
					if (eventData.params.siteId && eventData.params.landingId && eventData.params.generationId)
					{
						isBlocksCreated = true;
						siteId = eventData.params['siteId'];
						landingId = eventData.params.landingId;

						if (isPreviewImageCreated)
						{
							redirectToEditor(siteId, landingId, generationId);
						}
					}
				}

				if (eventData.command === 'LandingCopilotChatbot:onStartGeneration')
				{
					startSiteGenerating();
				}

				if (eventData.command === 'LandingCopilotChatbot:onRestartGeneration')
				{
					startSiteGenerating();
				}

				if (eventData.command === 'LandingCopilotChatbot:onStartOver')
				{
					setTimeout(() => {
						window.location.reload();
					}, 3000);
				}
			},
		});

		const redirectToEditor = (siteId, landingId, generationId) => {
			if (
				siteId > 0
				&& landingId > 0
				&& generationId > 0
			)
			{
				// todo: change hardcoded path, get url from component params
				window.location.href = `/sites/site/${siteId}/view/${landingId}/?site_generated=${generationId}`;
			}
		}

		const startSiteGenerating = () => {
			template.setStatusCreate();
			isGenerationStarted = true;
			setTimeout(() => {
				if (isGenerationStarted === true)
				{
					chat.hide();
				}
			}, 3000);
		}

		const stopSiteGenerating = () => {
			template.setStatusStart();
			isGenerationStarted = false;
			chat.show();
		}
	});
</script>
