<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ************************* Input params***************************************************************
$arParams["SHOW_LINK_TO_FORUM"] = ($arParams["SHOW_LINK_TO_FORUM"] == "N" ? "N" : "Y");
$arParams["FILES_COUNT"] = intVal(intVal($arParams["FILES_COUNT"]) > 0 ? $arParams["FILES_COUNT"] : 1);
$arParams["IMAGE_SIZE"] = (intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 100);
// *************************/Input params***************************************************************
if (!empty($arResult["MESSAGES"])):
$arResult["MESSAGES_REV"] = array_reverse($arResult["MESSAGES"], true);
?>
<!--Flag used for cut comments content on JS and put to correct node in DOM -->
#COMMENTS_BEGIN#
<?foreach ($arResult["MESSAGES_REV"] as $res):?>
	<?if ($arParams['FETCH_USER_ALIAS'])
		$res["AUTHOR_URL"] = CPGalleryInterface::GetPathWithUserAlias($res["AUTHOR_URL"], $res["AUTHOR_ID"], $arParams['IBLOCK_ID']);
	?>

	<div class="photo-comment" id="bxphoto_com_<?=$res["ID"]?>">
		<div class="photo-comment-avatar <?if ($res['AUTHOR_PHOTO'] == ''){echo 'photo-comment-avatar-none';}?>" >
			<?if ($res['AUTHOR_PHOTO'] != ''):?>
				<div class="photo-comment-avatar-inner" style="background-image: url('<?= $res['AUTHOR_PHOTO']?>');" title="<?=$res["AUTHOR_NAME"]?>"></div>
			<?endif;?>
		</div>
		<div onmouseout="BX.removeClass(this, 'photo-comment-hover')" onmouseover="BX.addClass(this, 'photo-comment-hover')" class="photo-comment-info-text">
			<div class="photo-comment-info">
				<a name="message<?=$res["ID"]?>"></a>
				<?if (intVal($res["AUTHOR_ID"]) > 0 && !empty($res["AUTHOR_URL"])):?>
				<a class="photo-comment-name" href="<?=$res["AUTHOR_URL"]?>"><?=$res["AUTHOR_NAME"]?></a>
				<?else:?>
				<span class="photo-comment-name"><?=$res["AUTHOR_NAME"]?></span>
				<?endif;?>
				<span class="photo-info-date"><?=$res["POST_DATE"]?></span>
				<?if ($arParams["SHOW_RATING"] == "Y"):?>
				<span class="review-rating rating_vote_text">
					<?
					$arRatingParams = Array(
							"ENTITY_TYPE_ID" => "FORUM_POST",
							"ENTITY_ID" => $res["ID"],
							"OWNER_ID" => $res["AUTHOR_ID"],
							"AJAX_MODE" => 'Y',
							"PATH_TO_USER_PROFILE" => strlen($arParams["PATH_TO_USER"]) > 0? $arParams["PATH_TO_USER"]: $arParams["~URL_TEMPLATES_PROFILE_VIEW"]
						);
					if (!isset($res['RATING']))
						$res['RATING'] = array(
								"USER_VOTE" => 0,
								"USER_HAS_VOTED" => 'N',
								"TOTAL_VOTES" => 0,
								"TOTAL_POSITIVE_VOTES" => 0,
								"TOTAL_NEGATIVE_VOTES" => 0,
								"TOTAL_VALUE" => 0
							);
					$arRatingParams = array_merge($arRatingParams, $res['RATING']);
					// mantis:48973
					?><script type="text/javascript">if (!window.BX && top.BX){window.BX = top.BX;}</script><?
					$GLOBALS["APPLICATION"]->IncludeComponent( "bitrix:rating.vote", $arParams["RATING_TYPE"], $arRatingParams, $component, array("HIDE_ICONS" => "Y"));
					?>
				</span>
				<?endif;?>
				<?/*
				TODO: add ability to delete and / or edit
				<a href="" class="photo-comment-edit"></a>
				<a href="" class="photo-comment-remove"></a>
				*/?>
			</div>
			<div class="photo-comment-text"><?=$res["POST_MESSAGE_TEXT"]?></div>
		</div>
	</div>
