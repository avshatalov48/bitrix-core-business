<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var string $templateFolder
 */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Web\Json;

Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.entity-selector',
	'ui.buttons',
	'ai.copilot',
]);

Main\Page\Asset::getInstance()->addJs($templateFolder."/index.js");
include_once(__DIR__."/functions.php");
include_once(__DIR__."/message.php");
include(__DIR__."/file.php");
include(__DIR__."/urlpreview.php");

CUtil::InitJSCore([ 'fx', 'ui.cnt']);
$controlId = htmlspecialcharsbx($arParams["divId"]);

?><div class="feed-add-post" id="div<?=$controlId?>" <?=($arParams["LHE"]["lazyLoad"] ? ' style="display:none;"' : '')?>>
	<?php if ($arParams['isDnDEnabled']): ?>
	<div class="feed-add-post-dnd-notice">
		<div class="feed-add-post-dnd-inner">
			<span class="feed-add-post-dnd-icon"></span>
			<span class="feed-add-post-dnd-text"><?=GetMessage("MPF_DRAG_ATTACHMENTS")?></span>
		</div>
	</div>
	<?php endif; ?>
	<div class="feed-add-post-form feed-add-post-edit-form">
		<?= $arParams["~HTML_BEFORE_TEXTAREA"] ?? ''?>
		<div class="feed-add-post-text">
