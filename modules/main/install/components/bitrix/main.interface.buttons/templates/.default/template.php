<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)die();
use \Bitrix\Main\Localization\Loc;

CJSCore::Init(array('ajax', 'popup'));

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

<div class="main-buttons">
	<div class="main-buttons-inner-container" id="<?=$arResult["ID"]?>">
		<? foreach ($arResult["ITEMS"] as $key => $arItem) :
			$itemClass = $arItem["CLASS"];
			if ($arItem["IS_ACTIVE"])
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
		?>
			<div
				 class="main-buttons-item <?=$itemClass?>"
				 id="<?=$arItem["ID"]?>"
				 data-disabled="<?=$arItem["IS_DISABLED"]?>"
				 data-class="<?=$arItem["CLASS_SUBMENU_ITEM"]?>"
				 data-onclick="<?=$arItem["ON_CLICK"]?>"
				 data-url="<?=$arItem["URL"]?>"
				 data-text="<?=$arItem["TEXT"]?>"
				 data-id="<?=$arItem["DATA_ID"]?>"
				 data-counter="<?=$arItem["COUNTER"]?>"
				 data-counter-id="<?=$arItem["COUNTER_ID"]?>"
				 data-locked="<?=$arItem["IS_LOCKED"]?>"
				 data-item="<?=\Bitrix\Main\Text\Converter::getHtmlConverter()->encode(\Bitrix\Main\Web\Json::encode($arItem))?>"
				 data-top-menu-id="<?=$arResult["ID"]?>"
				 title="<?=isset($arItem["TITLE"]) ? $arItem["TITLE"] : ""?>">
				<? if (!$arItem["HTML"]) :?>
					<a class="main-buttons-item-link<?=$arParams["CLASS_ITEM_LINK"] ? " ".$arParams["CLASS_ITEM_LINK"] : ""?>"
					   href="<?=$arItem["URL"]?>">
						<span class="main-buttons-item-icon<?=$arParams["CLASS_ITEM_ICON"] ? " ".$arParams["CLASS_ITEM_ICON"] : ""?>"></span><?
						?><span class="main-buttons-item-text<?=$arParams["CLASS_ITEM_TEXT"] ? " ".$arParams["CLASS_ITEM_TEXT"] : ""?>">
							<span class="main-buttons-item-edit-button"></span>
							<span class="main-buttons-item-text-title"><?=$arItem["TEXT"]?></span>
							<span class="main-buttons-item-drag-button"></span>
							<span class="main-buttons-item-text-marker"></span>
						</span><?
						?><span class="main-buttons-item-counter<?=$arParams["CLASS_ITEM_COUNTER"] ? " ".$arParams["CLASS_ITEM_COUNTER"] : ""?>"><?=$arItem["COUNTER"] > $arItem['MAX_COUNTER_SIZE'] ? $arItem['MAX_COUNTER_SIZE'].'+' : $arItem["COUNTER"]?></span>
					</a>
					<? if ($arItem["SUB_LINK"]) : ?>
						<a class="main-buttons-item-sublink<?=" ".$arItem["SUB_LINK"]["CLASS"]?>" href="<?=$arItem["SUB_LINK"]["URL"]?>"></a>
					<? endif; ?>
				<? else : ?>
					<?=$arItem["HTML"]?>
				<? endif; ?>
			</div><!--main-buttons-item-->
		<? endforeach; ?>
		<div class="main-buttons-item <?=$arResult["MORE_BUTTON"]["CLASS"]?> main-buttons-item-more" id="<?=$arResult["ID"]?>_more_button"<?=$arParams["DISABLE_SETTINGS"] ? " style=\"display: none;\"" : ""?>>
			<? if (!$arResult["MORE_BUTTON"]["HTML"]) : ?>
				<a href="#" class="main-buttons-item-link<?=$arParams["CLASS_ITEM_LINK"] ? " ".$arParams["CLASS_ITEM_LINK"] : ""?>">
					<span class="main-buttons-item-icon<?=$arParams["CLASS_ITEM_ICON"] ? " ".$arParams["CLASS_ITEM_ICON"] : ""?>"></span><?
					?><span class="main-buttons-item-text<?=$arParams["CLASS_ITEM_TEXT"] ? " ".$arParams["CLASS_ITEM_TEXT"] : ""?>"><?=$arResult["MORE_BUTTON"]["TEXT"]?></span>
					<span class="main-buttons-item-counter"></span>
				</a>
			<? else : ?>
				<?=$arResult["MORE_BUTTON"]["HTML"]?>
			<? endif; ?>
		</div><!--main-buttons-item-->
	</div><!--main-buttons-inner-container-->
	<iframe height="100%" width="100%" id="maininterfacebuttons-tmp-frame-<?=$arResult["ID"]?>" name="maininterfacebuttonstmpframe-<?=$arResult["ID"]?>" style="position: absolute; z-index: -1; opacity: 0;"></iframe>
</div><!--main-buttons-->

<script>
	BX.Main.interfaceButtonsManager.init({
		containerId: '<?=$arResult["ID"]?>',
		disableSettings: '<?=CUtil::PhpToJSObject($arParams["DISABLE_SETTINGS"])?>',
		classes: {
			itemMore: 'main-buttons-item-more',
			itemActive: '<?=$arParams["CLASS_ITEM_ACTIVE"]?>'
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
			MIB_CANCEL_BUTTON: '<?=CUtil::JSEscape(Loc::getMessage("MIB_CANCEL_BUTTON"))?>'
		}
	});
</script>