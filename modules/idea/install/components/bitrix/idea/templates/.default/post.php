<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//get array of official answers
$arBlogPostOfficial = array();
$arFilter = array(
	//"BLOG_ID"
);

if(is_numeric($arResult["VARIABLES"]["post_id"]))
	$arFilter["ID"] = $arResult["VARIABLES"]["post_id"];
else
	$arFilter["CODE"] = $arResult["VARIABLES"]["post_id"];

$arBlogPostOfficial = CBlogPost::GetList(
	array(),
	$arFilter,
	false,
	false,
	array("ID", CIdeaManagment::UFAnswerIdField)
)->Fetch();

if(!is_array($arBlogPostOfficial[CIdeaManagment::UFAnswerIdField]))
	$arBlogPostOfficial[CIdeaManagment::UFAnswerIdField] = array();
?>

<div class="idea-managment-content">
	<?if(!empty($arResult["ACTIONS"])):?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arResult["ACTIONS"]
		),
		$component
	);?>
	<?endif;?>
	<?//Side bar tools?>
	<?$this->SetViewTarget("sidebar", 100)?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.category.list",
				"",
				Array(
					"IBLOCK_CATEGORIES" => $arParams["IBLOCK_CATEGORIES"],
					"PATH_TO_CATEGORY_1" => $arResult["PATH_TO_CATEGORY_1"],
					"PATH_TO_CATEGORY_2" => $arResult["PATH_TO_CATEGORY_2"],
				),
				$component
		);
		?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.statistic",
				"",
				Array(
					"BLOG_URL" => $arResult["VARIABLES"]["blog"],
					"PATH_WITH_STATUS" => $arResult["PATH_TO_STATUS_0"],
					"PATH_TO_INDEX" => $arResult["PATH_TO_INDEX"],
				),
				$component
		);
		?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.tags",
				"",
				Array(
					"BLOG_URL" => $arParams["BLOG_URL"],
					"PATH_TO_BLOG_CATEGORY" => $arResult["PATH_TO_BLOG_CATEGORY"],
					"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
					"TAGS_COUNT" => $arParams["TAGS_COUNT"]
				),
				$component
		);
		?>
	<?$this->EndViewTarget();?>
	<?//Work Field?>
	<?$this->SetViewTarget("idea_body", 100)?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.detail",
				"",
				Array(
						"RATING_TEMPLATE" => $arParams['RATING_TEMPLATE'],
						"EXT"								   => array($arResult,$arParams),
						"SPECIAL_ANSWER_ID"					 => $arBlogPostOfficial[CIdeaManagment::UFAnswerIdField],
						"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
						"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
						"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
						"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
						"PATH_TO_INDEX"			=> $arResult["PATH_TO_INDEX"],
						"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
						"PATH_TO_POST"			=> $arResult["PATH_TO_POST"],
						"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
						"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
						"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
						"PATH_TO_SMILE"			=> $arResult["PATH_TO_SMILE"],
						"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
						"ID"					=> $arResult["VARIABLES"]["post_id"],
						"CACHE_TYPE"			=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"			=> $arResult["CACHE_TIME"],
						"SET_NAV_CHAIN"			=> $arParams["SET_NAV_CHAIN"],
						"SET_TITLE"				=> $arParams["SET_TITLE"],
						"POST_PROPERTY"			=> $arParams["POST_PROPERTY"],
						"DATE_TIME_FORMAT"		=> $arResult["DATE_TIME_FORMAT"],
						"GROUP_ID" 				=> $arParams["GROUP_ID"],
						"NAME_TEMPLATE" 		=> $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" 			=> $arParams["SHOW_LOGIN"],
						"PATH_TO_CONPANY_DEPARTMENT"	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
						"PATH_TO_SONET_USER_PROFILE"	=> $arParams["PATH_TO_SONET_USER_PROFILE"],
						"PATH_TO_MESSAGES_CHAT"	=> $arParams["PATH_TO_MESSAGES_CHAT"],
						"PATH_TO_VIDEO_CALL"	=> $arParams["PATH_TO_VIDEO_CALL"],
						"SHOW_RATING" => $arParams["SHOW_RATING"],
						"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
						"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
						"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
						"POST_BIND_USER" => $arParams["POST_BIND_USER"],
				),
				$component
		);?>
		<?if($arParams["DISABLE_RSS"] != "Y"):?>
			<?$APPLICATION->IncludeComponent(
					"bitrix:blog.rss.link",
					"group",
					Array(
							"RSS1"				=> "N",
							"RSS2"				=> "Y",
							"ATOM"				=> "N",
							"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
							"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
							"GROUP_VAR"			=> $arResult["ALIASES"]["group_id"],
							"PATH_TO_POST_RSS"		=> $arResult["PATH_TO_POST_RSS"],
							"PATH_TO_RSS_ALL"	=> $arResult["PATH_TO_RSS_ALL"],
							"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
							"POST_ID"			=> $arResult["VARIABLES"]["post_id"],
							"MODE"				=> "C",
							"PARAM_GROUP_ID" 			=> $arParams["GROUP_ID"],
							"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
					),
					$component
			);?>
		<?endif;?>
		<?
		if(isset($arBlogPostOfficial[CIdeaManagment::UFAnswerIdField]) && is_array($arBlogPostOfficial[CIdeaManagment::UFAnswerIdField]) && !empty($arBlogPostOfficial[CIdeaManagment::UFAnswerIdField]))
		$arNonOfficial = array(
			"!ID" => $arBlogPostOfficial[CIdeaManagment::UFAnswerIdField],
		);
		?>
		<?$APPLICATION->IncludeComponent(
				"bitrix:idea.comment.list",
				".default",
				Array(
						"RATING_TEMPLATE" => $arParams['RATING_TEMPLATE'],
						"FILTER" => $arNonOfficial,
						"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
						"USER_VAR"		=> $arResult["ALIASES"]["user_id"],
						"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
						"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
						"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
						"PATH_TO_POST"	=> $arResult["PATH_TO_POST"],
						"PATH_TO_USER"	=> $arResult["PATH_TO_USER"],
						"PATH_TO_SMILE"	=> $arResult["PATH_TO_SMILE"],
						"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
						"ID"			=> $arResult["VARIABLES"]["post_id"],
						"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"	=> $arResult["CACHE_TIME"],
						"COMMENTS_COUNT" => $arResult["COMMENTS_COUNT"],
						"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
						"USE_ASC_PAGING"	=> $arParams["USE_ASC_PAGING"],
						//"NOT_USE_COMMENT_TITLE"	=> "Y",
						"GROUP_ID" 			=> $arParams["GROUP_ID"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
						"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
						"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
						"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
						"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
						"SHOW_RATING" => $arParams["SHOW_RATING"],
						"SMILES_COUNT" => $arParams["SMILES_COUNT"],
						"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
						"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
						"EDITOR_RESIZABLE" => $arParams["COMMENT_EDITOR_RESIZABLE"],
						"EDITOR_DEFAULT_HEIGHT" => $arParams["COMMENT_EDITOR_DEFAULT_HEIGHT"],
						"EDITOR_CODE_DEFAULT" => $arParams["COMMENT_EDITOR_CODE_DEFAULT"],
						"ALLOW_VIDEO" => $arParams["COMMENT_ALLOW_VIDEO"],
						"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
						"SHOW_SPAM" => $arParams["SHOW_SPAM"],
						"NO_URL_IN_COMMENTS" => $arParams["NO_URL_IN_COMMENTS"],
						"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["NO_URL_IN_COMMENTS_AUTHORITY"],
						"POST_BIND_USER" => $arParams["POST_BIND_USER"],
				),
				$component
		);
		?>
	<?$this->EndViewTarget();?>
	<?if($arResult["IS_CORPORTAL"] != "Y"):?>
		<div class="idea-managment-content-left">
			<?$APPLICATION->ShowViewContent("sidebar")?>
		</div>
	<?endif;?>
	<div class="idea-managment-content-right">
		<?$APPLICATION->ShowViewContent("idea_filter")?>
		<?$APPLICATION->ShowViewContent("idea_body")?>
	</div>
	<div style="clear:both;"></div>
</div>