<?endforeach;?>
#COMMENTS_END#
</div>
<?endif;?>

<?
if (empty($arResult["ERROR_MESSAGE"]) && !empty($arResult["~OK_MESSAGE"]) && $arResult["~OK_MESSAGE"][0]['id'] !== 'REPLY' && $arResult["~OK_MESSAGE"][0]['id'] !== 'NEW'):
?>
#NOTE_BEGIN#
	<div class="reviews-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"]);?></div>
#NOTE_END#
<?endif;?>

#ADD_COMMENT_BEGIN#
<?if($arResult["USER"]["PERMISSION"] > "E"): /* USER CAN WRITE MESSAGES*/?>
<form name="REPLIER<?=$arParams["form_index"]?>" id="REPLIER<?=$arParams["form_index"]?>" action="<?=$arParams['~ACTION_URL']?>" method="POST" enctype="multipart/form-data" class="reviews-form">
	<input type="hidden" name="back_page" value="<?=$arResult["CURRENT_PAGE"]?>" />
	<input type="hidden" name="photo_element_id" value="<?=$arParams["ELEMENT_ID"]?>" id="ELEMENT_ID<?=$arParams["form_index"]?>" />
	<input type="hidden" name="SECTION_ID" value="<?=$arResult["ELEMENT_REAL"]["IBLOCK_SECTION_ID"]?>" />
	<input type="hidden" name="REVIEW_USE_SMILES" id="REVIEW_USE_SMILES<?=$arParams["form_index"]?>" value="<?=(($arResult["REVIEW_USE_SMILES"]=="Y") ? "Y" : "N")?>" />
	<input type="hidden" name="save_product_review" value="Y" />
	<input type="hidden" name="preview_comment" value="N" />
	<input type="hidden" name="save_photo_comment" value="Y" />
	<?=bitrix_sessid_post()?>

<?if (!empty($arResult["ERROR_MESSAGE"])):?>
<div class="reviews-note-box reviews-note-error">
	<div class="reviews-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "reviews-note-error");?></div>
</div>
<? endif;?>

<?if (!$arResult["IS_AUTHORIZED"]): /* GUEST PANEL */?>
	<div class="reviews-reply-fields">
		<div class="reviews-reply-field-user">
			<div class="reviews-reply-field reviews-reply-field-author">
				<input name="REVIEW_AUTHOR" id="REVIEW_AUTHOR<?=$arParams["form_index"]?>" size="30" type="text" value="<?= ($arResult["REVIEW_AUTHOR"] != '' ? $arResult["REVIEW_AUTHOR"] : GetMessage("OPINIONS_NAME").'...')?>" title="<?=GetMessage("OPINIONS_NAME")?>" <?if($arResult["REVIEW_AUTHOR"] != ''){echo 'class="bxph-photo-active"';}?> />
			</div>
<?if ($arResult["FORUM"]["ASK_GUEST_EMAIL"]=="Y"):?>
			<div class="reviews-reply-field reviews-reply-field-email"><?/* <label for="REVIEW_EMAIL<?=$arParams["form_index"]?>"><?=GetMessage("OPINIONS_EMAIL")?></label>*/?>
				<input type="text" name="REVIEW_EMAIL" id="REVIEW_EMAIL<?=$arParams["form_index"]?>" size="30" value="<?= ($arResult["REVIEW_EMAIL"] != '' ? $arResult["REVIEW_EMAIL"] : GetMessage("OPINIONS_EMAIL").'...')?>" title="<?=GetMessage("OPINIONS_EMAIL")?>" <?if($arResult["REVIEW_EMAIL"] != ''){echo 'class="bxph-photo-active"';}?>/>
			</div>
<?endif;?>
			<div class="reviews-clear-float"></div>
		</div>
	</div>