<script>
<?
if (isset($GLOBALS["arExtranetGroupID"]) && is_array($GLOBALS["arExtranetGroupID"]))
{
	?>
	if (typeof window['arExtranetGroupID'] == 'undefined')
	{
		window['arExtranetGroupID'] = <?= Json::encode($GLOBALS["arExtranetGroupID"])?>;
	}
	<?
}
?>
BX.ready(function()
{
	BX.message(<?= Json::encode(Main\Localization\Loc::loadLanguageFile(__DIR__."/editor.php")) ?>);
	<?if ($arParams["JS_OBJECT_NAME"] !== ""): ?>window['<?=$arParams["JS_OBJECT_NAME"]?>'] = <? endif; ?>
	new BX.Main.PostForm(
		{
			id: '<?=CUtil::JSEscape($arParams["LHE"]["id"])?>',
			name: '<?=CUtil::JSEscape($arParams["LHE"]["jsObjName"])?>',
			formId: '<?=CUtil::JSEscape($arParams["FORM_ID"])?>',
			eventNode: BX('div<?=CUtil::JSEscape($controlId)?>'),
		},
		<?= Json::encode([
			'ctrlEnterHandler' => $arParams["LHE"]['ctrlEnterHandler'] ?? '',
			'showPanelEditor' => isset($arParams["TEXT"]["SHOW"]) && $arParams["TEXT"]["SHOW"] === "Y",
			'lazyLoad' => !!$arParams["LHE"]['lazyLoad'],
			'urlPreviewId' => $arParams['urlPreviewId'] ?? '',
			'parsers' => $arParams["PARSER"],
			'isDnDEnabled' => $arParams['isDnDEnabled'],
			'tasksLimitExceeded' => !!$arResult['tasksLimitExceeded'],
		]); ?>,
		<?= Json::encode(
			array(
				"arSize" => $arParams["UPLOAD_FILE_PARAMS"] ?? null,
				"CID" => $arParams["UPLOADS_CID"],
			)); ?>
	);
});
</script>
<?php
$visibleButtons = include(__DIR__.'/lhe.php');
?>
			<div style="display:none;"><input type="text" tabindex="<?=($arParams["TEXT"]["TABINDEX"]++)?>" onFocus="LHEPostForm.getEditor('<?=$arParams["LHE"]["id"]?>').Focus()" name="hidden_focus" /></div>
		</div>
		<div class="main-post-form-toolbar">
			<div class="main-post-form-toolbar-buttons">
				<div class="main-post-form-toolbar-buttons-container" data-bx-role="toolbar"><?php
				foreach ($visibleButtons as $key => $item)
				{
					?><div class="main-post-form-toolbar-button"
					       data-bx-role="toolbar-item"
					       id="mpf-<?=$item["ID"]?>-<?=$arParams["FORM_ID"]?>"
					       data-id="<?=$item["ID"]?>">
						<?=$item["HTML"]?>
					</div><?php
				}
					?>
				</div>
				<div class="main-post-form-toolbar-button main-post-form-toolbar-button-more" data-bx-role="toolbar-item-more" style="display: none;"></div>
			</div>
			<?

			if(!empty($arParams["ADDITIONAL"]))
			{
				if ($arParams["ADDITIONAL_TYPE"] == "popup")
				{
					?><div class="feed-add-post-form-but-more" <?
						?>onclick="BX.PopupMenu.show('menu-more<?=$arParams["FORM_ID"]?>', this, [<?=implode(", ", $arParams["ADDITIONAL"])?>], {offsetLeft: 42, offsetTop: 3, lightShadow: false, angle: top, events : {onPopupClose : function(popupWindow) {BX.removeClass(this.bindElement, 'feed-add-post-form-but-more-act');}}}); BX.addClass(this, 'feed-add-post-form-but-more-act');"><?
						?><?=GetMessage("MPF_MORE")?><?
						?><div class="feed-add-post-form-but-arrow"></div><?
					?></div><?
				}
				else if (count($arParams["ADDITIONAL"]) < 5)
				{
					?><div class="feed-add-post-form-but-more-open"><?
						?><?=implode("", $arParams["ADDITIONAL"])?>
					</div><?
				}
				else
				{
					foreach($arParams["ADDITIONAL"] as $key => $val)
					{
						$arParams["ADDITIONAL"][$key] = array("text" => $val, "onclick" => "BX.PopupMenu.Data['menu-more".$arParams["FORM_ID"]."'].popupWindow.close();");
					}
					?><script>window['more<?=$arParams["FORM_ID"]?>']=<?= Json::encode($arParams["ADDITIONAL"]) ?>;</script><?
					?><div class="feed-add-post-form-but-more" <?
						?>onclick="BX.PopupMenu.show('menu-more<?=$arParams["FORM_ID"]?>', this, window['more<?=$arParams["FORM_ID"]?>'], {offsetLeft: 42, offsetTop: 3, lightShadow: false, angle: top, events : {onPopupClose : function(popupWindow) {BX.removeClass(this.bindElement, 'feed-add-post-form-but-more-act');}}}); BX.addClass(this, 'feed-add-post-form-but-more-act');"><?
						?><?=GetMessage("MPF_MORE")?><?
						?><div class="feed-add-post-form-but-arrow"></div><?
					?></div><?
				}
			}
		?></div>
	</div><?php

	echo $arParams["~HTML_AFTER_TEXTAREA"] ?? '';

	if (isset($visibleButtons['MentionUser']))
	{
		if (defined("BITRIX24_INDEX_COMPOSITE"))
		{
			$dynamicArea = new \Bitrix\Main\Page\FrameStatic("blogpostform-init");
			$dynamicArea->startDynamicArea();
			$dynamicArea->setStub('');
		}
		CModule::IncludeModule('intranet'); // for gov/public messages

		$mentionSelectorId = 'mention_'.randString(6);

		?><span id="bx-mention-<?=$arParams["FORM_ID"]?>-id" data-bx-selector-id="<?=htmlspecialcharsbx($mentionSelectorId)?>"></span><?

		?><script>
			BX.ready(function(){
				window.MPFMentionInit('<?=$arParams["FORM_ID"]?>', {
					editorId: '<?= $arParams["LHE"]["id"]?>',
					id: '<?=$this->randString(6)?>',
					initDestination: <?=($arParams["DESTINATION_SHOW"] == "Y" ? "true" : "false")?>,
					entities: <?= Json::encode($arResult['MENTION_ENTITIES']) ?>,
				});
			});
		</script>
		<?php
		if (defined("BITRIX24_INDEX_COMPOSITE"))
		{
			$dynamicArea->finishDynamicArea();
		}
	}

	/***************** Upload files ************************************/
	echo $arParams["UPLOADS_HTML"];

	if (!empty($arParams["TAGS"]))
	{
		$tagsInput = [];
		$tags = array_map(function($val) use (&$tagsInput) {
			if (($val = trim($val)) <> '')
			{
				$val = htmlspecialcharsbx($val);
				$tagsInput[] = $val;
				return '<span class="feed-add-post-tags" data-tag="'.$val.'">'
					.$val.'<span class="feed-add-post-del-but"></span></span>';
			}
			return null;
		}, $arParams["TAGS"]["VALUE"]);

		?>
		<div id="post-tags-block-<?=$arParams["FORM_ID"]?>" class="feed-add-post-strings-blocks feed-add-post-tags-block"<?if (sizeof($tagsInput) > 0):?> style="display:block"<?endif?>>
			<div class="feed-add-post-tags-title"><?=GetMessage("MPF_TAGS")?></div>
			<div class="feed-add-post-tags-wrap" id="post-tags-container-<?=$arParams["FORM_ID"]?>">
				<?=implode('', $tags)?>
				<span class="feed-add-post-tags-add" id="post-tags-add-new-<?=$arParams["FORM_ID"]?>"><?=GetMessage("MPF_ADD_TAG")?></span>
				<input type="hidden" name="<?=$arParams["TAGS"]["NAME"]?>" id="post-tags-hidden-<?=$arParams["FORM_ID"]?>" value="<?=implode(",", $tagsInput)?>,">
			</div>
			<div id="post-tags-popup-content-<?=$arParams["FORM_ID"]?>" style="display:none;">
				<?php
				if($arParams["TAGS"]["USE_SEARCH"] == "Y" && ModuleManager::isModuleInstalled('search'))
				{
					$APPLICATION->IncludeComponent(
						"bitrix:search.tags.input",
						".default",
						Array(
							"NAME"	=>	$arParams["TAGS"]["NAME"]."_".$arParams["FORM_ID"],
							"VALUE"	=>	"",
							"arrFILTER"	=>	$arParams["TAGS"]["FILTER"],
							"PAGE_ELEMENTS"	=>	"10",
							"SORT_BY_CNT"	=>	"Y",
							"TEXT" => 'size="30" tabindex="'.($arParams["TEXT"]["TABINDEX"]++).'"',
							"ID" => "post-tags-popup-input-".$arParams["FORM_ID"]
						),
						false,
						array("HIDE_ICONS" => "Y")
					);
				}
				else
				{
					?><input type="text" id="post-tags-popup-input-<?=$arParams["FORM_ID"]?>" tabindex="<?=($arParams["TEXT"]["TABINDEX"]++)?>" name="<?=$arParams["TAGS"]["NAME"]."_".$arParams["FORM_ID"]?>" size="30" value=""><?
				}?>
			</div>
		</div>
		<?
	}

	if($arParams["DESTINATION_SHOW"] === "Y")
	{
		?>
		<div class="feed-add-post-strings-blocks feed-add-post-destination-block">
			<input type="hidden" id="entity-selector-data-<?=$controlId?>" name="DEST_DATA" value="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($arResult['DESTINATION']['ENTITIES_PRESELECTED']))?>" />
			<div class="feed-add-post-destination-title"><?=GetMessage("MPF_DESTINATION")?></div>
			<div id="entity-selector-<?=$controlId?>"></div>
<script>
	BX.ready(function()
	{
		new MPFEntitySelector({
			id: '<?=CUtil::JSescape($arParams["divId"])?>',
			context: '<?=CUtil::JSescape(
				!empty($arParams['SELECTOR_CONTEXT'])
					? $arParams['SELECTOR_CONTEXT']
					: (
				!empty($arParams['DEST_CONTEXT'])
					? $arParams['DEST_CONTEXT']
					: 'BLOG_POST'
				)
			)?>',
			tagNodeId: 'entity-selector-<?=CUtil::JSescape($arParams["divId"])?>',
			inputNodeId: 'entity-selector-data-<?=CUtil::JSescape($arParams["divId"])?>',
			preselectedItems: <?= CUtil::PhpToJSObject($arResult['DESTINATION']['ENTITIES_PRESELECTED']) ?>,
			allowSearchEmailUsers: <?=($arResult['ALLOW_EMAIL_INVITATION'] ? 'true' : 'false')?>,
			collabers: false,
			allowToAll: <?=($arResult['ALLOW_TO_ALL'] ? 'true' : 'false')?>,
			messages: {
				allUsersTitle: '<?= CUtil::JSescape(ModuleManager::isModuleInstalled('intranet') ? Loc::getMessage('MPF_DESTINATION_3') : Loc::getMessage('MPF_DESTINATION_4')) ?>',
			},
		});
	});
