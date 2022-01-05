<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(count($arResult["POSTS"])>0)
{
	?>
	<div class="blog-content">
		<div class="blog-posts">
			<div id="blog-posts-content">
			<?
			foreach($arResult["POSTS"] as $ind => $CurPost)
			{
				$className = "blog-post";
				if($ind == 0)
					$className .= " blog-post-first";
				elseif(($ind+1) == count($arResult["POSTS"]))
					$className .= " blog-post-last";
				if($ind%2 == 0)
					$className .= " blog-post-alt";
				$className .= " blog-post-year-".$CurPost["DATE_PUBLISH_Y"];
				$className .= " blog-post-month-".$CurPost["DATE_PUBLISH_M"];
				$className .= " blog-post-day-".$CurPost["DATE_PUBLISH_D"];
				?>
				<div class="<?=$className?>">
					<h2 class="blog-post-title"><a href="<?=$CurPost["urlToPost"]?>" title="<?=$CurPost["TITLE"]?>"><?=$CurPost["TITLE"]?></a></h2>
					<div class="blog-post-info-back blog-post-info-top">
					<div class="blog-post-info">
						<div class="blog-author">
						<?if($arParams["SEO_USER"] == "Y"):?>
							<noindex>
								<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>" rel="nofollow"></a>
							</noindex>
						<?else:?>
							<a class="blog-author-icon" href="<?=$CurPost["urlToAuthor"]?>"></a>
						<?endif;?>
						<?
						if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && ($CurPost["urlToBlog"] <> '' || $CurPost["urlToAuthor"] <> '') && array_key_exists("BLOG_USER_ALIAS", $CurPost) && $CurPost["BLOG_USER_ALIAS"] <> '')
							$arTmpUser = array(
								"NAME" => "",
								"LAST_NAME" => "",
								"SECOND_NAME" => "",
								"LOGIN" => "",
								"NAME_LIST_FORMATTED" => $CurPost["~BLOG_USER_ALIAS"],
							);
						elseif ($CurPost["urlToBlog"] <> '' || $CurPost["urlToAuthor"] <> '')
							$arTmpUser = array(
								"NAME" => $CurPost["~AUTHOR_NAME"],
								"LAST_NAME" => $CurPost["~AUTHOR_LAST_NAME"],
								"SECOND_NAME" => $CurPost["~AUTHOR_SECOND_NAME"],
								"LOGIN" => $CurPost["~AUTHOR_LOGIN"],
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
								"ID" => $CurPost["AUTHOR_ID"],
								"HTML_ID" => "blog_new_posts_list_".$CurPost["AUTHOR_ID"],
								"NAME" => $arTmpUser["NAME"],
								"LAST_NAME" => $arTmpUser["LAST_NAME"],
								"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
								"LOGIN" => $arTmpUser["LOGIN"],
								"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
								"USE_THUMBNAIL_LIST" => "N",
								"PROFILE_URL" => $CurPost["urlToAuthor"],
								"PROFILE_URL_LIST" => $CurPost["urlToBlog"],
								"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
								"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
								"SHOW_YEAR" => $arParams["SHOW_YEAR"],
								"CACHE_TYPE" => $arParams["CACHE_TYPE"],
								"CACHE_TIME" => $arParams["CACHE_TIME"],
								"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
								"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
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
						<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):?>
							<p>
							<?
							if(is_array($CurPost["POST_PROPERTIES"]["DATA"]) && !empty($CurPost["POST_PROPERTIES"]["DATA"])) 
							{
								foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
								<?if(!empty($arPostField["VALUE"])):?>
								<b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b>&nbsp;<?$APPLICATION->IncludeComponent(
												"bitrix:system.field.view", 
												$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
												array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?><br />
								<?endif;?>
								<?endforeach;
							}?>
							</p>
						<?endif;?>
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
										"ID" => $CurPost["AUTHOR_ID"],
										"HTML_ID" => "blog_new_posts_list_".$CurPost["AUTHOR_ID"],
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
						<div class="blog-post-meta-util">
							<span class="blog-post-comments-link"><a href="<?=$CurPost["urlToPost"]?>#comments"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?> <?=intval($CurPost["NUM_COMMENTS"]);?></a></span>
							<span class="blog-post-views-link"><a href="<?=$CurPost["urlToPost"]?>"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?> <?=intval($CurPost["VIEWS"]);?></a></span>
							<?if ($arParams["SHOW_RATING"] == "Y"):?>
							<span class="rating_vote_text">
							<?
							$APPLICATION->IncludeComponent(
								"bitrix:rating.vote", $arParams["RATING_TYPE"],
								Array(
									"ENTITY_TYPE_ID" => "BLOG_POST",
									"ENTITY_ID" => $CurPost["ID"],
									"OWNER_ID" => $CurPost["AUTHOR_ID"],
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
					</div>
				</div>
				<?
			}
			?>
			</div>
		</div>
		<?
		if($arResult["NAV_STRING"] <> '')
			echo $arResult["NAV_STRING"];
		?>
	</div>
	<?
}
?>