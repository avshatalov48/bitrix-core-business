<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Web\Json;

CJSCore::Init(['ajax', 'popup', 'ui.buttons.icons', 'ui.fonts.opensans']);

$templateFolder = $this->getFolder();
$this->addExternalJs($templateFolder."/utils.js");

/**
 * Main buttons container required attrs
 * @property {string} id Container id
 * @var $arResult
 * @var $arParams
 *
 */

/**
 * Main buttons item required attrs
 * @property {string} id
 *
 * Main buttons item data- attrs
 * @property {boolean} disabled @required
 * @property {string} class @required icon class
 * @property {string} onclick
 * @property {string} url
 * @property {string} text Text for submenu item
 * @property {number} counter Counter value
 * @property {boolean} locked Is locked
 */
?>

<div class="main-buttons<?=$arParams["THEME_ID"]?>">
	<div class="main-buttons-box">
		<div class="main-buttons-inner-container" id="<?=$arResult["ID"]?>">
		<? foreach ($arResult["ITEMS"] as $key => $arItem) :
			if (isset($arItem['PARENT_ITEM_ID']) && !empty($arItem['PARENT_ITEM_ID']))
			{
				continue;
			}

			$itemClass = $arItem["CLASS"];
			if ($arItem["IS_PASSIVE"])
			{
				$itemClass .= " --passive";
			}
			else if ($arItem["IS_ACTIVE"])
			{
				if (isset($arParams["CLASS_ITEM_ACTIVE"]) && mb_strlen($arParams["CLASS_ITEM_ACTIVE"]))
				{
					$itemClass .= " ".$arParams["CLASS_ITEM_ACTIVE"];
				}
				else
				{
					$itemClass .= " main-buttons-item-active";
				}
			}

			if ($arItem["HAS_MENU"])
			{
				$itemClass .= " --has-menu";
			}

			if ($arItem["IS_LOCKED"])
			{
				$itemClass .= " --locked";
			}

			$dataCounterId = '';
			if (isset($arItem['COUNTER_ID']))
			{
				$dataCounterId = 'data-mib-counter-id=' . $arItem['COUNTER_ID'];
			}

			$title = $arItem["TEXT"];
			if (mb_strlen($title) > $arParams['MAX_ITEM_LENGTH'])
			{
				$title = mb_substr($title, 0, $arParams['MAX_ITEM_LENGTH'] - 3) . '...';
			}

			?><div
				class="main-buttons-item <?=$itemClass?>"
				id="<?=$arItem["ID"]?>"
				data-disabled="<?=$arItem["IS_DISABLED"]?>"
				data-class="<?=$arItem["CLASS_SUBMENU_ITEM"]?>"
				data-id="<?=$arItem["DATA_ID"]?>"
				data-item="<?= htmlspecialcharsbx(Json::encode($arItem))?>"
				data-top-menu-id="<?=$arResult["ID"]?>"
				<? if (isset($arItem['PARENT_ITEM_ID'])) : ?>
				data-parent-item-id="<?=$arItem['PARENT_ITEM_ID']?>"
				<? endif;?>
				<? if (isset($arItem['HAS_CHILD']) && $arItem['HAS_CHILD'] === true): ?>
				data-has-child="true"
				<? endif; ?>
				<? if (isset($arItem['IS_DISBANDED']) && $arItem['IS_DISBANDED'] === true): ?>
				data-disbanded="true"
				<? endif; ?>
				title="<?=htmlspecialcharsbx($arItem["TITLE"])?>"><?
				if (!$arItem["HTML"]):
					if (!empty($arItem["URL"])):
						?><a class="main-buttons-item-link<?=$arParams["CLASS_ITEM_LINK"] ? " ".$arParams["CLASS_ITEM_LINK"] : ""?>"
						href="<?=htmlspecialcharsbx($arItem["URL"])?>"><?
					else:
						?><span class="main-buttons-item-link<?=$arParams["CLASS_ITEM_LINK"] ? " ".$arParams["CLASS_ITEM_LINK"] : ""?>"><?
					endif
						?><span class="main-buttons-item-icon<?=$arParams["CLASS_ITEM_ICON"] ? " ".$arParams["CLASS_ITEM_ICON"] : ""?>"></span><?
						?><span class="main-buttons-item-text<?=$arParams["CLASS_ITEM_TEXT"] ? " ".$arParams["CLASS_ITEM_TEXT"] : ""?>"><?
							?><span class="main-buttons-item-drag-button" data-slider-ignore-autobinding="true"></span><?
							if ($arItem['SUPER_TITLE']):
								['TEXT' => $text, 'CLASS' => $className, 'COLOR' => $color] = $arItem['SUPER_TITLE'];
								$className = empty($className) ? '' : ' '. $className;
								$style = empty($color) ? '' : ' style="color:' . $color . '"';
								?><span class="main-buttons-item-super-title<?=$className?>"<?=$style?>><?=$text?></span><?
							endif
							?><span class="main-buttons-item-text-title"><?
								?><span class="main-buttons-item-text-box"><?=htmlspecialcharsbx($title)?><span class="main-buttons-item-menu-arrow"></span></span><?
							?></span><?
							?><span class="main-buttons-item-edit-button" data-slider-ignore-autobinding="true"></span><?
							?><span class="main-buttons-item-text-marker"></span><?
						?></span><?
						?><span
							<?= $dataCounterId ?>
							class="main-buttons-item-counter<?=!empty($arParams["CLASS_ITEM_COUNTER"]) ? (" ".$arParams["CLASS_ITEM_COUNTER"]) : ""?>"><?=(isset($arItem["COUNTER"]) && ($arItem["COUNTER"] > $arItem['MAX_COUNTER_SIZE'])) ? $arItem['MAX_COUNTER_SIZE'].'+' : ($arItem["COUNTER"] ?? '')?></span><?
					if (!empty($arItem["URL"])):
						?></a><?
					else:
						?></span><?
					endif;
					if ($arItem["SUB_LINK"]):
						?><a class="main-buttons-item-sublink<?=" ".($arItem["SUB_LINK"]["CLASS"] ?? '')?>" href="<?=htmlspecialcharsbx($arItem["SUB_LINK"]["URL"])?>"></a><?
					endif;
				else:
					echo $arItem["HTML"];
				endif;
			?></div><?
			if (isset($arItem['HAS_CHILD']) && $arItem['HAS_CHILD'] === true):
				if ($arItem["EXPANDED"]):
					?><div data-is-opened="true" class="main-buttons-item-child main-buttons-item-child-button-cloned">
						<div class="main-buttons-item-child-button"></div>
					</div><?
				endif ?><?
				?><div class="main-buttons-item-child"
					data-id="<?=$arItem["DATA_ID"]?>"
					data-child-items="<?= Converter::getHtmlConverter()->encode(Json::encode($arItem['CHILD_ITEMS']))?>"
					<?=$arItem['EXPANDED'] ? ' data-is-opened="true"' : ''?>
				><?
					?><div class="main-buttons-item-child-list" style="<?=$arItem['EXPANDED'] ? 'max-width: none;opacity: 1; overflow: visible;' : ''?>">
						<div class="main-buttons-item-child-list-inner">
							<? foreach ($arItem["CHILD_ITEMS"] as $childKey => $arChildItem) :
								$itemClass = $arChildItem["CLASS"];

								if ($arChildItem["IS_PASSIVE"])
								{
									$itemClass .= " --passive";
								}
								else if ($arChildItem["IS_ACTIVE"])
								{
									if (isset($arParams["CLASS_ITEM_ACTIVE"]) && strlen($arParams["CLASS_ITEM_ACTIVE"]))
									{
										$itemClass .= " ".$arParams["CLASS_ITEM_ACTIVE"];
									}
									else
									{
										$itemClass .= " main-buttons-item-active";
									}
								}

								if ($arChildItem["IS_LOCKED"])
								{
									$itemClass .= " --locked";
								}

								$dataCounterId = '';
								if (isset($arChildItem['COUNTER_ID']))
								{
									$dataCounterId = 'data-mib-counter-id=' . $arChildItem['COUNTER_ID'];
								}

								$counterValue = '';
								if (isset($arChildItem["COUNTER"]))
								{
									if ($arChildItem["COUNTER"] > $arChildItem['MAX_COUNTER_SIZE'])
									{
										$counterValue = $arChildItem['MAX_COUNTER_SIZE'] . '+';
									}
									else
									{
										$counterValue = $arChildItem["COUNTER"];
									}
								}

								$title = $arChildItem["TEXT"];
								if (mb_strlen($title) > $arParams['MAX_ITEM_LENGTH'])
								{
									$title = mb_substr($title, 0, $arParams['MAX_ITEM_LENGTH'] - 3) . '...';
								}

								?><div
									class="main-buttons-item <?=$itemClass?>"
									id="<?=$arChildItem["ID"]?>"
									data-disabled="<?=$arChildItem["IS_DISABLED"]?>"
									data-class="<?=$arChildItem["CLASS_SUBMENU_ITEM"]?>"
									data-id="<?=$arChildItem["DATA_ID"]?>"
									data-locked="<?=$arChildItem["IS_LOCKED"]?>"
									data-item="<?= Converter::getHtmlConverter()->encode(Json::encode($arChildItem))?>"
									data-top-menu-id="<?=$arResult["ID"]?>"
									<? if (isset($arChildItem['PARENT_ITEM_ID'])) : ?>
										data-parent-item-id="<?=$arChildItem['PARENT_ITEM_ID']?>"
									<? endif;?>
									<? if (isset($arChildItem['HAS_CHILD']) && $arChildItem['HAS_CHILD'] === true): ?>
										data-has-child="true"
									<? endif; ?>
									<? if (isset($arChildItem['IS_DISBANDED']) && $arChildItem['IS_DISBANDED'] === true): ?>
										data-disbanded="true"
									<? endif; ?>
									title="<?=htmlspecialcharsbx($arChildItem["TITLE"])?>"><?
								if (!$arChildItem["HTML"]):
									if (!empty($arChildItem["URL"])):
										?><a class="main-buttons-item-link<?=$arParams["CLASS_ITEM_LINK"] ? " ".$arParams["CLASS_ITEM_LINK"] : ""?>"
										href="<?=htmlspecialcharsbx($arChildItem["URL"])?>"><?
									else:
										?><span class="main-buttons-item-link<?=$arParams["CLASS_ITEM_LINK"] ? " ".$arParams["CLASS_ITEM_LINK"] : ""?>"><?
									endif;

									?><span class="main-buttons-item-icon<?=$arParams["CLASS_ITEM_ICON"] ? " ".$arParams["CLASS_ITEM_ICON"] : ""?>"></span><?
									?><span class="main-buttons-item-text<?=$arParams["CLASS_ITEM_TEXT"] ? " ".$arParams["CLASS_ITEM_TEXT"] : ""?>"><?
										?><span class="main-buttons-item-drag-button" data-slider-ignore-autobinding="true"></span><?
										if ($arChildItem['SUPER_TITLE']):
											['TEXT' => $text, 'CLASS' => $className, 'COLOR' => $color] = $arChildItem['SUPER_TITLE'];
											$className = empty($className) ? '' : ' '. $className;
											$style = empty($color) ? '' : ' style="color:' . $color . '"';
											?><span class="main-buttons-item-super-title<?=$className?>"<?=$style?>><?=$text?></span><?
										endif
										?><span class="main-buttons-item-text-title"><?
											?><span class="main-buttons-item-text-box"><?=htmlspecialcharsbx($title)?></span><?
										?></span><?
										?><span class="main-buttons-item-edit-button" data-slider-ignore-autobinding="true"></span><?
										?><span class="main-buttons-item-text-marker"></span><?
									?></span><?
									?><span
										<?= $dataCounterId ?>
										class="main-buttons-item-counter<?=$arParams["CLASS_ITEM_COUNTER"] ? " ".$arParams["CLASS_ITEM_COUNTER"] : ""?>"><?=$counterValue?></span><?
									if (!empty($arChildItem["URL"])):
										?></a><?
									else:
										?></span><?
									endif;

									if ($arChildItem["SUB_LINK"]):
										?><a class="main-buttons-item-sublink<?=" ".($arChildItem["SUB_LINK"]["CLASS"] ?? '')?>" href="<?=htmlspecialcharsbx($arChildItem["SUB_LINK"]["URL"])?>"></a><?
									endif;
								else:
									echo $arChildItem["HTML"];
								endif;
								?></div><?
							endforeach
						?></div><?
					?></div><?
					?><div class="main-buttons-item-child-button"></div><?
				?></div><?
				endif;
			endforeach;
		?></div>
		<div class="main-buttons-item <?=$arResult["MORE_BUTTON"]["CLASS"]?> main-buttons-item-more --has-menu" id="<?=$arResult["ID"]?>_more_button"<?=$arParams["DISABLE_SETTINGS"] ? " style=\"display: none;\"" : ""?>>
		<? if (!$arResult["MORE_BUTTON"]["HTML"]):
			?><span class="main-buttons-item-link<?=$arParams["CLASS_ITEM_LINK"] ? " ".$arParams["CLASS_ITEM_LINK"] : ""?>"><?
				?><span class="main-buttons-item-icon<?=$arParams["CLASS_ITEM_ICON"] ? " ".$arParams["CLASS_ITEM_ICON"] : ""?>"></span><?
				?><span class="main-buttons-item-text<?=$arParams["CLASS_ITEM_TEXT"] ? " ".$arParams["CLASS_ITEM_TEXT"] : ""?>"><?
					?><span class="main-buttons-item-text-title"><?
						?><span class="main-buttons-item-text-box"><?=$arResult["MORE_BUTTON"]["TEXT"]?><span class="main-buttons-item-menu-arrow"></span></span><?
					?></span><?
				?></span><?
				?><span class="main-buttons-item-counter"></span><?
			?></span>
		<? else : ?>
			<?=$arResult["MORE_BUTTON"]["HTML"]?>
		<? endif; ?>
	</div>
	</div>
	<iframe height="100%" width="100%" id="maininterfacebuttons-tmp-frame-<?=$arResult["ID"]?>" name="maininterfacebuttonstmpframe-<?=$arResult["ID"]?>" style="position: absolute; z-index: -1; opacity: 0;"></iframe>
