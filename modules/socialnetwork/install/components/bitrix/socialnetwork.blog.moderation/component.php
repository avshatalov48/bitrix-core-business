<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

use \Bitrix\Blog\Item\Permissions;

global $CACHE_MANAGER, $USER_FIELD_MANAGER;

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 20;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_PUBLISH");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["YEAR"] = (IntVal($arParams["YEAR"])>0 ? IntVal($arParams["YEAR"]) : false);
$arParams["MONTH"] = (IntVal($arParams["MONTH"])>0 ? IntVal($arParams["MONTH"]) : false);
$arParams["DAY"] = (IntVal($arParams["DAY"])>0 ? IntVal($arParams["DAY"]) : false);
$arParams["CATEGORY_ID"] = (IntVal($arParams["CATEGORY_ID"])>0 ? IntVal($arParams["CATEGORY_ID"]) : false);
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
{
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	$arParams["CACHE_TIME_LONG"] = intval($arParams["CACHE_TIME_LONG"]);
	if(IntVal($arParams["CACHE_TIME_LONG"]) <= 0 && IntVal($arParams["CACHE_TIME"]) > 0)
		$arParams["CACHE_TIME_LONG"] = $arParams["CACHE_TIME"];

}
else
{
	$arParams["CACHE_TIME"] = 0;
	$arParams["CACHE_TIME_LONG"] = 0;
}

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

