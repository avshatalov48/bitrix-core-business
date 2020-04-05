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
use Bitrix\Main\Localization\Loc;

$getMessageLocal = function($messageCode, $replace = []) use ($arParams)
{
	if (empty($arParams['~MESS'][$messageCode]))
	{
		return Loc::getMessage($messageCode, $replace);
	}

	return str_replace(
		array_keys($replace),
		array_values($replace),
		$arParams['~MESS'][$messageCode]
	);
};

$containerId = 'sender-contact-set-selector-' . strtolower($arParams['INPUT_NAME']);
?>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-campaign-selector-wrapper">

	<?if (!$arParams['SELECT_ONLY']):?>
		<div class="sender-campaign-selector-title">
			<?=$getMessageLocal('SENDER_CONTACT_SET_SELECTOR_TITLE')?>
		</div>
	<?endif;?>

	<?
	$APPLICATION->IncludeComponent('bitrix:sender.ui.tile.selector', '', array(
		'INPUT_NAME' => $arParams['INPUT_NAME'],
		'ID' => $containerId,
		'LIST' => $arResult['TILES'],
		'MULTIPLE' => $arParams['MULTIPLE'],
		'DUPLICATES' => false,
		'SHOW_BUTTON_ADD' => !$arParams['READONLY'] && !$arParams['SELECT_ONLY'],
		'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_CONTACT_SET_SELECTOR_BUTTON_SELECT'),
		'READONLY' => $arParams['READONLY'],
	));
	?>

	<script type="text/javascript">
		BX.ready(function () {
			BX.Sender.ContactSet.SelectorManager.create(<?=Json::encode(array(
				'id' => $containerId,
				'containerId' => $containerId,
				'pathToAdd' => $arParams['PATH_TO_ADD'],
				'pathToEdit' => $arParams['PATH_TO_EDIT'],
				'actionUri' => $arResult['ACTION_URI'],
				'mess' => array(
					'searchTitle' => Loc::getMessage('SENDER_CONTACT_SET_SELECTOR_SEARCHER_TITLE'),
				)
			))?>);
		});
	</script>
</div>