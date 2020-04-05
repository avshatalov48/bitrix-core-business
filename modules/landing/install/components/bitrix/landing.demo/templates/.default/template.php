<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
\Bitrix\Main\UI\Extension::load("ui.fonts.opensans");
Loc::loadMessages(__FILE__);

// some errors
if ($arResult['ERRORS'])
{
	foreach ($arResult['ERRORS'] as $code => $error)
	{
		echo '<p style="color: red;">' . $error . '</p>';
	}
}

// show message for license renew if need
if (empty($arResult['DEMO']))
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
	($bodyClass ? $bodyClass.' ' : '') . 'no-all-paddings no-background'
);
\Bitrix\Landing\Manager::setPageTitle(
	Loc::getMessage('LANDING_TPL_TITLE')
);

// additional assets
\CJSCore::Init(array('popup', 'action_dialog', 'loader', 'sidepanel'));
Asset::getInstance()->addCSS('/bitrix/components/bitrix/landing.sites/templates/.default/style.css');
Asset::getInstance()->addJS('/bitrix/components/bitrix/landing.sites/templates/.default/script.js');
?>

<div class="grid-tile-wrap" id="grid-tile-wrap">
	<div class="grid-tile-inner" id="grid-tile-inner">

<?
foreach ($arResult['DEMO'] as $item):
	$uriSelect = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
	$uriSelect->addParams(array(
		'tpl' => (
					(
						defined('SMN_SITE_ID') ||
						!$arParams['SITE_ID']
					)
					&&
				 	isset($item['DATA']['items'][0])
				)
				? $item['DATA']['items'][0]
				: $item['ID']
	));
	?>
	<?if ($item['AVAILABLE']):?>
	<span data-href="<?= $uriSelect->getUri();?>" class="landing-template-pseudo-link landing-item landing-item-hover<?= $arResult['LIMIT_REACHED'] ? ' landing-item-payment' : '';?>">
	<?else:?>
	<span class="landing-item landing-item-hover landing-item-disabled">
	<?endif;?>
		<span class="landing-item-inner">
			<div class="landing-title">
				<div class="landing-title-wrap">
					<div class="landing-title-overflow"><?= \htmlspecialcharsbx($item['TITLE'])?></div>
				</div>
			</div>
			<?if (trim($item['DESCRIPTION'])):?>
				<span class="landing-item-cover landing-item-cover-short">
					<?if ($item['PREVIEW']):?>
						<img class="landing-item-cover-img"
							 src="<?= \htmlspecialcharsbx($item['PREVIEW'])?>"
							 srcset="<?= \htmlspecialcharsbx($item['PREVIEW2X'] ? $item['PREVIEW2X'] : $item['PREVIEW'])?> 2x,
									<?= \htmlspecialcharsbx($item['PREVIEW3X'] ? $item['PREVIEW3X'] : $item['PREVIEW'])?> 3x">
					<?endif;?>
				</span>
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
			<?else:?>
				<span class="landing-item-cover">
					<?if ($item['PREVIEW']):?>
						<img class="landing-item-cover-img"
							 src="<?= \htmlspecialcharsbx($item['PREVIEW'])?>"
							 srcset="<?= \htmlspecialcharsbx($item['PREVIEW2X'] ? $item['PREVIEW2X'] : $item['PREVIEW'])?> 2x,
									<?= \htmlspecialcharsbx($item['PREVIEW3X'] ? $item['PREVIEW3X'] : $item['PREVIEW'])?> 3x">
					<?endif;?>
				</span>
			<?endif?>
		</span>
	<?if (!$item['AVAILABLE']):?>
	</span>
	<?else:?>
	</span>
	<?endif;?>
<?endforeach;?>

	</div>
</div>

<?if ($arResult['NAVIGATION']->getPageCount() > 1):?>
	<div class="<?= (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? '' : 'landing-navigation';?>">
		<?$APPLICATION->IncludeComponent(
			'bitrix:main.pagenavigation',
			'',//grid
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

<a class="landing-license-banner" href="javascript:void(0)" onclick="BX.SidePanel.Instance.open('<?= SITE_DIR;?>marketplace/?placement=site_templates');">
	<div class="landing-license-banner-icon">
		<div class="landing-license-banner-icon-arrow"></div>
	</div>
	<div class="landing-license-banner-title">
		<?= Loc::getMessage('LANDING_TPL_LOAD_APP_TEMPLATE');?>
	</div>
</a>

<script type="text/javascript">
	BX.ready(function ()
	{
		var items = [].slice.call(document.querySelectorAll('.landing-template-pseudo-link'));

		items.forEach(function(item) {
			if (!BX.hasClass(item, 'landing-item-payment'))
			{
				BX.bind(item, 'click', function(event) {

					if(event.target.classList.contains('landing-item-desc-open'))
					{
						return;
					}

					BX.SidePanel.Instance.open(event.currentTarget.dataset.href, {
						allowChangeHistory: false
					});
				});
			}
        });

		var wrapper = BX('grid-tile-wrap');
		var tiles = Array.prototype.slice.call(wrapper.getElementsByClassName('landing-item'));
		new BX.Landing.Component.Demo({
			wrapper : wrapper,
			inner: BX('grid-tile-inner'),
			tiles : tiles
		});
		<?if ($arResult['LIMIT_REACHED']):?>
		if (typeof BX.Landing.PaymentAlert !== 'undefined')
		{
			BX.Landing.PaymentAlert({
				nodes: wrapper.querySelectorAll('.landing-item-payment'),
				title: '<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_LIMIT_REACHED_TITLE'));?>',
				message: '<?= ($arParams['SITE_ID'] > 0)
					? \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_PAGE_LIMIT_REACHED_TEXT'))
					: \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_SITE_LIMIT_REACHED_TEXT'));
					?>'
			});
		}
		<?endif;?>
	})
</script>