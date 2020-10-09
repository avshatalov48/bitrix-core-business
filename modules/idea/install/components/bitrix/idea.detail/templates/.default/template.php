<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="blog-post-current" id="blog-post-<?=$arParams["ID"]?>">
<?
if($arResult["MESSAGE"] <> '')
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
	<?
}
if($arResult["ERROR_MESSAGE"] <> '')
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["ERROR_MESSAGE"]?>
		</div>
	</div>
	<?
}
if($arResult["FATAL_MESSAGE"] <> '')
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["FATAL_MESSAGE"]?>
		</div>
	</div>
	<?
}
elseif($arResult["NOTE_MESSAGE"] <> '')
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["NOTE_MESSAGE"]?>
		</div>
	</div>
	<?
}
elseif(!empty($arResult["Post"])>0)
{
	/*
	 * GetMessage("IDEA_STATUS_COMPLETED"); GetMessage("IDEA_STATUS_NEW"); GetMessage("IDEA_STATUS_PROCESSING");
	 * */
	$arStatusList = CIdeaManagment::getInstance()->Idea()->GetStatusList();
	$status = GetMessage("IDEA_STATUS_".ToUpper($arStatusList[$arResult["POST_PROPERTIES"]["DATA"]["UF_STATUS"]["VALUE"]]["XML_ID"]));
	if($status == '')
		$status = $arStatusList[$arResult["POST_PROPERTIES"]["DATA"]["UF_STATUS"]["VALUE"]]["VALUE"];
	if($arParams["SHOW_RATING"] == "Y"):?>
		<div class="idea-rating-block">
			<span class="idea-rating-block-left">
				<span class="idea-rating-block-right">
					<span class="idea-rating-block-content idea-rating-block-content-ext-<?=$arParams['RATING_TEMPLATE']?>">
						<span class="idea-rating-block-content-description"><?=GetMessage("IDEA_RATING_TITLE");?>:</span>
						<?$APPLICATION->IncludeComponent(
							"bitrix:rating.vote", $arParams['RATING_TEMPLATE'],
							Array(
								"VOTE_AVAILABLE" => $arResult["DISABLE_VOTE"]?"N":"Y",
								"ENTITY_TYPE_ID" => "BLOG_POST",
								"ENTITY_ID" => $arResult["Post"]["ID"],
								"OWNER_ID" => $arResult["Post"]["AUTHOR_ID"],
								"USER_HAS_VOTED" => $arResult["RATING"]["USER_HAS_VOTED"],
								"TOTAL_VOTES" => $arResult["RATING"]["TOTAL_VOTES"],
								"TOTAL_POSITIVE_VOTES" => $arResult["RATING"]["TOTAL_POSITIVE_VOTES"],
								"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"]["TOTAL_NEGATIVE_VOTES"],
								"USER_VOTE" => $arResult["RATING"]["USER_VOTE"],
								"PATH_TO_USER_PROFILE" => $arParams["AR_RESULT"]["PATH_TO_USER"],
							),
							false,
							array("HIDE_ICONS" => "Y")
						);?>
					</span>
				</span>
			</span>
		</div>
	<?endif;?>
	<div class="blog-qtl<?if($arResult["Post"]["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_READY):?> blog-post-hidden<?endif;?>">
		<div class="blog-qtr">
			<div class="blog-idea-body">
				<div class="idea-owner">
					<div class="bx-idea-condition-description status-color-<?=ToLower($arStatusList[$arResult["POST_PROPERTIES"]["DATA"]["UF_STATUS"]["VALUE"]]["XML_ID"]);?>">
						<div <?if($arResult["IDEA_MODERATOR"]):?>class="status-action idea-action-cursor" onclick="JSPublicIdea.ShowStatusDialog(this, '<?=$arResult["Post"]["ID"]?>');" id="status-<?=$arResult["Post"]["ID"]?>"<?endif;?>><?=htmlspecialcharsbx($status)?></div>
					</div>
					<?=GetMessage("IDEA_INTRODUCED_TITLE")?> <img class="idea-user-avatar" src="<?=$arResult["AUTHOR_AVATAR"][$arResult["arUser"]["ID"]]["src"]?>" align="top">
					<?
					if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && array_key_exists("ALIAS", $arResult["BlogUser"]) && $arResult["BlogUser"]["ALIAS"] <> '')
						$arTmpUser = array(
							"NAME" => "",
							"LAST_NAME" => "",
							"SECOND_NAME" => "",
							"LOGIN" => "",
							"NAME_LIST_FORMATTED" => $arResult["BlogUser"]["~ALIAS"],
						);
					elseif ($arResult["urlToBlog"] <> '' || $arResult["urlToAuthor"] <> '')
						$arTmpUser = array(
							"NAME" => $arResult["arUser"]["~NAME"],
							"LAST_NAME" => $arResult["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $arResult["arUser"]["~SECOND_NAME"],
							"LOGIN" => $arResult["arUser"]["~LOGIN"],
							"NAME_LIST_FORMATTED" => "",
						);
					?><noindex><?$APPLICATION->IncludeComponent("bitrix:main.user.link",
						'',
						array(
							"ID" => $arResult["arUser"]["ID"],
							"HTML_ID" => "blog_post_".$arResult["arUser"]["ID"],
							"NAME" => $arTmpUser["NAME"],
							"LAST_NAME" => $arTmpUser["LAST_NAME"],
							"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
							"LOGIN" => $arTmpUser["LOGIN"],
							"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
							"USE_THUMBNAIL_LIST" => "N",
							"PROFILE_URL" => $arResult["urlToAuthor"],
							//"PROFILE_URL_LIST" => $arResult["urlToBlog"],
							"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
							"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
							"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
							"INLINE" => "Y",
							"SEO_USER" => "Y"
						),
						false,
						array("HIDE_ICONS" => "Y")
					);?></noindex>
					<?=$arResult["Post"]["DATE_PUBLISH_FORMATED"]?>
				</div>
				<div class="post-title"><h2><a title="<?=$arResult["Post"]["TITLE"]?>"><?=$arResult["Post"]["TITLE"]?></a></h2></div>
				<div class="blog-post-text"><?=$arResult["Post"]["textFormated"]?></div>

				<?if (!empty($arResult["POST_PROPERTIES"]["DATA"][CBlogPost::UF_NAME])):
					$eventHandlerID = false;
					$eventHandlerID = AddEventHandler("main", "system.field.view.file", Array("CBlogTools", "blogUFfileShow"));
					$blogPostDoc = $arResult["POST_PROPERTIES"]["DATA"][CBlogPost::UF_NAME];
					if (!empty($blogPostDoc["VALUE"])): ?>
						<div class="blog-post-files">
							<?$APPLICATION->IncludeComponent(
								"bitrix:system.field.view",
								$blogPostDoc["USER_TYPE"]["USER_TYPE_ID"],
								array("arUserField" => $blogPostDoc), null, array("HIDE_ICONS"=>"N"));?>
						</div>
					<? endif;
					if ($eventHandlerID !== false && (intval($eventHandlerID) > 0))
						RemoveEventHandler("main", "system.field.view.file", $eventHandlerID);
				endif; ?>

				<?if($USER->IsAuthorized() || $arResult["urlToHide"] <> '' || $arResult["urlToShow"] <> '' || $arResult["urlToEdit"] <> '' || $arResult["urlToDelete"] <> ''):?>
					<div class="idea-post-meta">
						<div class="idea-post-meta-util">
							<?if($arResult["urlToHide"] <> ''):?>
								<a href="javascript:void(0)" onclick="if(confirm('<?=GetMessage("BLOG_MES_HIDE_POST_CONFIRM")?>')) window.location='<?=$arResult["urlToHide"]."&".bitrix_sessid_get()?>'"><span><?=GetMessage("BLOG_MES_HIDE")?></span></a>
							<?elseif($arResult["urlToShow"] <> ''):?>
								<a href="javascript:void(0)" onclick="if(confirm('<?=GetMessage("IDEA_MES_SHOW_POST_CONFIRM")?>')) window.location='<?=$arResult["urlToShow"]."&".bitrix_sessid_get()?>'"><span><?=GetMessage("IDEA_MES_SHOW")?></span></a>
							<?endif;?>
							<?if($arResult["urlToEdit"] <> ''):?>
								<a href="<?=$arResult["urlToEdit"]?>"><span class="blog-post-link-caption"><?=GetMessage("BLOG_BLOG_BLOG_EDIT")?></span></a></span>
							<?endif;?>
							<?if($arResult["urlToDelete"] <> ''):?>
								<a href="javascript:void(0)" onclick="if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$arResult["urlToDelete"]."&".bitrix_sessid_get()?>'"><span><?=GetMessage("BLOG_BLOG_BLOG_DELETE")?></span></a>
							<?endif;?>
						</div>
						<div class="idea-post-meta-util-subscribe">
							<?if(in_array(CIdeaManagmentEmailNotify::SUBSCRIBE_IDEA_COMMENT.$arResult["Post"]["ID"], $arResult["USER_IDEA_SUBSCRIBE"])):?>
								<a class="idea-post-unsubscribe"><span class="idea-post-subscribe-<?=$arResult["Post"]["ID"]?>"><?=GetMessage("IDEA_POST_UNSUBSCRIBE")?></span></a>
							<?else:?>
								<a class="idea-post-subscribe"><span class="idea-post-subscribe-<?=$arResult["Post"]["ID"]?>"><?=GetMessage("IDEA_POST_SUBSCRIBE")?></span></a>
							<?endif;?>
						</div>
						<br clear="both" />
					</div>
				<?endif;?>
			</div>
		</div>
	</div>
<?
}
else
	echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
