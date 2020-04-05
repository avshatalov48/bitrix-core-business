<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

use \Bitrix\Main\UI;

UI\Extension::load("ui.buttons");

include_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/functions.php");
include_once($_SERVER["DOCUMENT_ROOT"].$templateFolder."/message.php");
include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/file.php");
include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/urlpreview.php");
$array = (((!empty($arParams["DESTINATION"]) || in_array("MentionUser", $arParams["BUTTONS"])) && IsModuleInstalled("socialnetwork")) ?
	array('socnetlogdest') : array());
$array[] = "fx";
CUtil::InitJSCore($array);
$arButtonsHTML = array();

foreach($arParams["BUTTONS"] as $val)
{
	switch($val)
	{
		case "CreateLink":
			$arButtonsHTML[] = '<span class="feed-add-post-form-but-cnt" id="bx-b-link-'.$arParams["FORM_ID"].'"></span>';
			break;
		case "UploadImage":
		case "UploadFile":
			$arButtonsHTML["Upload"] = '<span class="feed-add-post-form-but feed-add-file" id="bx-b-uploadfile-'.$arParams["FORM_ID"].'" '.
					'title="'.GetMessage('MPF_FILE_TITLE').'"></span>';
			break;
		case "InputVideo":
			$arButtonsHTML[] = '<span class="feed-add-post-form-but-cnt" id="bx-b-video-'.$arParams["FORM_ID"].'"></span>';
			break;
		case "InputTag":
			$arButtonsHTML[] = '<span class="feed-add-post-form-but feed-add-tag" id="bx-b-tag-input-'.$arParams["FORM_ID"].'" '.
				'title="'.GetMessage("MPF_TAG_TITLE").'"></span>';
			break;
		case "MentionUser":
			$arButtonsHTML[] = '<span class="feed-add-post-form-but feed-add-mention" id="bx-b-mention-'.$arParams["FORM_ID"].'" '.
				'title="'.GetMessage("MPF_MENTION_TITLE").'"></span>';
			break;
		case "Quote":
			$arButtonsHTML[] = '<span class="feed-add-post-form-but-cnt" id="bx-b-quote-'.$arParams["FORM_ID"].'"></span>';
			break;
		case "Important":
			$arButtonsHTML[] = '<span class="feed-add-post-form-but feed-add-important" id="bx-b-important-'.$arParams["FORM_ID"].'" '.
				'title="'.GetMessage("MPF_IMPORTANT_TITLE").'"></span>'.
				'<span id="bx-b-important-'.$arParams["FORM_ID"].'-active" class="feed-add-important-active-block" style="display: none;"><span class="feed-add-post-form-but feed-add-important-active"></span><span class="feed-add-important-text">'.GetMessage('MPF_IMPORTANT_TITLE').'</span></span>';
			break;
		default:
			if (isset($arParams["~BUTTONS_HTML"]) && is_array($arParams["~BUTTONS_HTML"]) && is_array($arParams["~BUTTONS_HTML"]) && array_key_exists($val, $arParams["~BUTTONS_HTML"]))
				$arButtonsHTML[] = $arParams["~BUTTONS_HTML"][$val];
			break;
	}
}

