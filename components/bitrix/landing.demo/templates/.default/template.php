<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var \LandingBaseComponent $component */
/** @var string $templateFolder */
/** @var \CMain $APPLICATION */

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.fonts.opensans',
	'sidepanel',
	'ui.design-tokens',
]);

Loc::loadMessages(__FILE__);

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// some errors
if ($arResult['ERRORS'])
{
	foreach ($arResult['ERRORS'] as $code => $error)
	{
		echo '<p style="color: red;">' . $error . '</p>';
	}
}

// show message for license renew if need
if (
	empty($arResult['DEMO'])
	&& !isset($arResult['ERRORS']['ACCESS_DENIED'])
	&& !$arResult['IS_SEARCH']
)
{
	if (ModuleManager::isModuleInstalled('bitrix24'))
	{
		\showError(Loc::getMessage('LANDING_TPL_EMPTY_REPO_SERVICE'));
	}
	else
	{
		if (Manager::licenseIsValid())
		{
			\showError(Loc::getMessage('LANDING_TPL_EMPTY_REPO_SERVICE'));
		}
		else
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
						<span class="landing-license-info-text"><?= Loc::getMessage('LANDING_TPL_EMPTY_REPO_EXPIRED');?></span>
						<div class="landing-license-info-btn">
							<?= Loc::getMessage('LANDING_TPL_EMPTY_REPO_EXPIRED_LINK', array(
								'#LINK1#' => '<a href="' . $link . '" target="_blank" class="landing-license-info-link">',
								'#LINK2#' => '</a>'
							));?>
						</div>
					</div>
				</div>
			</div>
			<?
		}
	}
}

// exit on fatal
if ($arResult['FATAL'])
{
	return;
}

// title
if (!$component->isAjax())
{
	$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
	$APPLICATION->SetPageProperty(
		'BodyClass',
		($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings no-background landing-slider-frame-popup'
	);
	\Bitrix\Landing\Manager::setPageTitle(
		$component->getMessageType('LANDING_TPL_TITLE')
	);

	// additional assets
	\CJSCore::Init(['popup', 'action_dialog', 'loader', 'sidepanel']);
	Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.sites/templates/.default/style.css');
	Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.sites/templates/.default/script.js');

	// filter
	if ($arParams['TYPE'] === 'PAGE')
	{
		ob_start();
		?>
		<div class="landing-filter-container">
			<?php
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.filter',
				'',
				[
					'FILTER_ID' => $arResult['FILTER_ID'],
					'FILTER' => $arResult['FILTER_FIELDS'],
					'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
					'ENABLE_LABEL' => true,
					'ENABLE_LIVE_SEARCH' => true,
				],
				$this->__component,
				['HIDE_ICONS' => true]
			);
			?>
			<script type="text/javascript">
				BX.Landing.Component.Demo.ajaxPath = '<?=\CUtil::jsEscape($arResult['FILTER_URI'])?>';
			</script>
		</div>
		<?php
		$filter = ob_get_contents();
		ob_end_clean();
		$APPLICATION->addViewContent('title_actions', $filter);
	}

	// create empty button
	if ($arParams['TYPE'] === 'KNOWLEDGE' || $arParams['TYPE'] === 'GROUP')
	{
		$emptyTpl = !$arParams['SITE_ID']
			? 'empty-multipage/main'
			: 'empty'
		;
		$emptyCreateUrl = $component->getUri(
			['tpl' => $emptyTpl],
			['select']
		);
		$createEmptyButton =
			'<div 
				id="landing-demo-empty"
				class="ui-btn ui-btn-md ui-btn-light-border landing-template-pseudo-link"
				data-href="' . $emptyCreateUrl . '"
			>'
			. Loc::getMessage("LANDING_TPL_CREATE_EMPTY")
			. '</div>';
		$APPLICATION->addViewContent('title_actions', $createEmptyButton);
	}
	?>
	<div style="display: none">
		<?$APPLICATION->includeComponent(
			'bitrix:ui.feedback.form',
			'',
			$component->getFeedbackParameters('demo')
		);?>
	</div>


	<?php
}
?>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">
		<?php if (
			$arParams['TYPE'] === 'PAGE'
			&& (count($arResult['DEMO']) > 0)
		): ?>
			<span class="landing-item landing-item-contact" onclick="BX.fireEvent(BX('landing-feedback-demo-button'), 'click');">
				<span class="landing-item-inner">
					<span class="landing-item-contact-title"><?= Loc::getMessage('LANDING_TPL_FEEDBACK_TITLE');?></span>
					<span class="landing-item-contact-icon"></span>
					<span class="landing-item-contact-desc"><?= Loc::getMessage('LANDING_TPL_FEEDBACK_MESSAGE_2');?></span>
					<span class="ui-btn ui-btn-sm ui-btn-round landing-item-contact-btn">
						<?= Loc::getMessage('LANDING_TPL_FEEDBACK_SEND') ?>
					</span>
				</span>
			</span>
		<?php elseif ($arResult['IS_SEARCH']): ?>
			<div class="landing-demo-not-found">
				<img
					class="landing-demo-not-found-img"
					src="<?= $templateFolder ?>/image/landing-search-icon.png"
					alt="<?= Loc::getMessage('LANDING_TPL_NOT_FOUND_TITLE') ?>"
				>
				<div class="landing-demo-not-found-title">
					<?= Loc::getMessage('LANDING_TPL_NOT_FOUND_TITLE') ?>
				</div>
				<div class="landing-demo-not-found-text">
					<?= Loc::getMessage('LANDING_TPL_FEEDBACK_MESSAGE_2') ?>
				</div>
				<span
					class="landing-demo-not-found-button ui-btn ui-btn-light-border"
					onclick="BX.fireEvent(BX('landing-feedback-demo-button'), 'click');"
				>
					<?= Loc::getMessage('LANDING_TPL_NOT_FOUND_BUTTON') ?>
				</span>
			</div>
		<?php endif; ?>
