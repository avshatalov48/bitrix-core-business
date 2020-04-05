<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
							</div>
							<div id="sidebar">
								<?if($USER->IsAuthorized()):?>
									<?$APPLICATION->IncludeComponent(
										"bitrix:menu", 
										"personal_left", 
										Array(
											"ROOT_MENU_TYPE"	=>	"left",
											"MAX_LEVEL"	=>	"1",
											"USE_EXT"	=>	"N"
										)
									);?>
								<?endif;?>
								<div id="tags-stub-id"><!-- контейнер для группировки, не должно быть в итоговой верстке -->
									<div class="sidebar-box popular-posts">
										<div class="sidebar-box-header"><?=GetMessage("TMPL_POPULAR_POSTS")?></div>
										<div class="sidebar-box-content">
											<?$APPLICATION->IncludeComponent(
												"bitrix:blog.popular_posts",
												"personal",
												Array(
													"SEO_USER" => "N",
													"MESSAGE_COUNT" => "4",
													"PERIOD_DAYS" => "60",
													"MESSAGE_LENGTH" => "80",
													"PREVIEW_WIDTH" => "100",
													"PREVIEW_HEIGHT" => "100",
													"DATE_TIME_FORMAT" => GetMessage("DATE_FORMAT"),
													"PATH_TO_BLOG" => SITE_DIR,
													"PATH_TO_POST" => SITE_DIR."#post_id#/",
													"PATH_TO_USER" => "",
													"PATH_TO_GROUP_BLOG_POST" => "",
													"PATH_TO_SMILE" => "/bitrix/images/blog/smile/",
													"BLOG_VAR" => "",
													"POST_VAR" => "",
													"USER_VAR" => "",
													"PAGE_VAR" => "",
													"CACHE_TYPE" => "A",
													"CACHE_TIME" => "36000000",
													"GROUP_ID" => "",
													"BLOG_URL" => "#BLOG_URL#"
												),
												false
											);?>
										</div>
										<b class="r0"></b><b class="r1"></b><b class="r2"></b>
									</div>
								
									<div class="sidebar-box new-comments">
										<div class="sidebar-box-header"><?=GetMessage("TMPL_NEW_COMMENTS")?></div>
										<div class="sidebar-box-content">
											<?$APPLICATION->IncludeComponent(
												"bitrix:blog.new_comments",
												"personal",
												Array(
													"SEO_USER" => "N",
													"COMMENT_COUNT" => "4",
													"MESSAGE_LENGTH" => "80",
													"DATE_TIME_FORMAT" => GetMessage("DATE_FORMAT"),
													"PATH_TO_BLOG" => SITE_DIR,
													"PATH_TO_POST" => SITE_DIR."#post_id#/",
													"PATH_TO_USER" => "",
													"PATH_TO_GROUP_BLOG_POST" => "",
													"PATH_TO_SMILE" => "/bitrix/images/blog/smile/",
													"BLOG_VAR" => "",
													"POST_VAR" => "",
													"USER_VAR" => "",
													"PAGE_VAR" => "",
													"CACHE_TYPE" => "A",
													"CACHE_TIME" => "36000000",
													"GROUP_ID" => "",
													"BLOG_URL" => "#BLOG_URL#"
												),
												false
											);?>
										</div>
										<b class="r0"></b><b class="r1"></b><b class="r2"></b>
									</div>
									<?if(IsModuleInstalled("search") && IsModuleInstalled("blog"))
									{
										$arBlog = CBlog::GetByUrl("#BLOG_URL#");
										if(!empty($arBlog))
										{
											?>
											<div class="sidebar-box tags-cloud">
												<div class="sidebar-box-header"><?=GetMessage("TMPL_TAGS_CLOUD")?></div>
												<div class="sidebar-box-content">
													<div class="search-cloud">
													<?
													$APPLICATION->IncludeComponent("bitrix:search.tags.cloud", ".default", array(
														"SORT" => "NAME",
														"PAGE_ELEMENTS" => "30",
														"PERIOD" => "60",
														"URL_SEARCH" => SITE_DIR."search.php",
														"TAGS_INHERIT" => "N",
														"CHECK_DATES" => "Y",
														"arrFILTER" => array(
															0 => "blog",
														),
														"arrFILTER_blog" => array(
															0 => $arBlog["ID"],
														),
														"CACHE_TYPE" => "A",
														"CACHE_TIME" => "36000000",
														"FONT_MAX" => "20",
														"FONT_MIN" => "15",
														"COLOR_NEW" => "0082D4",
														"COLOR_OLD" => "0082D4",
														"PERIOD_NEW_TAGS" => "",
														"SHOW_CHAIN" => "N",
														"COLOR_TYPE" => "Y",
														"WIDTH" => "100%"
														),
														false
													);
													?>
													</div>
												</div>
												<b class="r0"></b><b class="r1"></b><b class="r2"></b>
											</div>
										<?
										}
									}?>

									
									</div>
								</div>
							</div>
						</div>
						
				
				<div id="footer-wrapper">
					<div id="footer">
						<div id="copyright"><?$APPLICATION->IncludeFile(
							SITE_TEMPLATE_PATH."/include_areas/copyright.php",
							Array(),
							Array("MODE"=>"html")
						);?></div>
						<ul id="footer-links">
							<li><a href="<?=SITE_DIR?>contacts.php"><?=GetMessage("TMPL_FEEDBACK")?></a></li>
							<?if(!$USER->IsAuthorized()):?>
								<li><a href="<?=SITE_DIR?>auth.php"><?=GetMessage("TMPL_AUTH")?></a></li>
							<?endif;?>
						</ul>
						<div id="footer-design"><?=GetMessage("FOOTER_DISIGN")?></div>
					</div>
				</div>
			</div>
		</div>
</body>
</html>
<?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/blog.css");
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/common.css");?>