<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?CUtil::InitJSCore(array("image"));?>
<div id="blog-posts-content">
<?
if(!empty($arResult["OK_MESSAGE"]))
{
	?>
	<div class="blog-notes blog-note-box">
		<div class="blog-note-text">
			<ul>
				<?
				foreach($arResult["OK_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["MESSAGE"]))
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<ul>
				<?
				foreach($arResult["MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<ul>
				<?
				foreach($arResult["ERROR_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}

if(count($arResult["POST"])>0)
{
	foreach($arResult["POST"] as $ind => $CurPost)
	{
		$className = "blog-post";
		if($ind == 0)
			$className .= " blog-post-first";
		elseif(($ind+1) == count($arResult["POST"]))
			$className .= " blog-post-last";
		if($ind%2 == 0)
			$className .= " blog-post-alt";
		$className .= " blog-post-year-".$CurPost["DATE_PUBLISH_Y"];
		$className .= " blog-post-month-".IntVal($CurPost["DATE_PUBLISH_M"]);
		$className .= " blog-post-day-".IntVal($CurPost["DATE_PUBLISH_D"]);
		?>
			<script>
			BX.viewImageBind(
				'blg-post-<?=$CurPost["ID"]?>',
				{showTitle: false}, 
				{tag:'IMG', attr: 'data-bx-image'}
			);
			</script>
			<div class="<?=$className?>" id="blg-post-<?=$CurPost["ID"]?>">
				<h2 class="blog-post-title"><a href="<?=$CurPost["urlToPost"]?>" title="<?=$CurPost["TITLE"]?>"><?=$CurPost["TITLE"]?></a></h2>
				<div class="blog-post-info-back blog-post-info-top">
				<div class="blog-post-info">
					<?if ($arParams["SHOW_RATING"] == "Y"):?>
					<div class="blog-post-rating rating_vote_graphic">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:rating.vote", $arParams["RATING_TYPE"],
						Array(
							"ENTITY_TYPE_ID" => "BLOG_POST",
							"ENTITY_ID" => $CurPost["ID"],
							"OWNER_ID" => $CurPost["arUser"]["ID"],
							"USER_VOTE" => $arResult["RATING"][$CurPost["ID"]]["USER_VOTE"],
							"USER_HAS_VOTED" => $arResult["RATING"][$CurPost["ID"]]["USER_HAS_VOTED"],
							"TOTAL_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VOTES"],
							"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_POSITIVE_VOTES"],
							"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_NEGATIVE_VOTES"],
							"TOTAL_VALUE" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VALUE"],
							"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);?>
					</div>
					<?endif;?>
					<div class="blog-author">
					<?if($arParams["SEO_USER"] == "Y"):?>
						<noindex>
							<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>" rel="nofollow"></a>
						</noindex>
					<?else:?>
						<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>"></a>
					<?endif;?>
					<?
					if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && array_key_exists("ALIAS", $CurPost["BlogUser"]) && strlen($CurPost["BlogUser"]["ALIAS"]) > 0)
						$arTmpUser = array(
							"NAME" => "",
							"LAST_NAME" => "",
							"SECOND_NAME" => "",
							"LOGIN" => "",
							"NAME_LIST_FORMATTED" => $CurPost["BlogUser"]["~ALIAS"],
						);
					elseif (strlen($CurPost["urlToBlog"]) > 0 || strlen($CurPost["urlToAuthor"]) > 0)
						$arTmpUser = array(
							"NAME" => $CurPost["arUser"]["~NAME"],
							"LAST_NAME" => $CurPost["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $CurPost["arUser"]["~SECOND_NAME"],
							"LOGIN" => $CurPost["arUser"]["~LOGIN"],
							"NAME_LIST_FORMATTED" => "",
						);
					?>					
					<?if($arParams["SEO_USER"] == "Y"):?>
						<noindex>
					<?endif;?>
					<?
					$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
						'',
						array(
							"ID" => $CurPost["arUser"]["ID"],
							"HTML_ID" => "blog_blog_".$CurPost["arUser"]["ID"],
							"NAME" => $arTmpUser["NAME"],
							"LAST_NAME" => $arTmpUser["LAST_NAME"],
							"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
							"LOGIN" => $arTmpUser["LOGIN"],
							"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
							"USE_THUMBNAIL_LIST" => "N",
							"PROFILE_URL" => $CurPost["urlToAuthor"],
							"PROFILE_URL_LIST" => $CurPost["urlToBlog"],
							"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
							"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
							"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_SONET_USER_PROFILE"],
							"INLINE" => "Y",
							"SEO_USER" => $arParams["SEO_USER"],
						),
						false,
						array("HIDE_ICONS" => "Y")
					);
					?>
					<?if($arParams["SEO_USER"] == "Y"):?>
						</noindex>
					<?endif;?>
					</div>
					<div class="blog-post-date"><span class="blog-post-day"><?=$CurPost["DATE_PUBLISH_DATE"]?></span><span class="blog-post-time"><?=$CurPost["DATE_PUBLISH_TIME"]?></span><span class="blog-post-date-formated"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></span></div>
				</div>
				</div>
				<div class="blog-post-content">
					<div class="blog-post-avatar"><?=$CurPost["BlogUser"]["AVATAR_img"]?></div>
					<?=$CurPost["TEXT_FORMATED"]?>
					<?
					if ($CurPost["CUT"] == "Y")
					{
						?><p><a class="blog-postmore-link" href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_MORE")?></a></p><?
					}
					?>
					<?if(!empty($CurPost["arImages"]))
					{
						?>
						<div class="feed-com-files">
							<div class="feed-com-files-title"><?=GetMessage("BLOG_PHOTO")?></div>
							<div class="feed-com-files-cont">
								<?
								foreach($CurPost["arImages"] as $val)
								{
									?><span class="feed-com-files-photo"><img src="<?=$val["small"]?>" alt="" border="0" data-bx-image="<?=$val["full"]?>"></span><?
								}
								?>
							</div>
						</div>
						<?
					}?>
					<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):
						$eventHandlerID = false;
						$eventHandlerID = AddEventHandler('main', 'system.field.view.file', Array('CBlogTools', 'blogUFfileShow'));
						?>
						<?foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
						<?if(!empty($arPostField["VALUE"])):?>
						<div>
						<?=($FIELD_NAME=='UF_BLOG_POST_DOC' ? "" : "<b>".$arPostField["EDIT_FORM_LABEL"].":</b>&nbsp;")?>
							<?$APPLICATION->IncludeComponent(
								"bitrix:system.field.view", 
								$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
								array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
						</div>
						<?endif;?>
						<?endforeach;?>
						<?
						if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
							RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
					endif;?>
				</div>
				
				<div class="blog-post-meta">
					<div class="blog-post-info-bottom">
						<div class="blog-post-info">
							<div class="blog-author">
							<?if($arParams["SEO_USER"] == "Y"):?>
								<noindex>
									<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>" rel="nofollow"></a>
								</noindex>
							<?else:?>
								<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>"></a>
							<?endif;?>
							<?if($arParams["SEO_USER"] == "Y"):?>
								<noindex>
							<?endif;?>
							<?
							$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
								'',
								array(
									"ID" => $CurPost["arUser"]["ID"],
									"HTML_ID" => "blog_blog_".$CurPost["arUser"]["ID"],
									"NAME" => $arTmpUser["NAME"],
									"LAST_NAME" => $arTmpUser["LAST_NAME"],
									"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
									"LOGIN" => $arTmpUser["LOGIN"],
									"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
									"USE_THUMBNAIL_LIST" => "N",
									"PROFILE_URL" => $CurPost["urlToAuthor"],
									"PROFILE_URL_LIST" => $CurPost["urlToBlog"],
									"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
									"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
									"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
									"SHOW_YEAR" => $arParams["SHOW_YEAR"],
									"CACHE_TYPE" => $arParams["CACHE_TYPE"],
									"CACHE_TIME" => $arParams["CACHE_TIME"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
									"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
									"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_SONET_USER_PROFILE"],
									"INLINE" => "Y",
									"SEO_USER" => $arParams["SEO_USER"],
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
							?>
							<?if($arParams["SEO_USER"] == "Y"):?>
								</noindex>
							<?endif;?>
							</div>
							<div class="blog-post-date"><span class="blog-post-day"><?=$CurPost["DATE_PUBLISH_DATE"]?></span><span class="blog-post-time"><?=$CurPost["DATE_PUBLISH_TIME"]?></span><span class="blog-post-date-formated"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></span></div>
						</div>
					</div>
					<?
					if(array_key_exists("USE_SHARE", $arParams) && $arParams["USE_SHARE"] == "Y")
					{
						?>
						<div class="blog-post-share" style="float: right;">
							<noindex>
							<?
							$APPLICATION->IncludeComponent("bitrix:main.share", "", array(
									"HANDLERS" => $arParams["SHARE_HANDLERS"],
									"PAGE_URL" => htmlspecialcharsback($CurPost["urlToPost"]),
									"PAGE_TITLE" => htmlspecialcharsback($CurPost["TITLE"]),
									"SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
									"SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
									"ALIGN" => "right",
									"HIDE" => $arParams["SHARE_HIDE"],
								),
								$component,
								array("HIDE_ICONS" => "Y")
							);
							?>
							</noindex>
						</div>
						<?
					}
					?>
					<div class="blog-post-meta-util">
						<span class="blog-post-views-link"><a href="<?=$CurPost["urlToPost"]?>"><span class="blog-post-link-caption"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?></span><span class="blog-post-link-counter"><?=IntVal($CurPost["VIEWS"]);?></span></a></span>
						<?if($CurPost["ENABLE_COMMENTS"] == "Y"):?>
							<span class="blog-post-comments-link"><a href="<?=$CurPost["urlToPost"]?>#comments"><span class="blog-post-link-caption"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?></span><span class="blog-post-link-counter"><?=IntVal($CurPost["NUM_COMMENTS"]);?></span></a></span>
						<?endif;?>
						<?if(strLen($CurPost["urlToHide"])>0):?>
							<span class="blog-post-hide-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_HIDE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToHide"]."&".bitrix_sessid_get()?>'"><span class="blog-post-link-caption"><?=GetMessage("BLOG_MES_HIDE")?></span></a></span>
						<?endif;?>
						<?if(strLen($CurPost["urlToEdit"])>0):?>
							<span class="blog-post-edit-link"><a href="<?=$CurPost["urlToEdit"]?>"><span class="blog-post-link-caption"><?=GetMessage("BLOG_MES_EDIT")?></span></a></span>
						<?endif;?>
						<?if(strLen($CurPost["urlToDelete"])>0):?>
							<span class="blog-post-delete-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToDelete"]."&".bitrix_sessid_get()?>'"><span class="blog-post-link-caption"><?=GetMessage("BLOG_MES_DELETE")?></span></a></span>
						<?endif;?>

						<?if ($arParams["SHOW_RATING"] == "Y"):?>
						<span class="rating_vote_text">
						<?
						$APPLICATION->IncludeComponent(
							"bitrix:rating.vote", $arParams["RATING_TYPE"],
							Array(
								"ENTITY_TYPE_ID" => "BLOG_POST",
								"ENTITY_ID" => $CurPost["ID"],
								"OWNER_ID" => $CurPost["arUser"]["ID"],
								"USER_VOTE" => $arResult["RATING"][$CurPost["ID"]]["USER_VOTE"],
								"USER_HAS_VOTED" => $arResult["RATING"][$CurPost["ID"]]["USER_HAS_VOTED"],
								"TOTAL_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VOTES"],
								"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_POSITIVE_VOTES"],
								"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_NEGATIVE_VOTES"],
								"TOTAL_VALUE" => $arResult["RATING"][$CurPost["ID"]]["TOTAL_VALUE"],
								"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
							),
							$component,
							array("HIDE_ICONS" => "Y")
						);?>
						</span>
						<?endif;?>
					</div>

					<div class="blog-post-tag">
						<noindex>
						<?
						if(!empty($CurPost["CATEGORY"]))
						{
							echo "<span>".GetMessage("BLOG_BLOG_BLOG_CATEGORY")."</span>";
							$i=0;
							foreach($CurPost["CATEGORY"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["urlToCategory"]?>" rel="nofollow"><?=$v["NAME"]?></a><?
								$i++;
							}
						}
						?>
						</noindex>
					</div>
				</div>
			</div>
		<?
	}
	if(strlen($arResult["NAV_STRING"])>0)
		echo $arResult["NAV_STRING"];
}
elseif(!empty($arResult["BLOG"]))
	echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
?>	
</div>