<?php foreach ($arResult['DEMO'] as $item): ?>
<?php
	// empty is in top button, not need show in list
	// skip chats
	if (
		$item['ID'] === 'empty'
		|| $item['ID'] === 'empty-multipage'
		|| $item['ID'] === 'store-chats-dark'
	)
	{
		continue;
	}
	// skip site group items
	if (
		isset($item['DATA']['site_group_item'])
		&& $item['DATA']['site_group_item'] === 'Y'
	)
	{
		continue;
	}

	$isSmnSite = defined('SMN_SITE_ID') || !$arParams['SITE_ID'];
	$tpl =
		($isSmnSite && isset($item['DATA']['items'][0]))
			? $item['DATA']['items'][0]
			: $item['ID']
	;

	if ($item['ID'] === 'store_v3')
	{
		$previewUrl = $component->getUri(['super' => 'Y']);
	}
	else if (!isset($item['EXTERNAL_URL']))
	{
		$previewUrl = $component->getUri(
			['tpl' => $tpl],
			['select']
		);
	}
	else
	{
		$previewUrl = $item['EXTERNAL_URL']['href'] ?? '';
	}
	?>
	<?if ($item['AVAILABLE']):?>
	<span data-href="<?= $previewUrl;?>"<?if ($item['ID'] === 'store_v3') {?> data-slider-width="1200"<?}?> id="landing-demo-<?= \htmlspecialcharsbx($tpl);?>" <?
		?>class="landing-template-pseudo-link landing-item landing-item-hover<?= ($arResult['LIMIT_REACHED'] && !$item['SINGLETON']) ? ' landing-item-payment' : '';?>" <?
		?><?if (isset($item['EXTERNAL_URL']['width'])){?>data-slider-width="<?= (int)$item['EXTERNAL_URL']['width'];?>"<?}?>>
	<?else:?>
	<span class="landing-item landing-item-hover landing-item-unactive">
	<?endif;?>
		<span class="landing-item-inner">
			<div class="landing-title">
				<div class="landing-title-wrap">
					<div class="landing-title-overflow">
						<?= \htmlspecialcharsbx($item['TITLE'])?>
					</div>
					<?if (($item['IS_NEW'] ?? null) === 'Y'): ?>
						<span class="landing-title-new"><?= Loc::getMessage('LANDING_TPL_LABEL_NEW');?></span>
					<?endif;?>
				</div>
			</div>

			<span class="landing-item-cover <?=trim($item['DESCRIPTION']) ? 'landing-item-cover-short' : ''?>">
				<?if ($item['PREVIEW']):?>
					<img class="landing-item-cover-img"
						src="<?= \htmlspecialcharsbx($item['PREVIEW'])?>"
						srcset="<?= \htmlspecialcharsbx($item['PREVIEW2X'] ? $item['PREVIEW2X'] : $item['PREVIEW'])?> 2x,
									<?= \htmlspecialcharsbx($item['PREVIEW3X'] ? $item['PREVIEW3X'] : $item['PREVIEW'])?> 3x">
				<?endif;?>
				<?php if ($item['LABELS'] ?? null):?>
					<span class="landing-item-label">
						<?=Loc::getMessage('LANDING_TPL_LABEL_SUBSCRIPTION')?>
					</span>
				<?php elseif($item['TYPE'] === 'PAGE'):?>
					<span class="landing-item-label landing-item-label-free">
						<?=Loc::getMessage('LANDING_TPL_LABEL_FREE')?>
					</span>
				<?php endif;?>
			</span>

			<div class="landing-item-bottom">
				<?php if (trim($item['DESCRIPTION'])):?>
					<span class="landing-item-description">
						<span class="landing-item-desc-inner">
							<span class="landing-item-desc-overflow">
								<span class="landing-item-desc-height">
									<?= \htmlspecialcharsbx($item['DESCRIPTION'])?>
								</span>
							</span>
							<span class="landing-item-desc-open"></span>
						</span>
					</span>
				<?php endif?>
			</div>
		</span>
	</span>
