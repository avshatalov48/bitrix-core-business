<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \LandingPubComponent $component */
/** @var array $arResult */
/** @var array $arParams */

use \Bitrix\Landing\Config;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Landing\View;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Assets;
use \Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

$this->setFrameMode(true);
$landing = $arResult['LANDING'];/** @var \Bitrix\Landing\Landing $landing */
$b24Installed = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24');
$formEditor = $arResult['SPECIAL_TYPE'] == \Bitrix\Landing\Site\Type::PSEUDO_SCOPE_CODE_FORMS;
$masterFrame = $component->request('master') == 'Y' && Rights::hasAccessForSite(
	$landing->getSiteId(), Rights::ACCESS_TYPES['edit']
);

Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);

if ($arResult['ERRORS'])
{
	include 'error.php';
	return;
}

// load extensions
$extensions = ['ui.fonts.opensans'];
if ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP')
{
	$extensions[] = 'ui.entity-selector';
}
if (
	$arParams['SHOW_EDIT_PANEL'] == 'Y' ||
	!$landing->getDomainId()// wiki mode
)
{
	$extensions[] = 'landing.wiki.public';
	$extensions[] = 'ui.viewer';
}
if ($b24Installed)
{
	$extensions[] = 'landing.metrika';
}
$extensions[] = 'sidepanel';
$extensions[] = 'ui.hint';

Extension::load($extensions);

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

// edit menu
if ($arParams['SHOW_EDIT_PANEL'] === 'Y')
{
	Extension::load([
		'ui.buttons',
		'ui.buttons.icons',
		'ui.hint'
	]);
	ob_start(function($content)
	{
		Manager::setPageView('AfterBodyOpen',$content);
	});
	$allMess = Loc::loadLanguageFile(__FILE__);
	$setMessForJS = [];
	$setMessForJS['LANDING_TPL_PUB_COPIED_LINK'] = $allMess['LANDING_TPL_PUB_COPIED_LINK'];
	?>
	<div class="landing-pub-top-panel-wrapper">
		<div class="landing-pub-top-panel">
			<div class="landing-pub-top-panel-left">
				<div class="landing-pub-top-panel-nav-buttons">
					<button class="landing-pub-top-panel-back ui-btn ui-btn-xs ui-btn-icon-back ui-btn-link ui-btn-light"></button>
					<button class="landing-pub-top-panel-forward ui-btn ui-btn-xs ui-btn-icon-back ui-btn-link ui-btn-light"></button>
				</div>
				<div class="landing-pub-top-panel-separator"></div>
				<div class="landing-pub-top-panel-chain">
					<?php $title = $component->getMessageType('LANDING_TPL_SITES');?>
					<span class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-pub-top-panel-chain-link" style="pointer-events: none" title="<?= $title?>">
						<?= $title?>
					</span>
					<strong class="landing-pub-top-panel-chain-separator"><span></span></strong>
					<?php $title = \htmlspecialcharsbx($landing->getTitle());?>
					<span class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-pub-top-panel-chain-link landing-pub-top-panel-chain-link-page"" data-hint="<?= $title?>" data-hint-no-icon>
						<?= $title?>
					</span>
				</div>
				<div class="landing-pub-top-panel-page-link">
					<span class="landing-page-link-btn"></span>
				</div>
			</div>
			<div class="landing-pub-top-panel-right">
				<div class="landing-pub-top-panel-unique-view">
					<div class="ui-btn ui-btn-xs ui-btn-icon-eye-opened ui-btn-link ui-btn-light">
						<?= View::getNumberUniqueViews($landing->getId())?>
					</div>
					<div class="landing-pub-top-panel-unique-view-popup hide">
						<div class="landing-pub-top-panel-unique-view-popup-header">
							<?= $component->getMessageType('LANDING_TPL_VIEWS')?>
						</div>
						<div class="landing-pub-top-panel-unique-view-popup-item-container"></div>
					</div>
				</div>
				<?php if($arResult['CAN_EDIT'] === 'Y'): ?>
					<div class="landing-pub-top-panel-actions">
						<a href="<?= $arParams['PAGE_URL_LANDING_VIEW'];?>" data-landingId="<?= $landing->getId();?>" class="ui-btn ui-btn-primary ui-btn-icon-edit landing-pub-top-panel-edit-button">
							<?= $component->getMessageType('LANDING_TPL_EDIT_PAGE');?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<script>
			BX.message(<?= \CUtil::PhpToJSObject($setMessForJS)?>);
			var userData = <?= \CUtil::PhpToJSObject(View::getUniqueUserData($landing->getId()))?>;
			var data = [];
			data.userData = userData;
			BX.ready(function() {
				void new BX.Landing.Pub.TopPanel(data);
			});
		</script>
	</div>
	<?php
	ob_end_flush();
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


if ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP')
{
	?>
	<script>
		BX.ready(function() {
			void new BX.Landing.Pub.DiskFile();
		});
	</script>
	<?php
}

// landing view
$landing->view([
	'check_permissions' => false
]);

// available view
$check = \Bitrix\Landing\Restriction\Manager::isAllowed(
	'limit_knowledge_base_number_page_view',
	['ID' => $landing->getSiteId()]
);
if (!$check)
{
	?>
	<script>
		BX.ready(function()
		{
			document.body.style.opacity = 0.1;
			document.addEventListener('click', function handler(e) {
				e.stopPropagation();
				e.preventDefault();
			}, true);
			top.BX.UI.InfoHelper.show('limit_knowledge_base_number_page_view');
		});
	</script>
	<?php
}

// assets
$assets = Assets\Manager::getInstance();
$assets->addAsset(
		'landing_public',
		Assets\Location::LOCATION_AFTER_TEMPLATE
);
$viewMode = $component->isPreviewMode() ? 'preview' : 'view';
$publicModeInit = '
	BX.namespace("BX.Landing");
	BX.Landing.getMode = () => "' . $viewMode . '";
';
$assets->addString(
	"<script>{$publicModeInit}</script>",
);
$assets->addAsset(
	Config::get('js_core_public'),
	Assets\Location::LOCATION_KERNEL
);
$assets->addAsset('landing_critical_grid', Assets\Location::LOCATION_BEFORE_ALL);
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
<?php endif;?>

<?php if ($b24Installed):?>
<script>
	(function()
	{
		new BX.Landing.Metrika();
	})();
</script>
<?php endif;?>

<script>
	BX.ready(function() {
		void new BX.Landing.Pub.PageTransition();
		BX.UI.Hint.init(BX('bitrix-footer-terms'));
	});
</script>

<?php
$hooksSite = Hook::getForSite($landing->getSiteId());
if (!$masterFrame && !$formEditor && isset($hooksSite['COPYRIGHT']))
{
	$lang = $landing->getMeta()['SITE_LANG'];
	$hooksSite['COPYRIGHT']->setLang($lang);
	$hooksSite['COPYRIGHT']->setSiteId($landing->getSiteId());
	Manager::setPageView('BeforeBodyClose', $hooksSite['COPYRIGHT']->view());
}
?>
