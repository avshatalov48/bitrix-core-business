<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * @var array $arResult
 * @param CBitrixComponentTemplate $this
 */

$arResult["VARIABLES"] = (is_array($arResult["VARIABLES"]) ? $arResult["VARIABLES"] : array());
$pageMode = ($this->__component && $this->__component->__pageMode ? $this->__component->__pageMode : "index");
$arFilter = array();
$categoryCode = array();
//Prepare Filter
$arResult["VARIABLES"]["user_id"] = (array_key_exists("user_id", $arResult["VARIABLES"]) ? intval($arResult["VARIABLES"]["user_id"]) : false);
if ($arResult["VARIABLES"]["user_id"])
	$arFilter["AUTHOR_ID"] = $arResult["VARIABLES"]["user_id"];
$arResult["VARIABLES"]["status_code"] = (array_key_exists("status_code", $arResult["VARIABLES"]) ? ToUpper($arResult["VARIABLES"]["status_code"]) : false);
if ($arResult["VARIABLES"]["status_code"])
{
	$arFilter["IDEA_STATUS"] = $arResult["VARIABLES"]["status_code"];
	$arResult["VARIABLES"]["~status_code"] = ToLower($arResult["VARIABLES"]["status_code"]);
}
if (array_key_exists("category_1", $arResult["VARIABLES"]))
{
	$arFilter["IDEA_PARENT_CATEGORY_CODE"] = $categoryCode["CATEGORY_1"] = ToUpper($arResult["VARIABLES"]["category_1"]);
	$categoryCode["~CATEGORY_1"] = ToLower($categoryCode["CATEGORY_1"]);
	if (array_key_exists("category_2", $arResult["VARIABLES"]))
	{
		$arFilter["IDEA_PARENT_CATEGORY_CODE"] = $categoryCode["CATEGORY_2"] = ToUpper($arResult["VARIABLES"]["category_2"]);
		$categoryCode["~CATEGORY_2"] = ToLower($categoryCode["CATEGORY_2"]);
	}
}
//Prepare filter for life search (if pagination used)
if($arResult["LIFE_SEARCH_QUERY"] <> '')
	$arFilter["~TITLE"] = '%'.$arResult["LIFE_SEARCH_QUERY"].'%';
