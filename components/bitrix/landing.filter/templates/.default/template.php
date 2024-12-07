<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Help;
use Bitrix\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */

// init
Loc::loadMessages(__FILE__);
\CJSCore::init(array('sidepanel', 'action_dialog', 'loader'));
\Bitrix\Main\UI\Extension::load('ui.buttons');
\Bitrix\Main\UI\Extension::load('ui.buttons.icons');
\Bitrix\Main\UI\Extension::load('ui.hint');

if ($arResult['FATAL'])
{
	return;
}

// some vars
$uriAjax = new \Bitrix\Main\Web\Uri($arResult['CUR_URI']);
$uriAjax->addParams(array(
	'IS_AJAX' => 'Y',
	$arResult['NAVIGATION_ID'] => $arResult['CURRENT_PAGE']
));
$uriAjax->deleteParams([
	LandingBaseComponent::NAVIGATION_ID
]);
if (defined('SITE_TEMPLATE_ID'))
{
	$isBitrix24Template = SITE_TEMPLATE_ID === 'bitrix24';
}

// title
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'pagetitle-toolbar-field-view');
?>

<?php
if ($isBitrix24Template)
{
	$this->SetViewTarget('inside_pagetitle');
}

/** @var CBitrixComponentTemplate $this */
\Bitrix\Main\Page\Asset::getInstance()->addJs($this->GetFolder() . '/script.js');

