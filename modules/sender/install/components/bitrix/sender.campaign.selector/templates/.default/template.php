<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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

$containerId = 'sender-campaign-selector-'.mb_strtolower($arParams['INPUT_NAME']);
?>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-campaign-selector-wrapper">

	<?if (!$arParams['SELECT_ONLY']):?>
		<div class="sender-campaign-selector-title">
			<?=$getMessageLocal('SENDER_CAMPAIGN_SELECTOR_TITLE')?>
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
		'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_CAMPAIGN_SELECTOR_BUTTON_SELECT'),
		'READONLY' => $arParams['READONLY'],
	));
	?>

	<div class="sender-campaign-selector-main" style="<?=($arParams['SELECT_ONLY'] ? 'display: none;' : '')?>">
		<div class="sender-campaign-selector-main-sum">
			<div class="sender-campaign-selector-block">
				<div class="sender-campaign-selector-block-name">
					<?=$getMessageLocal('SENDER_CAMPAIGN_SELECTOR_SUBSCRIBER_COUNT')?>:
				</div>
				<div data-role="counter" class="sender-campaign-selector-block-number">
					<?=htmlspecialcharsbx($arResult['SUBSCRIBER_COUNT'])?>
				</div>
				<div data-hint="<?=$getMessageLocal('SENDER_CAMPAIGN_SELECTOR_SUBSCRIBER_COUNT_HINT')?>"></div>
				<div class="sender-campaign-selector-site">
					<span><?=Loc::getMessage('SENDER_CAMPAIGN_SELECTOR_SITE')?>:</span>
					<span data-role="site-name"><?=htmlspecialcharsbx($arResult['SITE_NAME'])?></span>
				</div>
			</div>
		</div>
	</div>

	<script>
		BX.ready(function () {
			BX.Sender.Campaign.SelectorManager.create(<?=Json::encode(array(
				'id' => $containerId,
				'containerId' => $containerId,
				'pathToAdd' => $arParams['PATH_TO_ADD'],
				'pathToEdit' => $arParams['PATH_TO_EDIT'],
				'actionUri' => $arResult['ACTION_URI'],
				'mess' => array(
					'searchTitle' => Loc::getMessage('SENDER_CAMPAIGN_SELECTOR_SEARCHER_TITLE'),
				)
			))?>);
		});
	</script>
</div>