</div>

<script>
(function() {
	const init = () => {
		BX.Main.interfaceButtonsManager.init({
			containerId: '<?=$arResult["ID"]?>',
			disableSettings: '<?= Json::encode($arParams["DISABLE_SETTINGS"])?>',
			theme: '<?=CUtil::JSEscape($arParams["THEME"])?>',
			maxItemLength: <?=(int)$arParams['MAX_ITEM_LENGTH']?>,
			ajaxSettings: {
				componentName: '<?= $this->getComponent()->getName() ?>',
				signedParams: '<?= $this->getComponent()->getSignedParameters() ?>',
			},
			classes: {
				itemActive: '<?=$arParams['CLASS_ITEM_ACTIVE']?>',
				extraItemLink: '<?=$arParams['CLASS_ITEM_LINK']?>',
				extraItemText: '<?=$arParams['CLASS_ITEM_TEXT']?>',
				extraItemIcon: '<?=$arParams['CLASS_ITEM_ICON']?>',
				extraItemCounter: '<?=$arParams['CLASS_ITEM_COUNTER']?>',
			},
			licenseWindow: {
				isFullDemoExists: 'Y',
				hostname: '',
				ajaxUrl: '',
				licenseAllPath: '',
				licenseDemoPath: '',
				featureGroupname: '',
				ajaxActionsUrl: ''
			},
			messages: {
				MIB_DROPZONE_TEXT: '<?=CUtil::JSEscape(Loc::getMessage("MIB_DROPZONE_TEXT"))?>',
				MIB_LICENSE_BUY_BUTTON: '<?=CUtil::JSEscape(Loc::getMessage("MIB_LICENSE_BUY_BUTTON"))?>',
				MIB_LICENSE_TRIAL_BUTTON: '<?=CUtil::JSEscape(Loc::getMessage("MIB_LICENSE_TRIAL_BUTTON"))?>',
				MIB_LICENSE_WINDOW_HEADER_TEXT: '<?=CUtil::JSEscape(Loc::getMessage("MIB_LICENSE_WINDOW_HEADER_TEXT"))?>',
				MIB_LICENSE_WINDOW_TEXT: '<?=CUtil::JSEscape(Loc::getMessage("MIB_LICENSE_WINDOW_TEXT"))?>',
				MIB_LICENSE_WINDOW_TRIAL_SUCCESS_TEXT: '',
				MIB_SETTING_MENU_ITEM: '<?=CUtil::JSEscape(Loc::getMessage("MIB_SETTING_MENU_ITEM"))?>',
				MIB_APPLY_SETTING_MENU_ITEM: '<?=CUtil::JSEscape(Loc::getMessage("MIB_APPLY_SETTING_MENU_ITEM"))?>',
				MIB_SET_HOME: '<?=CUtil::JSEscape(Loc::getMessage("MIB_SET_HOME"))?>',
				MIB_SET_HIDE: '<?=CUtil::JSEscape(Loc::getMessage("MIB_SET_HIDE"))?>',
				MIB_SET_SHOW: '<?=CUtil::JSEscape(Loc::getMessage("MIB_SET_SHOW"))?>',
				MIB_RESET_SETTINGS: '<?=CUtil::JSEscape(Loc::getMessage("MIB_RESET_SETTINGS"))?>',
				MIB_HIDDEN: '<?=CUtil::JSEscape(Loc::getMessage("MIB_HIDDEN"))?>',
				MIB_MANAGE: '<?=CUtil::JSEscape(Loc::getMessage("MIB_MANAGE"))?>',
				MIB_NO_HIDDEN: '<?=CUtil::JSEscape(Loc::getMessage("MIB_NO_HIDDEN"))?>',
				MIB_RESET_ALERT: '<?=CUtil::JSEscape(Loc::getMessage("MIB_RESET_ALERT"))?>',
				MIB_RESET_BUTTON: '<?=CUtil::JSEscape(Loc::getMessage("MIB_RESET_BUTTON"))?>',
				MIB_CANCEL_BUTTON: '<?=CUtil::JSEscape(Loc::getMessage("MIB_CANCEL_BUTTON"))?>',
				MIB_MAIN_BUTTONS_LOADING: '<?=CUtil::JSEscape(Loc::getMessage("MIB_MAIN_BUTTONS_LOADING"))?>',
				MIB_UNPIN_ITEM: '<?=CUtil::JSEscape(Loc::getMessage("MIB_UNPIN_ITEM"))?>',
				MIB_PIN_HINT: '<?=CUtil::JSEscape(Loc::getMessage("MIB_PIN_HINT"))?>',
			}
		});
	};

	const isReady = document.getElementById('<?=$arResult["ID"]?>');
	if (isReady)
	{
		init();
	}
	else
	{
		BX.Event.ready(() => {
			init();
		});
	}
})();

</script>
