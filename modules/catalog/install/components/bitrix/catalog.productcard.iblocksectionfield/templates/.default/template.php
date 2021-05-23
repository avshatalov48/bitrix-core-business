<?php
/**
 * @var $component \CatalogProductCardIblockSectionField
 * @var $this \CBitrixComponentTemplate
 * @var $arParams array
 * @var $arResult array
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\UI\EntitySelector\Dialog;

Extension::load(['ui.entity-selector']);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$selectorId = 'section-selector-' . $this->randString();
$selectorHiddenId = 'section-selector-' . $this->randString() . '-hidden';
?>
<div id="<?=$selectorId?>"></div>
<div id="<?=$selectorHiddenId?>">
	<?php
	$preselectedItems = [];
	foreach ($arResult['LIST'] as $section)
	{
		echo '<input type="hidden" name="IBLOCK_SECTION[]" value="' . $section['id'] . '" />';
		$preselectedItems[] = ['section', $section['id']];
	}
	$options = [
		[
			'id' => 'section',
			'options' => [
				'iblockId' => $arParams['IBLOCK_ID'],
			],
		],
	];
	$selectedItems = Dialog::getSelectedItems($preselectedItems, $options)->toJsObject();
	?>
</div>
<script>
	BX.ready(function(){
		BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
		BX.Catalog.SectionSelector.Instance = new BX.Catalog.SectionSelector(
			{
				selectorId: '<?=CUtil::JSEscape($selectorId)?>',
				selectorHiddenId: '<?=CUtil::JSEscape($selectorHiddenId)?>',
				selectedItems: <?=$selectedItems?>,
				iblockId: '<?=CUtil::JSEscape($arParams['IBLOCK_ID'])?>'
			}
		);
	});
</script>