<?endif; /* if (!$arResult["IS_AUTHORIZED"]) */?>

	<div class="reviews-reply-fields">
		<div class="reviews-reply-field reviews-reply-field-text">
			<textarea class="photo-textarea" name="REVIEW_TEXT" id="REVIEW_TEXT"><?=$arResult["REVIEW_TEXT"];?></textarea>
		</div>
<?if (strLen($arResult["CAPTCHA_CODE"]) > 0): /* CAPTHCA */?>
		<div class="photo-forum-capcha-cont">
			<img class="photo-forum-capcha-img" src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CAPTCHA_CODE"]?>" alt="<?=GetMessage("F_CAPTCHA_TITLE")?>"/>
			<label class="photo-forum-capcha-label" for="captcha_word" ><?=GetMessage("F_CAPTCHA_PROMT")?></label>
			<input class="photo-forum-capcha-input bxph-photo-active" type="text" size="30" name="captcha_word" autocomplete="off"/>
			<input type="hidden" name="captcha_code" value="<?=$arResult["CAPTCHA_CODE"]?>"/>
		</div>
<? endif; /* END CAPTHCA */ ?>

		<a href="javascript:void(0)" class="photo-comment-add" id="bxphoto_add_comment_but" title="<?= GetMessage("ADD_COMMENT_TITLE")?>"><span><?= GetMessage("ADD_COMMENT")?></span><i></i></a>
	</div>
</form>
<?endif; /* if($arResult["USER"]["PERMISSION"] > "E") */?>
#ADD_COMMENT_END#

<script>
top._bxArCommentsIds = <?= CUtil::PhpToJSObject($arResult['ARRAY_IDS'])?>;
top._bxError = <?=(empty($arResult["ERROR_MESSAGE"]) ? 'false' : 'true')?>;
if (top._bxErrorHandler)
{
	top.BX.removeCustomEvent(top.oBXPhotoSlider['<?= CUtil::JSEscape($_REQUEST["UCID"])?>'], 'OnRegisterCommentsControl', top._bxErrorHandler);
	top._bxErrorHandler = null;
}

top._bxErrorHandler = function(){
	top.oBXPhotoSlider['<?= CUtil::JSEscape($_REQUEST["UCID"])?>'].RegisterCommentsControl({
		returnComments: '<?= ($_REQUEST['return_more_comments'] != 'Y' ? 'Y' : 'N')?>',
		itemId: <?=$arParams["ELEMENT_ID"]?>,
		arComments: <?= CUtil::PhpToJSObject($arResult['ARRAY_IDS'])?>,
		formCont: top.BX('bxphoto-comments-reviews-reply-form'),
		button: top.BX('bxphoto_add_comment_but'),
		textarea: top.BX('REVIEW_TEXT'),
		form: top.BX('REPLIER<?=$arParams["form_index"]?>'),
		elementId: top.BX('ELEMENT_ID<?=$arParams["form_index"]?>'),
		<?if (!$arResult["IS_AUTHORIZED"]):?>
			guestName: top.BX('REVIEW_AUTHOR<?=$arParams["form_index"]?>'),
			guestEmail: top.BX('REVIEW_EMAIL<?=$arParams["form_index"]?>'),
		<?endif;?>
		navParams: {
			pageCount: '<?= $arResult["NAV_RESULT"]->NavPageCount?>',
			pageSize: '<?= $arResult["NAV_RESULT"]->NavPageSize?>',
			pagen: '<?= $arResult["NAV_RESULT"]->PAGEN?>',
			NavNum: '<?= $arResult["NAV_RESULT"]->NavNum?>',
			nSelectedCount: '<?= $arResult["NAV_RESULT"]->nSelectedCount?>'
		}
	});
};

top.BX.addCustomEvent(top.oBXPhotoSlider['<?= CUtil::JSEscape($_REQUEST["UCID"])?>'], 'OnRegisterCommentsControl', top._bxErrorHandler);
</script>