</script>
		</div><?

		echo $APPLICATION->GetViewContent("mpl_input_additional");
	}

	echo $arParams["~AT_THE_END_HTML"] ?? '';
	echo $arParams["URL_PREVIEW_HTML"] ?? '';

	if (isset($arParams["IMPORTANT"]) && isset($arParams["IMPORTANT"]["INPUT_NAME"]))
	{
?>
<script>
	var BXPostFormImportant_<?=$arParams["FORM_ID"]?> = new BXPostFormImportant("<?=$arParams["IMPORTANT"]["INPUT_NAME"]?>");
</script>
	<?php
	}
	?>
	<div class="feed-add-post-buttons --no-wrap" id="lhe_buttons_<?=$arParams["FORM_ID"]?>">
		<button class="ui-btn ui-btn-sm ui-btn-primary" id="lhe_button_submit_<?=$arParams["FORM_ID"]?>"><?=GetMessage("MPF_BUTTON_SEND")?></button>
		<button class="ui-btn ui-btn-sm ui-btn-link" id="lhe_button_cancel_<?=$arParams["FORM_ID"]?>"><?=GetMessage("MPF_BUTTON_CANCEL")?></button>

		<?= $APPLICATION->GetViewContent("mpf_extra_buttons"); ?>
	</div>
</div>
