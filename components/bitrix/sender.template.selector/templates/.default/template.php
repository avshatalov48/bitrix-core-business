<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->addExternalJs($this->GetFolder() . '/grid.js');

Extension::load(['ui.hint', 'ui.design-tokens']);

$containerId = 'bx-sender-template-selector';

?>
<script>
	BX.ready(function () {
		BX.Sender.Template.Selector.init(<?=Json::encode(array(
			'containerId' => $containerId,
			'actionUri' => $arResult['ACTION_URI'],
			'grid' => $arResult['GRID'],
			'mess' => array(
				'dlgBtnSelect' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_SELECT'),
				'dlgBtnDemo' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_DEMO'),
				'dlgBtnCancel' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_CANCEL'),
				'dlgPreviewTitle' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_PREVIEW_TITLE'),
				'showMore' => Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_SHOW_MORE')
			)
		))?>);
	});
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-template-cont">

	<div data-role="draw-place" class="sender-tpl"></div>

	<script id="sender-template-selector-preview" type="text/template" data-skip-movint="true">
		<div class="sender-template-preview">
			<iframe sandbox="" class="sender-template-preview-frame">
		</div>
	</script>

	<script data-role="tpl-row" type="text/template" data-skip-movint="true">
		<div class="sender-tpl-section" style="%style%">
			<div data-role="row-name" class="sender-tpl-title">%name%</div>
			<div data-role="row-items" class="sender-tpl-items"></div>
		</div>
	</script>

	<script data-role="tpl-item" type="text/template" data-skip-movint="true">
		<div class="sender-tpl-item" style="%style%">
			<div data-role="item-content" class="sender-tpl-content">
				<div data-role="item-title" class="sender-tpl-item-title">
					<span class="sender-tpl-item-title-name">%name%</span>
					<span class="sender-tpl-item-title-demo"><?=Loc::getMessage('SENDER_COMP_TEMPLATE_SELECTOR_DEMO')?></span>
				</div>
				<div data-role="item-image" class="sender-tpl-item-image" style="%image-style%">
					<div data-role="item-buttons" class="sender-tpl-config"></div>
				</div>
				<div data-role="item-desc" class="sender-tpl-item-description">%desc%</div>
			</div>
		</div>
	</script>

	<script data-role="tpl-button" type="text/template" data-skip-movint="true">
		<div class="sender-tpl-config-item">%name%</div>
	</script>
</div>