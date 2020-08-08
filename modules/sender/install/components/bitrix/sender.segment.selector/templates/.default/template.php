<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global \CAllMain $APPLICATION */
/** @global \CAllUser $USER */
/** @global \CAllDatabase $DB */
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

$containerId = 'sender-segment-selector';
?>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="sender-segment-selector-wrapper">

	<div class="sender-segment-selector-title">
		<?=($arParams['READONLY'] ?
			$getMessageLocal('SENDER_SEGMENT_SELECTOR_INCLUDE_VIEW_TITLE')
			:
			$getMessageLocal('SENDER_SEGMENT_SELECTOR_INCLUDE_EDIT_TITLE')
		)?>
	</div>
	<?
	$APPLICATION->IncludeComponent('bitrix:sender.ui.tile.selector', '', array(
		'INPUT_NAME' => 'SEGMENT[INCLUDE]',
		'ID' => 'segment-include',
		'LIST' => $arResult['SEGMENTS']['INCLUDE'],
		'SHOW_BUTTON_ADD' => $arParams['CAN_EDIT'],
		'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_SEGMENT_SELECTOR_BUTTON_SELECT'),
		'READONLY' => $arParams['READONLY']
	));
	?>
	<div class="sender-segment-selector-main">
		<div class="sender-segment-selector-main-sum" <?=(($arParams['READONLY'] && !$arParams['SHOW_COUNTERS']) ? 'style="display: none;"' : '')?>>
			<div class="sender-segment-selector-block" <?=($arParams['SHOW_COUNTERS'] ? '' : 'style="display: none;"')?>>
				<div class="sender-segment-selector-block-name">
					<?=$getMessageLocal('SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT')?>:
					<?if(!$arParams['IS_RECIPIENT_COUNT_EXACT']):?>~<?endif;?>
				</div>
				<div data-role="counter" class="sender-segment-selector-block-number">
					<?=htmlspecialcharsbx($arParams['RECIPIENT_COUNT'])?>
				</div>
				<?if($arParams['IS_RECIPIENT_COUNT_EXACT']):?>
					<div data-hint="<?=$getMessageLocal('SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT_EXACT_HINT1')?>"></div>
				<?else:?>
					<div data-hint="<?=$getMessageLocal('SENDER_SEGMENT_SELECTOR_RECIPIENT_COUNT_HINT')?>"></div>
				<?endif;?>
				<div data-role="duration" class="sender-segment-selector-duration <?=($arParams['DURATION_FORMATTED'] ? 'sender-segment-selector-duration-active' : '')?>">
					<span><?=Loc::getMessage('SENDER_SEGMENT_SELECTOR_SEND_TIME')?>: ~ </span>
					<span data-role="duration-text"><?=htmlspecialcharsbx($arParams['DURATION_FORMATTED'])?></span>
				</div>
			</div>
			<?if(!$arParams['READONLY']):?>
				<a data-role="exclude-add-button" class="sender-segment-selector-link <?=(!$arResult['HAS_EXCLUDE_SEGMENTS'] ? 'sender-segment-selector-link-active' : '')?>">
					<?=Loc::getMessage('SENDER_SEGMENT_SELECTOR_EXCLUDES')?>
				</a>
			<?endif;?>
		</div>

		<div data-role="exclude-container" class="sender-segment-selector-exclude" style="<?=(!$arResult['HAS_EXCLUDE_SEGMENTS'] ? 'display: none;' : '')?>">
			<?if(!$arParams['READONLY']):?>
				<div data-role="exclude-remove-button" class="sender-close-icon">
					<div class="sender-close-icon-item"></div>
				</div>
			<?endif;?>
			<div class="sender-segment-selector-title">
				<?=($arParams['READONLY'] ?
					Loc::getMessage('SENDER_SEGMENT_SELECTOR_EXCLUDE_VIEW_TITLE')
					:
					Loc::getMessage('SENDER_SEGMENT_SELECTOR_EXCLUDE_EDIT_TITLE')
				)?>
			</div>
			<?
			$APPLICATION->IncludeComponent('bitrix:sender.ui.tile.selector', '', array(
				'INPUT_NAME' => 'SEGMENT[EXCLUDE]',
				'ID' => 'segment-exclude',
				'LIST' => $arResult['SEGMENTS']['EXCLUDE'],
				'SHOW_BUTTON_ADD' => $arParams['READONLY'],
				'BUTTON_SELECT_CAPTION' => Loc::getMessage('SENDER_SEGMENT_SELECTOR_BUTTON_SELECT'),
				'READONLY' => $arParams['READONLY'],
			));
			?>
		</div>

	</div>

	<script type="text/javascript">
		BX.ready(function () {
			new BX.Sender.Segment.SelectorManager(<?=Json::encode(array(
				'containerId' => $containerId,
				'pathToAdd' => $arParams['PATH_TO_ADD'],
				'pathToEdit' => $arParams['PATH_TO_EDIT'],
				'actionUri' => $arResult['ACTION_URI'],
				'duration' => $arResult['DURATION'],
				'messageCode' => $arParams['MESSAGE_CODE'],
				'recipientCount' => $arParams['RECIPIENT_COUNT'],
				'recipientTypes' => $arResult['RECIPIENT_TYPES'],
				'mess' => array(
					'searchTitle' => Loc::getMessage('SENDER_SEGMENT_SELECTOR_SEARCHER_TITLE'),
				)
			))?>);
		});
	</script>
</div>