?>
</div>
<?if(!empty($arResult["Post"])):?>
	<?if($arResult["IS_DUPLICATE"] !== false):?>
	<div class="blog-comments-duplicate">
		<div class="blog-comment-line-duplicate"></div>
		<div class="blog-comment-duplicate">
			<?=GetMessage("IDEA_POST_DUPLICATE", array("#LINK#" => $arResult["IS_DUPLICATE"]))?>
		</div>
	</div>
	<?endif;
	$arOfficial = false;

	if(isset($arParams["SPECIAL_ANSWER_ID"]) && is_array($arParams["SPECIAL_ANSWER_ID"]) && !empty($arParams["SPECIAL_ANSWER_ID"]))
		$arOfficial = array(
			"ID" => $arParams["SPECIAL_ANSWER_ID"],
		);  
	if($arOfficial!==false):
		$cntOfficial = $APPLICATION->IncludeComponent(
			"bitrix:idea.comment.list", 
			"official_detail", 
			Array(
				"RATING_TEMPLATE" => $arParams['RATING_TEMPLATE'],
				"FILTER" => $arOfficial,
				"BLOG_VAR" => $arParams["EXT"][0]["ALIASES"]["blog"],
				"USER_VAR" => $arParams["EXT"][0]["ALIASES"]["user_id"],
				"PAGE_VAR" => $arParams["EXT"][0]["ALIASES"]["page"],
				"POST_VAR" => $arParams["EXT"][0]["ALIASES"]["post_id"],
				"PATH_TO_BLOG" => $arParams["EXT"][0]["PATH_TO_BLOG"],
				"PATH_TO_POST" => $arParams["EXT"][0]["PATH_TO_POST"],
				"PATH_TO_USER" => $arParams["EXT"][0]["PATH_TO_USER"],
				"PATH_TO_SMILE" => $arParams["EXT"][0]["PATH_TO_SMILE"],
				"BLOG_URL" => $arParams["EXT"][0]["VARIABLES"]["blog"],
				"ID" => $arParams["EXT"][0]["VARIABLES"]["post_id"],
				"CACHE_TYPE" => $arParams["EXT"][0]["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["EXT"][0]["CACHE_TIME"],
				"COMMENTS_COUNT" => 1000, //unlimited by logic
				"DATE_TIME_FORMAT" => $arParams["EXT"][0]["DATE_TIME_FORMAT"],
				"USE_ASC_PAGING" => $arParams["EXT"][1]["USE_ASC_PAGING"],
				//"NOT_USE_COMMENT_TITLE" => $arParams["EXT"][1]["NOT_USE_COMMENT_TITLE"],
				"GROUP_ID" => $arParams["EXT"][1]["GROUP_ID"],
				"NAME_TEMPLATE" => $arParams["EXT"][1]["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["EXT"][1]["SHOW_LOGIN"],
				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["EXT"][1]["PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_SONET_USER_PROFILE" => $arParams["EXT"][1]["PATH_TO_SONET_USER_PROFILE"],
				"PATH_TO_MESSAGES_CHAT" => $arParams["EXT"][1]["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_VIDEO_CALL" => $arParams["EXT"][1]["PATH_TO_VIDEO_CALL"],
				"SHOW_RATING" => $arParams["EXT"][1]["SHOW_RATING"],
				"SMILES_COUNT" => $arParams["EXT"][1]["SMILES_COUNT"],
				"IMAGE_MAX_WIDTH" => $arParams["EXT"][1]["IMAGE_MAX_WIDTH"],
				"IMAGE_MAX_HEIGHT" => $arParams["EXT"][1]["IMAGE_MAX_HEIGHT"],
				"EDITOR_RESIZABLE" => $arParams["EXT"][1]["COMMENT_EDITOR_RESIZABLE"],
				"EDITOR_DEFAULT_HEIGHT" => $arParams["EXT"][1]["COMMENT_EDITOR_DEFAULT_HEIGHT"],
				"EDITOR_CODE_DEFAULT" => $arParams["EXT"][1]["COMMENT_EDITOR_CODE_DEFAULT"],
				"ALLOW_VIDEO" => $arParams["EXT"][1]["COMMENT_ALLOW_VIDEO"],
				"ALLOW_POST_CODE" => $arParams["EXT"][1]["ALLOW_POST_CODE"],
				"SHOW_SPAM" => $arParams["EXT"][1]["SHOW_SPAM"],
				"NO_URL_IN_COMMENTS" => $arParams["EXT"][1]["NO_URL_IN_COMMENTS"],
				"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["EXT"][1]["NO_URL_IN_COMMENTS_AUTHORITY"],
				"POST_BIND_USER" => $arParams["EXT"][1]["POST_BIND_USER"],
			),
			$component
		);
	endif;
	$cntOfficial = intval($cntOfficial);
	?>
	<div class="tag-tl">
		<div class="tag-tr">
			<div class="tag-block">
				<noindex>
					<div class="tag-line">
						<span class="main-tag-category"><?
						if($arResult["IDEA_CATEGORY"]["NAME"]!==false)
						{
							if($arResult["IDEA_CATEGORY"]["LINK"]===false)
								echo $arResult["IDEA_CATEGORY"]["NAME"];
							else
							{
								?><a href="<?=$arResult["IDEA_CATEGORY"]["LINK"];?>"><?=$arResult["IDEA_CATEGORY"]["NAME"];?></a><?
							}
						}?></span>
						<?if(!empty($arResult["Category"]))
						{
							$skipFirst = true;
							?><span class="tag-marker"></span><?
							foreach($arResult["Category"] as $v)
							{
								if (!$skipFirst) echo ', ';
									?><a href="<?=$v["urlToCategory"]?>" rel="nofollow"><?=$v["NAME"]?></a><?
								$skipFirst = false;
							}
						}
						?>
					</div>
				</noindex>
				<span class="post-comment"><a href="<?=$arResult["Post"]["urlToPost"]?>#comments"><?=GetMessage("IDEA_POST_COMMENT_CNT")?>: <?=(intval($arResult["Post"]["NUM_COMMENTS"])-$cntOfficial);?></a></span>
				<br clear="both"/>
			</div>
		</div>
	</div>
	<div class="tag-tbl"><div class="tag-tbr"><div class="tag-tbb"></div></div></div>
<?endif;?>
<script type="text/javascript">
	BX.viewElementBind(
		'blog-post-<?=$arParams["ID"]?>',
		{showTitle: true},
		function(node){
			return BX.type.isElementNode(node) && node.getAttribute('data-bx-image');
		}
	);
</script>