<?endforeach;?>

	</div>
</div>

<?php if ($arResult['NAVIGATION']->getPageCount() > 1): ?>
	<div
		id="landing-demo-navigation"
		class="<?= (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '' : 'landing-navigation' ?>"
	>
		<?php $APPLICATION->IncludeComponent(
			'bitrix:main.pagenavigation',
			'',
			[
				'NAV_OBJECT' => $arResult['NAVIGATION'],
				'SEF_MODE' => 'N',
				'BASE_LINK' => $arResult['NAV_URI'] .
							   ((defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '&slider' : '')//@tmp bug #105866
			],
			false
		);?>
	</div>
<?php endif; ?>

<?if (
	Manager::isB24()
	&& $arParams['TYPE'] !== 'PAGE'
):?>
<a class="landing-license-banner" href="javascript:void(0)" onclick="BX.SidePanel.Instance.open('<?= SITE_DIR;?>marketplace/?placement=site_templates');">
	<div class="landing-license-banner-icon">
		<div class="landing-license-banner-icon-arrow"></div>
	</div>
	<div class="landing-license-banner-title">
		<?= Loc::getMessage('LANDING_TPL_LOAD_APP_TEMPLATE_2');?>
	</div>
</a>
<?endif;?>

<script type="text/javascript">
	BX.ready(function ()
	{
		<?if ($arResult['LIMIT_REACHED']):?>
		var nodes = BX('grid-tile-wrap').querySelectorAll('.landing-item-payment');
		if (nodes.length)
		{
			for (var i = 0, c = nodes.length; i < c; i++)
			{
				BX.bind(nodes[i], 'click', function(e)
				{
					<?
					echo \Bitrix\Landing\Restriction\Manager::getActionCode(
						($arParams['TYPE'] == 'STORE') ? 'limit_shop_number' : 'limit_sites_number'
					);
					?>
					BX.PreventDefault(e);
				});
			}
		}
		<?endif;?>

		<?if ($select = $request->get('select')):?>
		BX.fireEvent(
			BX('landing-demo-<?= \CUtil::JSEscape($select);?>'),
			'click'
		);
		<?endif;?>
	})
</script>