<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentProps = CComponentUtil::GetComponentProps("bitrix:blog.new_posts", $arCurrentValues);

$arParameters = Array(
		"PARAMETERS"=> Array(
			"PATH_TO_BLOG"=>$arComponentProps["PARAMETERS"]["PATH_TO_BLOG"],
			"PATH_TO_POST"=>$arComponentProps["PARAMETERS"]["PATH_TO_POST"],
			"PATH_TO_GROUP_BLOG_POST"=>$arComponentProps["PARAMETERS"]["PATH_TO_GROUP_BLOG_POST"],
			"PATH_TO_USER"=>$arComponentProps["PARAMETERS"]["PATH_TO_USER"],
			"CACHE_TYPE"=>$arComponentProps["PARAMETERS"]["CACHE_TYPE"],
			"CACHE_TIME"=>$arComponentProps["PARAMETERS"]["CACHE_TIME"],
		),
		"USER_PARAMETERS"=> Array(
			"MESSAGE_COUNT"=>$arComponentProps["PARAMETERS"]["MESSAGE_COUNT"],
			"MESSAGE_LENGTH"=>$arComponentProps["PARAMETERS"]["MESSAGE_LENGTH"],
			"DATE_TIME_FORMAT"=>$arComponentProps["PARAMETERS"]["DATE_TIME_FORMAT"],
		),
	);

$arParameters["PARAMETERS"]["PATH_TO_BLOG"]["DEFAULT"] = "/company/personal/user/#user_id#/blog/";
$arParameters["PARAMETERS"]["PATH_TO_POST"]["DEFAULT"] = "/company/personal/user/#user_id#/blog/#post_id#/";
$arParameters["PARAMETERS"]["PATH_TO_GROUP_BLOG_POST"]["DEFAULT"] = "/workgroups/group/#group_id#/blog/#post_id#/";
$arParameters["PARAMETERS"]["PATH_TO_USER"]["DEFAULT"] = "/company/personal/user/#user_id#/";

$arParameters["PARAMETERS"]["CACHE_TYPE"]["DEFAULT"] = "A";
$arParameters["PARAMETERS"]["CACHE_TIME"]["DEFAULT"] = "180";

$arParameters["USER_PARAMETERS"]["DATE_TIME_FORMAT"]["DEFAULT"] = $arParams["DATE_TIME_FORMAT"];
$arParameters["USER_PARAMETERS"]["MESSAGE_COUNT"]["DEFAULT"] = 6;
$arParameters["USER_PARAMETERS"]["MESSAGE_LENGTH"]["DEFAULT"] = 100;
?>