?><div class="feed-add-post" id="div<?=$arParams["divId"]?>" <?if($arParams["LHE"]["lazyLoad"]):?> style="display:none;"<? endif; ?>><?
	?><div class="feed-add-post-dnd">
		<div class="feed-add-post-dnd-inner">
			<span class="feed-add-post-dnd-icon"></span>
			<span class="feed-add-post-dnd-text"><?=GetMessage("MPF_SELECT_ATTACHMENTS")?><span><?=GetMessage("MPF_DROP_ATTACHMENTS")?></span></span>
		</div>
	</div><?
	?><div class="feed-add-post-dnd-notice">
		<div class="feed-add-post-dnd-inner">
			<span class="feed-add-post-dnd-icon"></span>
			<span class="feed-add-post-dnd-text"><?=GetMessage("MPF_DRAG_ATTACHMENTS")?></span>
		</div>
	</div><?
	?><div class="feed-add-post-form feed-add-post-edit-form">
		<?= $arParams["~HTML_BEFORE_TEXTAREA"]?>
		<div class="feed-add-post-text">
			<script type="text/javascript">
				<?
				if (is_array($GLOBALS["arExtranetGroupID"]))
				{
					?>
					if (typeof window['arExtranetGroupID'] == 'undefined')
					{
						window['arExtranetGroupID'] = <?=CUtil::PhpToJSObject($GLOBALS["arExtranetGroupID"])?>;
					}
					<?
				}
				?>
				BX.ready(function()
				{
					if (!LHEPostForm.getHandler('<?=$arParams["LHE"]["id"]?>'))
					{
						<?if ($arParams["JS_OBJECT_NAME"] !== ""): ?>window['<?=$arParams["JS_OBJECT_NAME"]?>'] = <? endif; ?>new LHEPostForm(
							'<?=$arParams["FORM_ID"]?>',
							<?=CUtil::PhpToJSObject(
								array(
									"LHEJsObjId" => $arParams["LHE"]["id"],
									"LHEJsObjName" => $arParams["LHE"]["jsObjName"],
									"arSize" => $arParams["UPLOAD_FILE_PARAMS"],
									"CID" => $arParams["UPLOADS_CID"],
									'parsers' => $arParams["PARSER"],
									'showPanelEditor' => ($arParams["TEXT"]["SHOW"] == "Y"),
									'pinEditorPanel' => ($arParams["PIN_EDITOR_PANEL"] == "Y"),
									'formID' => $arParams["FORM_ID"],
									'lazyLoad' => !!$arParams["LHE"]['lazyLoad'],
									'ctrlEnterHandler' => $arParams["LHE"]['ctrlEnterHandler'],
									'urlPreviewId' => $arParams['urlPreviewId']
								));?>
						);
					}
					else
					{
						BX.debug('LHEPostForm <?=$arParams["LHE"]["id"]?> has already existed.');
					}
				});
			</script>
			<?
			include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/lhe.php");
			?>
			<div style="display:none;"><input type="text" tabindex="<?=($arParams["TEXT"]["TABINDEX"]++)?>" onFocus="LHEPostForm.getEditor('<?=$arParams["LHE"]["id"]?>').SetFocus()" name="hidden_focus" /></div>
		</div>
		<div class="feed-add-post-form-but-wrap" id="post-buttons-bottom"><?=implode("", $arButtonsHTML);
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
					?><script type="text/javascript">window['more<?=$arParams["FORM_ID"]?>']=<?=CUtil::PhpToJSObject($arParams["ADDITIONAL"])?>;</script><?
					?><div class="feed-add-post-form-but-more" <?
						?>onclick="BX.PopupMenu.show('menu-more<?=$arParams["FORM_ID"]?>', this, window['more<?=$arParams["FORM_ID"]?>'], {offsetLeft: 42, offsetTop: 3, lightShadow: false, angle: top, events : {onPopupClose : function(popupWindow) {BX.removeClass(this.bindElement, 'feed-add-post-form-but-more-act');}}}); BX.addClass(this, 'feed-add-post-form-but-more-act');"><?
						?><?=GetMessage("MPF_MORE")?><?
						?><div class="feed-add-post-form-but-arrow"></div><?
					?></div><?
				}
			}
		?></div>
	</div>
	<?=$arParams["~HTML_AFTER_TEXTAREA"]?><?
	if($arParams["DESTINATION_SHOW"] == "Y" || !empty($arParams["TAGS"]))
	{
		?><ol class="feed-add-post-strings-blocks"><?
	}

	if($arParams["DESTINATION_SHOW"] == "Y")
	{
		?>
		<li class="feed-add-post-destination-block">
			<div class="feed-add-post-destination-title"><?=GetMessage("MPF_DESTINATION")?></div>
			<div class="feed-add-post-destination-wrap" id="feed-add-post-destination-container">
				<span id="feed-add-post-destination-item"></span>
				<span class="feed-add-destination-input-box" id="feed-add-post-destination-input-box">
					<input type="text" value="" class="feed-add-destination-inp" id="feed-add-post-destination-input" autocomplete="off">
				</span>
				<a href="#" class="feed-add-destination-link" id="bx-destination-tag"></a>
			</div>
		</li>
		<?
		echo $APPLICATION->GetViewContent("mpl_input_additional");
	}

	if (!empty($arParams["TAGS"]))
	{
		$tags = "";
		$tagsInput = "";
		foreach($arParams["TAGS"]["VALUE"] as $val)
		{
			$val = trim($val);
			if(strlen($val) > 0)
			{
				$tags .= '<span class="feed-add-post-tags" data-tag="'.htmlspecialcharsbx($val).'">'.htmlspecialcharsEx($val);
				$tags .= '<span class="feed-add-post-del-but"></span></span>';

				if ($tagsInput != "")
				{
					$tagsInput .= ",";
				}
				$tagsInput .= htmlspecialcharsbx($val);
			}
		}
		?>
		<li id="post-tags-block-<?=$arParams["FORM_ID"]?>" class="feed-add-post-tags-block"<?if ($tags !== ""):?> style="display:block"<?endif?>>
			<div class="feed-add-post-tags-title"><?=GetMessage("MPF_TAGS")?></div>
			<div class="feed-add-post-tags-wrap" id="post-tags-container-<?=$arParams["FORM_ID"]?>">
				<?=$tags?>
				<span class="feed-add-post-tags-add" id="post-tags-add-new-<?=$arParams["FORM_ID"]?>"><?=GetMessage("MPF_ADD_TAG")?></span>
				<input type="hidden" name="<?=$arParams["TAGS"]["NAME"]?>" id="post-tags-hidden-<?=$arParams["FORM_ID"]?>" value="<?=$tagsInput?>,">
			</div>
		<div id="post-tags-popup-content-<?=$arParams["FORM_ID"]?>" style="display:none;">
		<?if($arParams["TAGS"]["USE_SEARCH"] == "Y" && IsModuleInstalled("search"))
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
			?><input type="text" id="post-tags-popup-input-<?=$arParams["FORM_ID"]?>" tabindex="<?=($arParams["TEXT"]["TABINDEX"]++)?>" name="<?=$arParams["TAGS"]["NAME"]?>" size="30" value=""><?
		}?>
		</div>
		<script type="text/javascript">
			var BXPostFormTags_<?=$arParams["FORM_ID"]?> = new BXPostFormTags("<?=$arParams["FORM_ID"]?>", "bx-b-tag-input-<?=$arParams["FORM_ID"]?>");
			var BXPostFormImportant_<?=$arParams["FORM_ID"]?> = new BXPostFormImportant("<?=$arParams["FORM_ID"]?>", "bx-b-important-<?=$arParams["FORM_ID"]?>", <?=(isset($arParams["IMPORTANT"]) && isset($arParams["IMPORTANT"]["INPUT_NAME"]) ? '"'.$arParams["IMPORTANT"]["INPUT_NAME"].'"' : 'false')?>);
		</script>
		</li>
		<?
	}
	if($arParams["DESTINATION_SHOW"] == "Y" || !empty($arParams["TAGS"]))
	{
		?></ol><?
	}

	if (defined("BITRIX24_INDEX_COMPOSITE"))
	{
		$dynamicArea = new \Bitrix\Main\Page\FrameStatic("blogpostform-init");
		$dynamicArea->startDynamicArea();
		$dynamicArea->setStub('');
	}

	if (in_array('socnetlogdest', $array))
	{
		CModule::IncludeModule('intranet'); // for gov/public messages
		?>
		<script type="text/javascript">
			BX.ready(function(){
			window.MPFMentionInit('<?=$arParams["FORM_ID"]?>', {
				editorId : '<?= $arParams["LHE"]["id"]?>',
				id : '<?=$this->randString(6)?>',
				extranetUser : <?=($arParams["DESTINATION"]["EXTRANET_USER"] == 'Y'? 'true': 'false')?>,
				initDestination : <?=($arParams["DESTINATION_SHOW"] == "Y" ? "true" : "false")?>,
				items : {
					users : <?=(empty($arParams["DESTINATION"]['USERS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['USERS']))?>,
					emails: <?=(empty($arParams["DESTINATION"]['EMAILS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['EMAILS']))?>,
					crmemails: <?=(empty($arParams["DESTINATION"]['CRMEMAILS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['CRMEMAILS']))?>,
					mentionUsers : <?=(empty($arParams["DESTINATION"]['MENTION_USERS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['MENTION_USERS']))?>,
					groups : <?=($arParams["DESTINATION"]["EXTRANET_USER"] == 'Y' || (array_key_exists("DENY_TOALL", $arParams["DESTINATION"]) && $arParams["DESTINATION"]["DENY_TOALL"]) ?
					'{}' : "{'UA' : {'id':'UA','name': '".(!empty($arParams["DESTINATION"]['DEPARTMENT']) ? GetMessageJS("MPF_DESTINATION_3"): GetMessageJS("MPF_DESTINATION_4"))."'}}")?>,
					sonetgroups : <?=(empty($arParams["DESTINATION"]['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['SONETGROUPS']))?>,
					department : <?=(empty($arParams["DESTINATION"]['DEPARTMENT']) ? '{}' : CUtil::PhpToJSObject($arParams["DESTINATION"]["DEPARTMENT"]))?>,
					extranetRoot : <?=(empty($arResult["EXTRANET_ROOT"]) ? '{}' : CUtil::PhpToJSObject($arResult["EXTRANET_ROOT"]))?>,
					departmentRelation : <?=(empty($arParams["DESTINATION"]['DEPARTMENT_RELATION']) ? "false" : CUtil::PhpToJSObject($arParams["DESTINATION"]['DEPARTMENT_RELATION']))?>,
					contacts : <?=(empty($arParams["DESTINATION"]['CONTACTS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['CONTACTS']))?>,
					companies : <?=(empty($arParams["DESTINATION"]['COMPANIES'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['COMPANIES']))?>,
					leads : <?=(empty($arParams["DESTINATION"]['LEADS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LEADS']))?>,
					deals : <?=(empty($arParams["DESTINATION"]['DEALS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['DEALS']))?>
				},
				itemsLast : {
					users : <?=(empty($arParams["DESTINATION"]['LAST']['USERS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['USERS']))?>,
					emails : <?=(empty($arParams["DESTINATION"]['LAST']['EMAILS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['EMAILS']))?>,
					crmemails: <?=(empty($arParams["DESTINATION"]['LAST']['CRMEMAILS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['CRMEMAILS']))?>,
					mentionUsers : <?=(!isset($arParams["DESTINATION"]['LAST']['MENTION_USERS']) || empty($arParams["DESTINATION"]['LAST']['MENTION_USERS']) ? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['MENTION_USERS']))?>,
					sonetgroups : <?=(empty($arParams["DESTINATION"]['LAST']['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['SONETGROUPS']))?>,
					department : <?=(empty($arParams["DESTINATION"]['LAST']['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['DEPARTMENT']))?>,
					groups : <?=($arParams["DESTINATION"]["EXTRANET_USER"] == 'Y' || (array_key_exists("DENY_TOALL", $arParams["DESTINATION"]) && $arParams["DESTINATION"]["DENY_TOALL"]) ? '{}' : "{'UA':true}" )?>,
					contacts : <?=(empty($arParams["DESTINATION"]['LAST']['CONTACTS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['CONTACTS']))?>,
					companies : <?=(empty($arParams["DESTINATION"]['LAST']['COMPANIES'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['COMPANIES']))?>,
					leads : <?=(empty($arParams["DESTINATION"]['LAST']['LEADS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['LEADS']))?>,
					deals : <?=(empty($arParams["DESTINATION"]['LAST']['DEALS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['DEALS']))?>,
					crm : <?=(empty($arParams["DESTINATION"]['LAST']['CRM'])? '[]': CUtil::PhpToJSObject($arParams["DESTINATION"]['LAST']['CRM']))?>
				},
				itemsSelected : <?=(empty($arParams["DESTINATION"]['SELECTED'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['SELECTED']))?>,
				itemsHidden : <?=CUtil::PhpToJSObject(isset($arParams["DESTINATION"]["HIDDEN_ITEMS"]) ? $arParams["DESTINATION"]["HIDDEN_ITEMS"] : $arParams["DESTINATION"]["HIDDEN_GROUPS"])?>,
				isCrmFeed : <?=(empty($arParams["DESTINATION"]['LAST']['CRM']) ? 'false' : 'true')?>,
				useClientDatabase : <?=($arParams["DESTINATION_USE_CLIENT_DATABASE"] != "N" ? 'true' : 'false')?>,
				destSort : <?=CUtil::PhpToJSObject(isset($arParams["DESTINATION"]['DEST_SORT']) ? $arParams["DESTINATION"]['DEST_SORT'] : $arResult["DEST_SORT"])?>,
				mentionDestSort : <?=CUtil::PhpToJSObject(isset($arResult["MENTION_DEST_SORT"]) ? $arResult["MENTION_DEST_SORT"] : false)?>,
				allowAddUser: <?=($arResult["ALLOW_EMAIL_INVITATION"] ? 'true' : 'false')?>,
				allowAddCrmContact: <?=($arResult["ALLOW_ADD_CRM_CONTACT"] ? 'true' : 'false')?>,
				allowSearchCrmEmailUsers: <?=($arResult["ALLOW_CRM_EMAILS"] ? 'true' : 'false')?>,
				userNameTemplate: '<?=CUtil::JSEscape($arParams['NAME_TEMPLATE'])?>',
				allowSonetGroupsAjaxSearch: <?=(isset($arParams["DESTINATION"]['SONETGROUPS_LIMITED']) && $arParams["DESTINATION"]['SONETGROUPS_LIMITED'] == 'Y' ? 'true' : 'false')?>,
				allowSonetGroupsAjaxSearchFeatures: <?=(!empty($arParams["DESTINATION"]['SONETGROUPS_FEATURES']) ? CUtil::PhpToJSObject($arParams["DESTINATION"]['SONETGROUPS_FEATURES']) : '{}')?>,
				usersVacation : <?=(empty($arParams["DESTINATION"]['USERS_VACATION'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]['USERS_VACATION']))?>
			});
		});
		</script>
		<?
	}

	if (defined("BITRIX24_INDEX_COMPOSITE"))
	{
		$dynamicArea->finishDynamicArea();
	}
	/***************** Upload files ************************************/
	?><?=$arParams["UPLOADS_HTML"]?><?
	?><?=$arParams["~AT_THE_END_HTML"]?><?
	?><?=$arParams["URL_PREVIEW_HTML"]?><?
	?>
	<div class="feed-add-post-buttons" id="lhe_buttons_<?=$arParams["FORM_ID"]?>">
		<button class="ui-btn ui-btn-primary" id="lhe_button_submit_<?=$arParams["FORM_ID"]?>"><?=GetMessage("MPF_BUTTON_SEND")?></button>
		<button class="ui-btn ui-btn-link" id="lhe_button_cancel_<?=$arParams["FORM_ID"]?>"><?=GetMessage("MPF_BUTTON_CANCEL")?></button>
	</div>
</div>