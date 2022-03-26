<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @var \LandingBaseComponent $component */
/** @var \CMain $APPLICATION */

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;

\Bitrix\Main\UI\Extension::load(['ui.fonts.opensans', 'sidepanel']);
Loc::loadMessages(__FILE__);
Loc::loadMessages(
	Manager::getDocRoot() . '/bitrix/components/bitrix/landing.demo/templates/.default/designed_by.php'
);

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
if (empty($arResult['DEMO']) && !isset($arResult['ERRORS']['ACCESS_DENIED']))
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
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings no-background landing-slider-frame-popup'
);
\Bitrix\Landing\Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);

if ($arParams['TYPE'] !== 'STORE')
{
	$emptyTpl = ($arParams['TYPE'] === 'KNOWLEDGE' && !$arParams['SITE_ID'])
			? 'empty-multipage/main'
			: 'empty'
	;
	$emptyCreateUrl = $component->getUri(
		['tpl' => $emptyTpl],
		['select']
	);
	$createEmptyButton =
		'<div 
		class="ui-btn ui-btn-md ui-btn-light-border landing-template-pseudo-link"
		data-href="' . $emptyCreateUrl . '"
	>'
		. Loc::getMessage("LANDING_TPL_CREATE_EMPTY")
		. '</div>';
	$APPLICATION->addViewContent('title_actions', $createEmptyButton);
}

// additional assets
\CJSCore::Init(['popup', 'action_dialog', 'loader', 'sidepanel']);
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.sites/templates/.default/style.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.sites/templates/.default/script.js');
?>

<div style="display: none">
	<?$APPLICATION->includeComponent(
		'bitrix:ui.feedback.form',
		'',
		$component->getFeedbackParameters('demo')
	);?>
</div>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">

		<?if ($arParams['TYPE'] == 'PAGE'):?>
			<span class="landing-item landing-item-contact" onclick="BX.fireEvent(BX('landing-feedback-demo-button'), 'click');">
				<span class="landing-item-inner">
					<span class="landing-item-contact-title"><?= Loc::getMessage('LANDING_TPL_FEEDBACK_TITLE');?></span>
					<span class="landing-item-contact-icon"></span>
					<span class="landing-item-contact-desc"><?= Loc::getMessage('LANDING_TPL_FEEDBACK_MESSAGE');?></span>
					<span class="ui-btn ui-btn-sm ui-btn-round landing-item-contact-btn">
						<?= Loc::getMessage('LANDING_TPL_FEEDBACK_SEND');?>
					</span>
				</span>
			</span>
		<?endif;?>
<?
foreach ($arResult['DEMO'] as $item):
	// empty is in top button, not need show in list
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
		isset($item['DATA']['site_group_item']) &&
		$item['DATA']['site_group_item'] == 'Y'
	)
	{
		continue;
	}

	$tpl = (
		(
			defined('SMN_SITE_ID') ||
			!$arParams['SITE_ID']
		)
		&&
		isset($item['DATA']['items'][0])
	)
		? $item['DATA']['items'][0]
		: $item['ID'];

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
	else if (isset($item['EXTERNAL_URL']['href']))
	{
		$previewUrl = $item['EXTERNAL_URL']['href'];
	}
	else
	{
		$previewUrl = '';
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
					<?if ($item['IS_NEW'] === 'Y'): ?>
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
				<?php if ($item['LABELS']):?>
					<span class="landing-item-label">
						<?=$item['LABELS'][0]['TEXT']?>
					</span>
				<?php endif;?>
			</span>

			<div class="landing-item-bottom">
				<?php if ($item['DESIGNED_BY']):?>
					<a class="landing-item-designed"
						href="<?= Loc::getMessage('LANDING_TPL_DESIGNED_BY_' . $item['DESIGNED_BY'] . '_URL')?>"
						target="_blank"
					>
						<?php $name = Loc::getMessage('LANDING_TPL_DESIGNED_BY_' . $item['DESIGNED_BY'] . '_NAME'); ?>
						<?= Loc::getMessage('LANDING_TPL_DESIGN_BY', [
							'#DESIGNER#' => $name,
						]);?>
					</a>
				<?php endif;?>

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

<?if ($arResult['NAVIGATION']->getPageCount() > 1):?>
	<div class="<?= (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '' : 'landing-navigation';?>">
		<?$APPLICATION->IncludeComponent(
			'bitrix:main.pagenavigation',
			'',
			array(
				'NAV_OBJECT' => $arResult['NAVIGATION'],
				'SEF_MODE' => 'N',
				'BASE_LINK' => $arResult['CUR_URI'] .
							   ((defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '&slider' : '')//@tmp bug #105866
			),
			false
		);?>
	</div>
<?endif;?>

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