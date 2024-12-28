<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \LandingMainpagePubComponent $component */
/** @var array $arResult */
/** @var array $arParams */

use Bitrix\Landing\Assets;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Site\Type;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Intranet;

Loc::loadMessages(__FILE__);

$this->setFrameMode(true);

// assets
$assets = Assets\Manager::getInstance();
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.mainpage.pub/templates/.default/style-widgets.css');

if (isset($arResult['LANDING']))
{
	/** @var \Bitrix\Landing\Landing $landing */
	$landing = $arResult['LANDING'];
	$b24Installed = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24');
	$masterFrame = $component->request('master') == 'Y' && Rights::hasAccessForSite(
		$landing->getSiteId(), Rights::ACCESS_TYPES['edit']
	);
}
else
{
	// todo: print error no landing
}

if ($arParams['TYPE'] !== Type::SCOPE_CODE_MAINPAGE)
{
	$arResult['ERRORS'][] = 'Incorrect type';
}
?>

<?php
if (Loader::includeModule('bitrix24'))
{
	$available = Bitrix\Bitrix24\Feature::isFeatureEnabled("main_page");
}
else
{
	// do not change this condition! Need for preview.bitrix24.site
	$available =
		class_exists('CBXFeatures')
		&& CBXFeatures::IsFeatureEnabled('main_page')
	;
}
if (!$available)
{
?>
	<div class="landing-mainpage-disabled-container">
		<div class="landing-mainpage-disabled-icon"></div>
		<div class="landing-mainpage-disabled-title">
			<?= Loc::getMessage('LANDING_TPL_MAINPAGE_DISABLED_TITLE'); ?>
		</div>
		<div class="landing-mainpage-disabled-text">
			<?= Loc::getMessage('LANDING_TPL_MAINPAGE_DISABLED_TEXT'); ?>
		</div>
	</div>
<?php
	return;
}
?>

<?php
Manager::setPageView(
	'BodyClass',
	'no-all-paddings landing-tile no-background'
);

if ($arResult['ERRORS'])
{
	include 'error.php';
	return;
}

if (Loader::includeModule('intranet'))
{
	$publisher = new Intranet\MainPage\Publisher();
	$isPublished =	$publisher->isPublished();
}
else
{
	$isPublished = false;
}

// load extensions
$extensions = [
	'sidepanel',
	'applayout',
	'landing.mainpage.public',
];
if (!$isPublished)
{
	$extensions[] = 'ui.alerts';
}
if ($b24Installed)
{
	$extensions[] = 'landing.metrika';
}

Extension::load($extensions);

$viewMode = 'view';
$publicModeInit = '
	BX.namespace("BX.Landing");
	BX.Landing.getMode = () => "' . $viewMode . '";
';
$assets->addString(
	"<script>{$publicModeInit}</script>",
);

// check frame parameter outside the frame
if ($component->request('IFRAME'))
{
	?>
	<script>
		(function()
		{
			if (top.window.location.href === window.location.href)
			{
				top.window.location.href = BX.Uri.removeParam(
					top.window.location.href,
					'IFRAME'
				);
			}
			else if (window.location.hash.indexOf('#landingId') === 0)
			{
				window.location.hash = '';
			}
		})();
	</script>
	<?php
}
?>

<?php if (!$isPublished):?>
	<div class="ui-alert ui-alert-warning ui-alert-icon-info ui-alert-close-animate">
		<span class="ui-alert-message"><?= Loc::getMessage('LANDING_TPL_MAINPAGE_ALERT_TEXT'); ?>
			<?php if (Manager::isAdmin()): ?>
				<?= Loc::getMessage('LANDING_TPL_MAINPAGE_ALERT_TEXT_ADMIN'); ?>
			<?php endif; ?>
		</span>
		<span class="ui-alert-close-btn" onclick="this.parentNode.style.display = 'none';"></span>
	</div>
<?php endif;?>

<?php
// shop master frame
if ($masterFrame)
{
	\Bitrix\Landing\Manager::setPageView(
		'BodyTag',
		'style="pointer-events: none; user-select: none;"'
	);
	echo '<style>.b24-widget-button-wrapper, .catalog-cart-block {display: none;}</style>';
}

if ($arResult['SEARCH_RESULT_QUERY'])
{
	if (!$component->isAjax())
	{
		?>
		<script>
			BX.ready(function() {
				void new BX.Landing.Pub.SearchResult();
			});
		</script>
		<?php
	}
}

if ($component->request('ts'))
{
	?>
	<script>
		BX.ready(function() {
			void new BX.Landing.Pub.TimeStamp();
		});
	</script>
	<?php
}

?>
<script>
	BX.ready(function() {
		void new BX.Landing.Mainpage.Public();
	});
</script>
<?php

// todo: after creating page from market - check TPL_ID. Need .landing-main wrapper or own container
// @see \Bitrix\Landing\Landing::applyTemplate

// landing view
$landing->view([
	'check_permissions' => false
]);

Manager::setPageTitle(Loc::getMessage('LANDING_TPL_MAINPAGE_TITLE'));

$viewMode = $component->isPreviewMode() ? 'preview' : 'view';
?>
<?php if ($viewMode === 'preview' && $component->request('scrollTo')):?>
<script>
	const scrollToElementId = '<?= CUtil::JSEscape(htmlspecialcharsbx($component->request('scrollTo')))?>';
	const scrollToElement = document.getElementById(scrollToElementId);

	if (scrollToElement)
	{
		scrollToElement.scrollIntoView();
	}
</script>
<?php elseif ($viewMode === 'preview'):?>
<style>
	[data-b24-crm-hello-cont] {
		display: none;
	}
</style>
<script>
	BX.ready(function() {
		void new BX.Landing.Pub.Analytics();
	});
</script>
<script>
	BX.ready(function() {
		void new BX.Landing.Pub.Pseudolinks();
	});
</script>
<?php endif;?>

<?php if ($b24Installed):?>
<script>
	(function()
	{
		new BX.Landing.Metrika();
	})();
</script>
<?php endif;?>

