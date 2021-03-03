<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global \CAllMain $APPLICATION */
/** @global \CAllUser $USER */
/** @global \CAllDatabase $DB */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var \CBitrixComponent $component */

use Bitrix\Main\Web\Json;
$regExpId = '/[^a-zA-Z0-9]/';
$prefix = 'rest-integration-selector-';
$id = preg_replace($regExpId, '', mb_strtolower($arParams['INPUT_NAME']));
$scopeCode = '';
if (is_string($arParams['INPUT_SCOPE_NAME']))
{
	$scopeCode = $prefix . preg_replace($regExpId, '', mb_strtolower($arParams['INPUT_SCOPE_NAME']));
}
$containerId = $prefix . $id;
?>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="rest-integration-selector-wrapper">

	<?if ($arParams['TITLE'] != ''):?>
		<div class="rest-integration-selector-title">
			<?=$arParams['TITLE']?>
		</div>
	<?endif;?>

	<?
	$APPLICATION->IncludeComponent(
		'bitrix:ui.tile.selector',
		'',
		array(
			'INPUT_NAME' => $arParams['INPUT_NAME'],
			'ID' => $containerId,
			'LIST' => $arResult['TILES'],
			'MULTIPLE' => $arParams['MULTIPLE'],
			'DUPLICATES' => $arParams['DUPLICATES'],
			'SHOW_BUTTON_SELECT' => $arParams['SHOW_BUTTON_SELECT'],
			'SHOW_BUTTON_ADD' => $arParams['SHOW_BUTTON_ADD'],
			'CAN_REMOVE_TILES' => $arParams['CAN_REMOVE_TILES'],
			'BUTTON_SELECT_CAPTION' => $arParams['TITLE_BUTTON'],
			'READONLY' => $arParams['READONLY'],
		)
	);
	?>
	<script type="text/javascript">
		BX.ready(function () {
			BX.rest.integration.selectorManager.create(<?=Json::encode(array(
				'id' => $containerId,
				'containerId' => $containerId,
				'scopeSelectorName' => $scopeCode,
				'pathToAdd' => $arParams['PATH_TO_ADD'],
				'pathToEdit' => $arParams['PATH_TO_EDIT'],
				'action' => $arParams['ACTION'],
				'onChange' => $arParams['ON_CHANGE'],
				'signetParameters' => $this->getComponent()->getSignedParameters(),
				'mess' => array(
					'searchTitle' => $arParams['TITLE_SEARCHER_TITLE'],
				)
			))?>);
		});
	</script>
</div>