if (!$isBitrix24Template):?>
<div class="tasks-interface-filter-container">
<?endif;?>

	<?
	if (isset($arParams['BUTTONS']) && is_array($arParams['BUTTONS']))
	{
		if (count($arParams['BUTTONS']) === 1)
		{
			$button = array_shift($arParams['BUTTONS']);
			if (isset($button['LINK'], $button['TITLE']))
			{
				$linkClassList = 'ui-btn ui-btn-md ui-btn-success landing-filter-action-link';
				$linkEnabled = true;
				$isButtonDisabled = isset($button['DISABLED']) && $button['DISABLED'] === true;
				if ($isButtonDisabled)
				{
					$linkClassList .= ' ui-btn-disabled ui-btn-icon-lock';
					$linkEnabled = false;
				}
				?>
				<div id="landing-create-element-container" class="pagetitle-container pagetitle-align-right-container">
					<a
						<?php if ($isButtonDisabled):?>
							data-hint="
								<?= Loc::getMessage('LANDING_TPL_CREATE_BUTTON_HINT') ?>
								<?php if ($helpUrl = Help::getHelpUrl('SHOP1C')):?>
									<br>
									<a href='<?= $helpUrl ?>'>
										<?= Loc::getMessage('LANDING_TPL_CREATE_BUTTON_HINT_LINK_TEXT') ?>
									</a>
								<?php endif;?>
							"
							data-hint-no-icon
							data-hint-html
							data-hint-interactivity
						<?php endif;?>
						<?php if ($linkEnabled):?>
							href="<?= \htmlspecialcharsbx($button['LINK']) ?>"
						<?php endif;?>
						id="landing-create-element"
						class="<?= \htmlspecialcharsbx($linkClassList) ?>"
					>
						<?= \htmlspecialcharsbx($button['TITLE']);?>
					</a>
				</div>
				<script>
					BX.ready(function ()
					{
						BX.UI.Hint.init(BX('landing-create-element-container'));
					});
				</script>
			<?
			}
		}
		else
		{
		$button = array_shift($arParams['BUTTONS']);
		?>
		<?if (isset($button['LINK']) && isset($button['TITLE'])):?>
			<div class="pagetitle-container pagetitle-align-right-container" id="landing-menu-actions">
				<a href="<?= \htmlspecialcharsbx($button['LINK']);?>" id="landing-create-element" <?
				?>class="ui-btn ui-btn-md ui-btn-success ui-btn-icon-add landing-filter-action-link ui-btn-dropdown">
					<?= \htmlspecialcharsbx($button['TITLE']);?>
				</a>
			</div>
			<script>
				var landingCreateButtons = [
					<?foreach ($arParams['BUTTONS'] as $button):?>
					<?if (isset($button['LINK']) && isset($button['TITLE'])):?>
					{
						href: '<?= \CUtil::JSEscape($button['LINK']);?>',
						text: '<?= \CUtil::JSEscape($button['TITLE']);?>'
					},
					<?endif;?>
					<?endforeach;?>
					null
				];
			</script>
		<?endif;?>
			<?
		}
	}
	?>

	<div class="pagetitle-container<?if (!$isBitrix24Template) {?> pagetitle-container-light<?}?> pagetitle-flexible-space">
		<?$APPLICATION->IncludeComponent(
			'bitrix:main.ui.filter',
			'',
			array(
				'FILTER_ID' => $arParams['FILTER_ID'],
				'GRID_ID' => $arParams['FILTER_ID'],
				'FILTER' => $arResult['FILTER'],
				'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
				'ENABLE_LABEL' => true,
				'ENABLE_LIVE_SEARCH' => true,
				'RESET_TO_DEFAULT_MODE' => true
			),
			$this->__component,
			array('HIDE_ICONS' => true)
		);?>
		<script>
			var landingAjaxPath = '<?= \CUtil::jsEscape($uriAjax->getUri());?>';
			var landingFilterId = '<?= \CUtil::jsEscape($arParams['FILTER_ID']);?>';
		</script>
	</div>

	<div class="landing-filter-buttons-container">

		<span class="ui-btn ui-btn-light-border ui-btn-themes landing-recycle-bin-btn" id="landing-recycle-bin">
			<?= Loc::getMessage('LANDING_TPL_RECYCLE_BIN');?>
		</span>

		<?if ($arParams['SETTING_LINK']):
			// for compatibility
			if (!is_array($arParams['SETTING_LINK']))
			{
				$arParams['SETTING_LINK'] = [[
					'TITLE' => '',
					'LINK' => $arParams['SETTING_LINK']
				]];
			}
			?>
			<script>
				var landingSettingsButtons = [
					<?
					$bFirst = true;
					foreach ($arParams['SETTING_LINK'] as $link):?>
						<?if (isset($link['LINK']) && isset($link['TITLE'])):?>
						<?= !$bFirst ? ',' : '';?>{
							href: '<?= \CUtil::JSEscape($link['LINK']);?>',
							text: '<?= \CUtil::JSEscape($link['TITLE']);?>'
							<? if (isset($link['DATASET']) && is_array($link['DATASET'])): ?>
								, dataset: <?= \CUtil::phpToJSObject($link['DATASET']) ?>
							<? endif ?>
							<? if (isset($link['DELIMITER']) && $link['DELIMITER'] === true): ?>
								, delimiter: true
							<? endif ?>
						}
						<?
						$bFirst = false;
						endif;?>
					<?endforeach;?>
				];
			</script>
			<a class="ui-btn ui-btn-light-border ui-btn-themes ui-btn-icon-setting" id="landing-menu-settings" href="#"></a>
		<?endif;?>

		<?if ($arParams['FOLDER_SITE_ID']):?>
		<a class="ui-btn ui-btn-light-border ui-btn-icon-add-folder ui-btn-themes landing-filter-buttons-add-folder" <?
			?>id="landing-create-folder" <?
			?>data-type="<?= $arParams['TYPE'];?>" <?
			?>data-action="<?= \CUtil::jsEscape(Loc::getMessage('LANDING_TPL_CREATE_FOLDER_ACTION'));?>" <?
			?>data-siteId="<?= $arParams['FOLDER_SITE_ID'];?>" <?
			?>data-folderId="<?= $arParams['FOLDER_ID'];?>" <?
			?>href="javascript:void(0);" <?
			?>title="<?= Loc::getMessage('LANDING_TPL_CREATE_FOLDER');?>"></a>
		<?else:?>
		<?endif;?>

	</div>


<?if (!$isBitrix24Template):?>
</div>
<?endif;?>

<?php
if ($isBitrix24Template)
{
	$this->EndViewTarget();
}
?>
