<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Web\Json;

/** @var $this \CBitrixComponentTemplate */
/** @var \CAllMain $APPLICATION */
/** @var array $arResult*/
/** @var array $arParams*/

$this->addExternalCss($this->GetFolder() . '/template.css');
$this->addExternalJs($this->GetFolder() . '/template.js');

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
	<?if ($arParams['EDITABLE_TITLE_SELECTOR']):?>
		<style>
			<?=$arParams['EDITABLE_TITLE_SELECTOR']?> {
				display: none;
			}
		</style>
	<?endif;?>
</head>
<body class="ui-page-slider-wrapper <?=(!$arParams['PLAIN_VIEW'] ? 'ui-page-slider-padding' : '')?> template-<?=(defined('SITE_TEMPLATE_ID') ? SITE_TEMPLATE_ID  : 'def')?> <?$APPLICATION->ShowProperty('BodyClass');?>">
	<div id="left-panel"><? $APPLICATION->ShowViewContent("left-panel"); ?></div>
	<div class="pagetitle-wrap" style="<?=($arParams['PLAIN_VIEW'] ? 'display: none;' : '')?>">
		<div class="pagetitle-inner-container">
			<div class="pagetitle-menu pagetitle-last-item-in-a-row" id="pagetitle-menu">
				<? $APPLICATION->ShowViewContent("pagetitle"); ?>
			</div>
			<div class="pagetitle">
				<span id="pagetitle" class="pagetitle-item"><? $APPLICATION->ShowTitle(); ?></span>
				<span id="pagetitle_edit" class="pagetitle-edit-button" style="display: none;"></span>
				<input id="pagetitle_input" type="text" class="pagetitle-item" style="display: none;">
			</div>

			<? $APPLICATION->ShowViewContent("inside_pagetitle"); ?>
		</div>
	</div>

	<div id="ui-page-slider-workarea">
		<div id="sidebar"><? $APPLICATION->ShowViewContent("sidebar"); ?></div>
		<div id="workarea-content">
			<div class="<?=($arParams['USE_PADDING'] ? 'ui-page-slider-workarea-content-padding' : '')?>">
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

	<script type="text/javascript">
		BX.ready(function () {
			BX.UI.SidePanelWrapper.init(<?=Json::encode([
				'containerId' => 'workarea-content',
				'isCloseAfterSave' => $arParams['CLOSE_AFTER_SAVE'],
				'isReloadGridAfterSave' => $arParams['RELOAD_GRID_AFTER_SAVE'],
				'isReloadPageAfterSave' => $arParams['RELOAD_PAGE_AFTER_SAVE'],
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