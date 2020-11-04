<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Json;
use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;

/** @var $this \CBitrixComponentTemplate */
/** @var \CAllMain $APPLICATION */
/** @var array $arResult*/
/** @var array $arParams*/

CJSCore::Init();
$this->addExternalCss($this->GetFolder() . '/template.css');
$this->addExternalJs($this->GetFolder() . '/template.js');
\Bitrix\Main\UI\Extension::load(['sidepanel', 'ui.common', 'ui.fonts.opensans']);

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID ?>" lang="<?=LANGUAGE_ID ?>">
<head>
	<script type="text/javascript">
		// Prevent loading page without header and footer
		if(window === window.top)
		{
			window.location = "<?=\CUtil::JSEscape($APPLICATION->GetCurPageParam('', ['IFRAME'])); ?>";
		}
	</script>
	<?$APPLICATION->ShowHead();?>
	<title><?$APPLICATION->ShowTitle()?></title>
	<?if ($arParams['EDITABLE_TITLE_SELECTOR']):?>
		<style>
			<?=$arParams['EDITABLE_TITLE_SELECTOR']?> {
				display: none;
			}
		</style>
	<?endif;?>

	<?
	if ($arResult["SHOW_BITRIX24_THEME"] == "Y")
	{
		$themePicker = new ThemePicker(SITE_TEMPLATE_ID, false, $arParams["POPUP_COMPONENT_BITRIX24_THEME_FOR_USER_ID"]);
		$themePicker->showHeadAssets();
	}
	?>
</head>

<?
$bodyClass = "ui-page-slider-wrapper";
if (!$arParams['PLAIN_VIEW'])
{
	$bodyClass .= " ui-page-slider-padding";
}

$bodyClass .= " template-".(defined('SITE_TEMPLATE_ID') ? SITE_TEMPLATE_ID  : 'def');

if ($arResult["SHOW_BITRIX24_THEME"] == "Y")
{
	$bodyClass .= " bitrix24-".$themePicker->getCurrentBaseThemeId()."-theme";
}
else
{
	$bodyClass .= " ui-page-slider-wrapper-default-theme";
}
?>
<body class="<?=$bodyClass?> <?$APPLICATION->ShowProperty('BodyClass');?>">
<?
if ($arResult["SHOW_BITRIX24_THEME"] == "Y")
{
	$themePicker->showBodyAssets();
}
?>
<div class="ui-slider-page">
	<div id="left-panel" class="ui-page-slider-left-panel"><? $APPLICATION->ShowViewContent("left-panel"); ?></div>
	<div id="ui-page-slider-content">
		<div class="pagetitle-above"><?$APPLICATION->ShowViewContent("above_pagetitle")?></div>
		<? if(!isset($arParams['USE_UI_TOOLBAR']) || $arParams['USE_UI_TOOLBAR'] !== 'Y')
		{
		?>
			<div class="ui-side-panel-wrap-title-wrap" style="<?=($arParams['PLAIN_VIEW'] ? 'display: none;' : '')?>">
				<div class="ui-side-panel-wrap-title-inner-container">
					<div class="ui-side-panel-wrap-title-menu ui-side-panel-wrap-title-last-item-in-a-row">
						<? $APPLICATION->ShowViewContent("pagetitle"); ?>
					</div>
					<div class="ui-side-panel-wrap-title">
						<div class="ui-side-panel-wrap-title-box">
							<span id="pagetitle" class="ui-side-panel-wrap-title-item">
								<span class="ui-side-panel-wrap-title-name-item ui-side-panel-wrap-title-name"><? $APPLICATION->ShowTitle(); ?></span>
								<span class="ui-side-panel-wrap-title-edit-button" style="display: none;"></span>
								<input type="text" class="ui-side-panel-wrap-title-item ui-side-panel-wrap-title-input" style="display: none;">
							</span>
							<span class="ui-side-panel-wrap-subtitle-box">
								<span class="ui-side-panel-wrap-subtitle-item"></span>
								<span class="ui-side-panel-wrap-subtitle-control"></span>
							</span>
						</div>
						<? $APPLICATION->ShowViewContent("inside_pagetitle_below"); ?>
					</div>
					<? $APPLICATION->ShowViewContent("inside_pagetitle"); ?>
				</div>
			</div>
		<?
		}
		else
		{
			$APPLICATION->IncludeComponent("bitrix:ui.toolbar", '', []);
		}
		?>
		<div class="ui-side-panel-wrap-below"><?$APPLICATION->ShowViewContent("below_pagetitle")?></div>

		<div class="ui-page-slider-workarea">
		<div class="ui-side-panel-wrap-sidebar"><? $APPLICATION->ShowViewContent("sidebar"); ?></div>
		<div id="workarea-content" class="ui-side-panel-wrap-workarea<?=($arParams['USE_PADDING'] ? ' ui-page-slider-workarea-content-padding' : '')?>">
			<?
			include ('content.php');

			if (!empty($arParams['BUTTONS']))
			{
				$APPLICATION->IncludeComponent(
					"bitrix:ui.button.panel",
					"",
					["BUTTONS" => $arParams['BUTTONS']],
					false
				);
			}
			?>
		</div>
	</div>
	</div>
	<div><?$APPLICATION->ShowViewContent("below_page")?></div>
	<script type="text/javascript">
		BX.ready(function () {
			BX.UI.SidePanel.Wrapper.init(<?=Json::encode([
				'containerId' => 'workarea-content',
				'isCloseAfterSave' => $arParams['CLOSE_AFTER_SAVE'],
				'isReloadGridAfterSave' => $arParams['RELOAD_GRID_AFTER_SAVE'],
				'isReloadPageAfterSave' => $arParams['RELOAD_PAGE_AFTER_SAVE'],
				'skipNotification' => $arResult['SKIP_NOTIFICATION'],
				'useLinkTargetsReplacing' => $arParams['USE_LINK_TARGETS_REPLACING'],
				'title' => [
					'defaultTitle' => $arParams['EDITABLE_TITLE_DEFAULT'],
					'selector' => $arParams['EDITABLE_TITLE_SELECTOR']
				],
				'notification' => $arParams['NOTIFICATION'],
			])?>);
		});
	</script>
</body>
</html>
