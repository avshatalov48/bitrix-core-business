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
$extensions = [];
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
	<?
}

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
		'ui.buttons.icons'
	]);
	ob_start(function($content)
	{
		Manager::setPageView('AfterBodyOpen',$content);
	});
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
					<?$title = $component->getMessageType('LANDING_TPL_SITES');?>
					<span class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-pub-top-panel-chain-link" style="pointer-events: none" title="<?= $title;?>">
						<?= $title;?>
					</span>
					<strong class="landing-pub-top-panel-chain-separator"><span></span></strong>
					<?$title = \htmlspecialcharsbx($landing->getTitle());?>
					<span class="ui-btn ui-btn-xs ui-btn-light ui-btn-round landing-pub-top-panel-chain-link" style="pointer-events: none" title="<?= $title;?>">
						<?= $title;?>
					</span>
				</div>
			</div>
			<?php if($arResult['CAN_EDIT'] === 'Y'): ?>
				<div class="landing-pub-top-panel-right">
					<div class="landing-pub-top-panel-actions">
						<a href="<?= $arParams['PAGE_URL_LANDING_VIEW'];?>" data-landingId="<?= $landing->getId();?>" class="ui-btn ui-btn-primary ui-btn-icon-edit landing-pub-top-panel-edit-button">
							<?= $component->getMessageType('LANDING_TPL_EDIT_PAGE');?>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<script>
			BX.ready(function() {
				void new BX.Landing.Pub.TopPanel();
			});
		</script>
	</div>
	<?
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
		<?
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
	<?
}


if ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP')
{
	?>
	<script>
		BX.ready(function() {
			void new BX.Landing.Pub.DiskFile();
		});
	</script>
	<?
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
	<?
}

// hook for copyrights
$enableHook = \Bitrix\Landing\Restriction\Manager::isAllowed(
	'limit_sites_powered_by'
);
if ($enableHook)
{
	$hooksSite = Hook::getForSite($landing->getSiteId());
}

// assets
$assets = Assets\Manager::getInstance();
$assets->addAsset(
		'landing_public',
		Assets\Location::LOCATION_AFTER_TEMPLATE
);
$assets->addAsset(
	Config::get('js_core_public'),
	Assets\Location::LOCATION_KERNEL
);
$assets->addAsset('landing_critical_grid', Assets\Location::LOCATION_BEFORE_ALL);
?>

<?if ($b24Installed):?>
<script>
	(function()
	{
		new BX.Landing.Metrika();
	})();
</script>
<?endif;?>

<?ob_start(); ?>
<?if (!$masterFrame && !$formEditor && (!$enableHook || isset($hooksSite['COPYRIGHT']) && $hooksSite['COPYRIGHT']->enabled())):?>
<div class="bitrix-footer">
	<?if (Manager::isB24()):?>
		<span class="bitrix-footer-text">
			<?
			$zone = Manager::getZone();
			$westCopy = !in_array($zone, ['ru', 'kz', 'by', 'ua']);
			$fullCopy = in_array($zone, array('ru', 'by'))
						? Loc::getMessage('LANDING_TPL_COPY_FULL')
						: Loc::getMessage('LANDING_TPL_COPY_FULL2');
			$logo = '<img src="' .
						$this->getFolder() . '/images/' .
						(in_array($zone, array('ru', 'ua', 'en')) ? $zone : 'en') .
						'.svg?1" alt="' . Loc::getMessage('LANDING_TPL_COPY_NAME') . '">';
			$rel = $westCopy ? ' rel="nofollow"' : '';
			if ($fullCopy)
			{
				if ($westCopy)
				{
					$fullCopy = preg_replace('#<linkcreate>[^<]+</linkcreate>#is', '', $fullCopy);
					$fullCopy = trim($fullCopy, ' .');
				}
				echo str_replace(
					[
						'#LOGO#',
						'<linklogo>', '</linklogo>',
						'<linksite>', '</linksite>',
						'<linkcrm>', '</linkcrm>',
						'<linkcreate>', '</linkcreate>'
					],
					[
						$logo,
						'<a' . $rel . ' target="_blank" href="' . $this->getComponent()->getRefLink('bitrix24_logo', true, $westCopy) . '">', '</a>',
						'<a class="bitrix-footer-link" target="_blank" href="' . $this->getComponent()->getRefLink('websites', true, $westCopy) . '">', '</a>',
						'<a' . $rel . ' class="bitrix-footer-link" target="_blank" href="' . $this->getComponent()->getRefLink('crm', true, $westCopy) . '">', '</a>',
						'<a class="bitrix-footer-link" target="_blank" href="' . $this->getComponent()->getRefLink('create', false) . '">', '</a>'
					],
					$fullCopy
				);
			}
			else
			{
				echo Loc::getMessage('LANDING_TPL_COPY_NAME_0') . ' ';
				echo $logo;
				echo ' &mdash; ';
				echo Loc::getMessage('LANDING_TPL_COPY_REVIEW');
			}
			?>
		</span>
		<?if (!$fullCopy && !$westCopy):?>
		<a class="bitrix-footer-link" target="_blank" href="<?= $this->getComponent()->getRefLink('create', false);?>">
			<?= Loc::getMessage('LANDING_TPL_COPY_LINK');?>
		</a>
		<?endif;?>
	<?else:?>
		<span class="bitrix-footer-text"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_0');?></span>
		<a href="https://www.1c-bitrix.ru/?<?= $arResult['ADV_CODE'];?>" target="_blank" class="bitrix-footer-link"><?= Loc::getMessage('LANDING_TPL_COPY_NAME_SMN_1');?></a>
	<?endif;?>
</div>
<?endif;?>
<?
$footer = ob_get_contents();
ob_end_clean();
Manager::setPageView('BeforeBodyClose', $footer);
?>