$arParams["IMAGE_MAX_WIDTH"] = IntVal($arParams["IMAGE_MAX_WIDTH"]);
$arParams["IMAGE_MAX_HEIGHT"] = IntVal($arParams["IMAGE_MAX_HEIGHT"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$user_id = IntVal($USER->GetID());
$bGroupMode = (IntVal($arParams["SOCNET_GROUP_ID"]) > 0);

if($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(GetMessage("BLOG_MOD_TITLE"));
}

if(
	IntVal($arParams["SOCNET_GROUP_ID"]) > 0
	|| IntVal($arParams["USER_ID"]) > 0
)
{
	if (
		(
			$bGroupMode
			&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog")
		)
		|| IntVal($arParams["USER_ID"]) > 0
	)
	{
		$arResult["ERROR_MESSAGE"] = Array();
		$arResult["OK_MESSAGE"] = Array();
		$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
		$arResult["perms"] = Permissions::DENY;
		if(!$bGroupMode)
		{
			if (
				$user_id == $arParams["USER_ID"]
				|| CSocNetUser::isCurrentUserModuleAdmin()
				|| $APPLICATION->getGroupRight("blog") >= "W"

			)
			{
				$arResult["perms"] = Permissions::FULL;
			}
		}
		else
		{
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post", $bCurrentUserIsAdmin) || $APPLICATION->GetGroupRight("blog") >= "W")
				$arResult["perms"] = Permissions::FULL;
			elseif (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "moderate_post", $bCurrentUserIsAdmin))
				$arResult["perms"] = Permissions::MODERATE;
		}

		if($arResult["perms"] >= Permissions::MODERATE)
		{
			//Message delete
			if (IntVal($_GET["del_id"]) > 0)
			{
				if($_GET["success"] == "Y")
				{
					$arResult["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DELED");
				}
				else
				{
					if (check_bitrix_sessid())
					{
						$del_id = IntVal($_GET["del_id"]);
						if($arPost = CBlogPost::GetByID($del_id))
						{
							if($arResult["perms"] >= Permissions::FULL || $arPost["AUTHOR_ID"] == $arParams["USER_ID"])
							{
								CBlogPost::DeleteLog($del_id);
								if (CBlogPost::Delete($del_id))
								{
									if($bGroupMode)
									{
										CSocNetGroup::SetLastActivity($arParams["SOCNET_GROUP_ID"]);
									}
									LocalRedirect($APPLICATION->GetCurPageParam("del_id=".$del_id."&success=Y", Array("del_id", "pub_id", "sessid", "success")));
								}
								else
								{
									$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_ERROR");
								}
							}
							else
							{
								$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_NO_RIGHTS");
							}
						}
					}
					else
					{
						$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_SESSID_WRONG");
					}
				}
			}
			elseif (IntVal($_GET["pub_id"]) > 0)
			{
				if($_GET["success"] == "Y")
				{
					$arResult["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_PUB");
				}
				else
				{
					if (check_bitrix_sessid())
					{
						$pub_id = IntVal($_GET["pub_id"]);
						$arPost = CBlogPost::GetByID($pub_id);
						if(!empty($arPost) && ($arPost["AUTHOR_ID"] == $arParams["USER_ID"] || $bGroupMode) && $arPost["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
						{
							if(CBlogPost::Update($pub_id, array(
									"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
									"=DATE_PUBLISH" => $DB->GetNowFunction(),
									"SEARCH_GROUP_ID" => \Bitrix\Main\Config\Option::get("socialnetwork", "userbloggroup_id", false, SITE_ID)
								)
							))
							{
								$arParamsNotify = Array(
									"bSoNet" => true,
									"allowVideo" => $arResult["allowVideo"],
									"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
									"PATH_TO_POST" => $arParams["PATH_TO_POST"],
									"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
									"user_id" => $arPost["AUTHOR_ID"],
									"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
									"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
								);

								$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost;

								$dbRes = CSocNetLog::GetList(
									array("ID" => "DESC"),
									array(
										"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
										"SOURCE_ID" => $pub_id
									),
									false,
									false,
									array("ID", "ENTITY_TYPE", "ENTITY_ID")
								);
								if ($arRes = $dbRes->Fetch())
								{
									CBlogPost::UpdateLog($pub_id, $arPost, false, $arParamsNotify);
								}
								else
								{
									CBlogPost::Notify($arPost, false, $arParamsNotify);

									$postUrl = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("post_id" => $arPost["ID"], "user_id" => $arPost["AUTHOR_ID"]));

									CBlogPost::NotifyImPublish(array(
										"TYPE" => "POST",
										"TITLE" => $arPost["TITLE"],
										"TO_USER_ID" => $arPost["AUTHOR_ID"],
										"POST_URL" => $postUrl,
										"POST_ID" => $arPost["ID"],
									));

									$socnetRights = CBlogPost::GetSocNetPermsCode($arPost["ID"]);
									$arFieldsIM = Array(
										"TYPE" => "POST",
										"TITLE" => $arPost["TITLE"],
										"URL" => $postUrl,
										"ID" => $arPost["ID"],
										"FROM_USER_ID" => $arPost["AUTHOR_ID"],
										"TO_USER_ID" => array(),
										"TO_SOCNET_RIGHTS" => $socnetRights,
										"TO_SOCNET_RIGHTS_OLD" => array(
											"U" => array(),
											"SG" => array()
										)
									);

									CBlogPost::NotifyIm($arFieldsIM);
								}

								LocalRedirect($APPLICATION->GetCurPageParam("pub_id=".$pub_id."&success=Y", Array("del_id", "pub_id", "sessid", "success")));
							}
							else
							{
								$errorMessage = GetMessage("BLOG_BLOG_BLOG_MES_PUB_ERROR");
								if ($ex = $APPLICATION->GetException())
								{
									$errorMessage .= ': '.$ex->GetString();
								}
								$arResult["ERROR_MESSAGE"][] = $errorMessage;
							}
						}
						else
							$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_PUB_NO_RIGHTS");
					}
					else
						$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_SESSID_WRONG");
				}
			}

			$arFilter = array(
				"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_READY,
				"BLOG_USE_SOCNET" => "Y",
				"GROUP_ID" => $arParams["GROUP_ID"],
				"GROUP_SITE_ID" => SITE_ID,
			);

			if($bGroupMode)
			{
				$arFilter["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
			}
			else
			{
				$arFilter["AUTHOR_ID"] = $arParams["USER_ID"];
			}

			$dbPost = CBlogPost::GetList(
				Array("DATE_PUBLISH" => "DESC"),
				$arFilter,
				false,
				array("nPageSize"=>$arParams["MESSAGE_COUNT"], "bShowAll" => false),
				array("ID", "TITLE", "BLOG_ID", "AUTHOR_ID", "DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DATE_PUBLISH", "PUBLISH_STATUS", "ENABLE_COMMENTS", "VIEWS", "NUM_COMMENTS", "CATEGORY_ID", "CODE", "BLOG_OWNER_ID", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "MICRO")
			);

			$arResult["NAV_STRING"] = $dbPost->GetPageNavString(GetMessage("MESSAGE_COUNT"), $arParams["NAV_TEMPLATE"]);
			$arResult["POST"] = Array();

			while($arPost = $dbPost->GetNext())
			{
				$arPost["perms"] = $arResult["perms"];
				$arPost["urlToPub"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("pub_id=".$arPost["ID"]."&".bitrix_sessid_get(), Array("del_id", "sessid", "success", "pub_id")));

				$arPost["ADIT_MENU"][6] = Array(
					"text_php" => GetMessage("BLOG_MOD_PUB"),
					"href" => $arPost["urlToPub"],
				);

				if($arResult["perms"] >= BLOG_PERMS_FULL || $arPost["AUTHOR_ID"] == $user_id)
				{
					$arPost["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$arPost["ID"]."&".bitrix_sessid_get(), Array("del_id", "sessid", "success", "pub_id")));
					$arPost["ADIT_MENU"][7] = Array(
						"text_php" => GetMessage("BLOG_MOD_DELETE"),
						"onclick" => "function() { if(confirm('".GetMessage("BLOG_MOD_DELETE_CONFIRM")."')) window.location='".$arPost["urlToDelete"]."';  this.popupWindow.close();}",
					);
				}
				$arResult["POST"][] = $arPost;
			}
		}
		else
		{
			$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_MOD_NO_RIGHTS");
		}
	}
	else
	{
		$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_SONET_MODULE_NOT_AVAIBLE");
	}
}
else
{
	$arResult["ERROR_MESSAGE"][] = (
		$bGroupMode
			? GetMessage("BLOG_MOD_NO_SOCNET_GROUP")
			: GetMessage("BLOG_MOD_EMPTY_SOCNET_USER")
	);
}
$this->IncludeComponentTemplate();
?>