?>
	<?//Side bar tools?>
	<?$this->SetViewTarget("sidebar", 100)?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:idea.category.list",
			"",
			Array(
				"IBLOCK_CATEGORIES" => $arParams["IBLOCK_CATEGORIES"],
				"PATH_TO_CATEGORY_1" => $arResult["PATH_TO_CATEGORY_1"],
				"PATH_TO_CATEGORY_2" => $arResult["PATH_TO_CATEGORY_2"],
				"SELECTED_CATEGORY" => $arFilter["IDEA_PARENT_CATEGORY_CODE"]
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
	<?$this->SetViewTarget("idea_filter", 100)?>
		<?if($arParams["DISABLE_RSS"] != "Y"):
			$pathPostfix = ($pageMode == "index" ? "" : "_".ToUpper(str_replace(array("_1", "_2"), "", $pageMode)));/*.
				(strpos($pageMode, "status") !== false ? "_STATUS" : "")*/;
			?><?
$APPLICATION->IncludeComponent(
				"bitrix:blog.rss.link",
				"",
				Array(
					"RSS1"				=> "N",
					"RSS2"				=> "Y",
					"ATOM"				=> "N",
					"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
					"PATH_TO_RSS"		=> CComponentEngine::MakePathFromTemplate(
							$arResult["PATH_TO_RSS".$pathPostfix],
							array(
								"category" => ToLower($arFilter["IDEA_PARENT_CATEGORY_CODE"]),
								"status_code" => $arResult["VARIABLES"]["~status_code"],
								"user_id" => $arFilter["AUTHOR_ID"]
							)
						),
					"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
				),
				$component
			);
			?>
		<?endif;?>
		<?$pathPostfix = ToUpper(str_replace("_status", "", ($pageMode == "index" || $pageMode == "status_0" ? "" : $pageMode)));
		$APPLICATION->IncludeComponent(
			"bitrix:idea.filter",
			"",
			Array(
				"PATH_TO_CATEGORY_WITH_STATUS" => CComponentEngine::MakePathFromTemplate(
					$arResult["PATH_TO_".($pathPostfix == "" ? "STATUS_0" : $pathPostfix."_STATUS")],
					array(
						"category_1" => $categoryCode["~CATEGORY_1"],
						"category_2" => $categoryCode["~CATEGORY_2"],
						"user_id" => $arFilter["AUTHOR_ID"]
					)
				),
				"PATH_TO_CATEGORY" => CComponentEngine::MakePathFromTemplate(
					$arResult["PATH_TO_".($pathPostfix == "" ? "INDEX" : $pathPostfix)],
					array(
						"category_1" => $categoryCode["~CATEGORY_1"],
						"category_2" => $categoryCode["~CATEGORY_2"],
						"user_id" => $arFilter["AUTHOR_ID"]
					)
				),
				"SELECTED_STATUS" => $arResult["VARIABLES"]["status_code"],
				"SELECTED_USER_ID" => $arResult["VARIABLES"]["user_id"],
				"CATEGORIES" => $CategoryCode,
				"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
			),
			$component
		);
		?>
	<?$this->EndViewTarget();?>
	<?$this->SetViewTarget("idea_body", 100)?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:idea.list",
			"",
			Array(
				"RATING_TEMPLATE" => $arParams['RATING_TEMPLATE'],
				"SORT_BY1" => $_SESSION["IDEA_SORT_ORDER"],
				"IBLOCK_CATEGORIES" => $arParams["IBLOCK_CATEGORIES"],
				"EXT_FILTER" => $arFilter,
				"MESSAGE_COUNT"			=> $arResult["MESSAGE_COUNT"],
				"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
				"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
				"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
				"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
				"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
				"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
				"PATH_TO_POST"			=> $arResult["PATH_TO_POST"],
				"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
				"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
				"PATH_TO_SMILE"			=> $arResult["PATH_TO_SMILE"],
				"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
				"YEAR"					=> $arResult["VARIABLES"]["year"],
				"MONTH"					=> $arResult["VARIABLES"]["month"],
				"DAY"					=> $arResult["VARIABLES"]["day"],
				"CATEGORY_ID"			=> $arResult["VARIABLES"]["tag"],
				"CACHE_TYPE"			=> $arResult["CACHE_TYPE"],
				"CACHE_TIME"			=> $arResult["CACHE_TIME"],
				"CACHE_TIME_LONG"		=> $arResult["CACHE_TIME_LONG"],
				"SET_NAV_CHAIN"			=> $arParams["SET_NAV_CHAIN"],
				"POST_PROPERTY_LIST"	=> $arParams["POST_PROPERTY_LIST"],
				"DATE_TIME_FORMAT"		=> $arParams["DATE_TIME_FORMAT"],
				"NAV_TEMPLATE"			=> $arParams["NAV_TEMPLATE"],
				"GROUP_ID" 				=> $arParams["GROUP_ID"],
				"NAME_TEMPLATE" 		=> $arParams["NAME_TEMPLATE"],
				"SHOW_LOGIN" 			=> $arParams["SHOW_LOGIN"],
				"PATH_TO_CONPANY_DEPARTMENT" 	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_SONET_USER_PROFILE" 	=> $arParams["PATH_TO_SONET_USER_PROFILE"],
				"PATH_TO_MESSAGES_CHAT"	=> $arParams["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_VIDEO_CALL" 	=> $arParams["PATH_TO_VIDEO_CALL"],
				"SHOW_RATING" => $arParams["SHOW_RATING"],
				"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
				"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
				"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
				"AR_RESULT" => $arResult,
				"AR_PARAMS" => $arParams,
				"POST_BIND_USER" => $arParams["POST_BIND_USER"],
			),
			$component
	);?>
	<?$this->EndViewTarget();?>
<?
if($USER->IsAuthorized())
{
	$notifyEmail = new \Bitrix\Idea\NotifyEmail();
	if (array_key_exists("action", $_REQUEST) && $_REQUEST["action"] == "subscribe" && check_bitrix_sessid())
	{
		$notifyEmail->addCategory($arFilter["IDEA_PARENT_CATEGORY_CODE"], "NEW IDEAS");
		LocalRedirect($APPLICATION->GetCurPageParam("", array("action", "sessid")));
	}
	else
	{
		$subscribes = $notifyEmail->getAscendedCategories($arFilter["IDEA_PARENT_CATEGORY_CODE"]);
		if ($subscribes !== false && empty($subscribes))
		{
			array_unshift($arResult["ACTIONS"]["MENU"]["MENU"], array("SEPARATOR" => true));
			array_unshift($arResult["ACTIONS"]["MENU"]["MENU"], array(
				"TEXT" => GetMessage("IDEA_ADD_SUBSCRIPTION"),
				"ONCLICK" => "top.window.location.href='".CUtil::JSEscape($APPLICATION->GetCurPageParam("action=subscribe&".bitrix_sessid_get(), array("action", "sessid")))."';",
			));
		}
	}
}

?>
<div class="idea-managment-content">
	<?$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arResult["ACTIONS"]
		),
		$component
	);?>
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
<?
if($arParams["SET_NAV_CHAIN"] == "Y" || $arParams["SET_TITLE"] == "Y")
{
	if (mb_strpos($pageMode, "user") !== false)
	{
		$title = "";
		if($arResult["VARIABLES"]["user_id"] == $USER->GetID())
			$title = GetMessage("IDEA_USER_IDEA_LIST_MINE");
		elseif($arUser = $USER->GetByID($arFilter["AUTHOR_ID"])->Fetch())
			$title = GetMessage("IDEA_USER_IDEA_LIST_USER", array("#USER_NAME#" => CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, true)));
		if($arParams["SET_NAV_CHAIN"] == "Y")
			$APPLICATION->AddChainItem($title, CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_IDEAS"], array("user_id" => $arResult["VARIABLES"]["user_id"])));
		if($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle($title);
	}
	else
	{
		$arCategoryList = ($arParams["SET_NAV_CHAIN"] == "Y" || $arParams["SET_TITLE"] == "Y" ? CIdeaManagment::getInstance()->Idea()->GetCategoryList() : array());
		//Set ChainItem
		if($arParams["SET_NAV_CHAIN"] == "Y")
		{
			foreach ($categoryCode as $key => $val)
			{
				if (array_key_exists($val, $arCategoryList))
				{
					$APPLICATION->AddChainItem($arCategoryList[$val]["NAME"],
						CComponentEngine::MakePathFromTemplate(
							$arResult["PATH_TO_".$key.(mb_strpos($pageMode, "status") !== false ? "_STATUS" : "")],
							array(
								"category_1" => $categoryCode["~CATEGORY_1"],
								"category_2" => $categoryCode["~CATEGORY_2"],
								"status" => $arResult["VARIABLES"]["status_code"]
							)
						)
					);
				}
			}
		}
		//Set Title
		if($arParams["SET_TITLE"] == "Y")
		{
			$val = $arFilter["IDEA_PARENT_CATEGORY_CODE"];
			if (!!$val && array_key_exists($val, $arCategoryList))
				$APPLICATION->SetTitle(GetMessage("IDEA_CATEGORY_PAGE_TITLE", array("#CATEGORY_NAME#" => $arCategoryList[$val]["NAME"])));
			else
				$APPLICATION->SetTitle(GetMessage("IDEA_INDEX_PAGE_TITLE"));
		}
	}
}
?>