<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arGadgetParams["SHOW"] = ($arGadgetParams["SHOW"]?$arGadgetParams["SHOW"]:false);

if(!CModule::IncludeModule("socialnetwork"))
	return false;
	
if (trim($arGadgetParams["TITLE"]) <> '')
	$arGadget["TITLE"] = htmlspecialcharsback($arGadgetParams["TITLE"]);
elseif ($arParams["MODE"] == "SG")
	$arGadget["TITLE"] = GetMessage('GD_SONET_BLOG_TITLE_GROUP');
elseif ($arParams["MODE"] == "SU")
	$arGadget["TITLE"] = GetMessage('GD_SONET_BLOG_TITLE_USER');
	
$arGadgetParams["TEMPLATE_NAME"] = ($arGadgetParams["TEMPLATE_NAME"]?$arGadgetParams["TEMPLATE_NAME"]:"main_page");
$arGadgetParams["SHOW_TITLE"] = ($arGadgetParams["SHOW_TITLE"]?$arGadgetParams["SHOW_TITLE"]:"N");
$arGadgetParams["TITLE"] = ($arGadgetParams["TITLE"]?$arGadgetParams["TITLE"]:"");
$arGadgetParams["PATH_TO_BLOG"] = ($arGadgetParams["PATH_TO_BLOG"]?$arGadgetParams["PATH_TO_BLOG"]:"/company/personal/user/#user_id#/blog/");
$arGadgetParams["PATH_TO_POST"] = ($arGadgetParams["PATH_TO_POST"]?$arGadgetParams["PATH_TO_POST"]:"/company/personal/user/#user_id#/blog/#post_id#/");
$arGadgetParams["PATH_TO_GROUP_BLOG_POST"] = ($arGadgetParams["PATH_TO_GROUP_BLOG_POST"]?$arGadgetParams["PATH_TO_GROUP_BLOG_POST"]:"/workgroups/group/#group_id#/blog/#post_id#/");
$arGadgetParams["PATH_TO_USER"] = ($arGadgetParams["PATH_TO_USER"]?$arGadgetParams["PATH_TO_USER"]:"/company/personal/user/#user_id#/");
$arGadgetParams["PATH_TO_SMILE"] = ($arGadgetParams["PATH_TO_SMILE"]?$arGadgetParams["PATH_TO_SMILE"]:"");
$arGadgetParams["CACHE_TYPE"] = ($arGadgetParams["CACHE_TYPE"]?$arGadgetParams["CACHE_TYPE"]:"A");
$arGadgetParams["CACHE_TIME"] = ($arGadgetParams["CACHE_TIME"]?$arGadgetParams["CACHE_TIME"]:"180");
$arGadgetParams["BLOG_VAR"] = ($arGadgetParams["BLOG_VAR"]?$arGadgetParams["BLOG_VAR"]:"");
$arGadgetParams["POST_VAR"] = ($arGadgetParams["POST_VAR"]?$arGadgetParams["POST_VAR"]:"");
$arGadgetParams["USER_VAR"] = ($arGadgetParams["POST_VAR"]?$arGadgetParams["USER_VAR"]:"");
$arGadgetParams["PAGE_VAR"] = ($arGadgetParams["POST_VAR"]?$arGadgetParams["PAGE_VAR"]:"");
$arGadgetParams["DATE_TIME_FORMAT"] = ($arGadgetParams["DATE_TIME_FORMAT"] ? $arGadgetParams["DATE_TIME_FORMAT"] : $arParams["DATE_TIME_FORMAT"]);
$arGadgetParams["MESSAGE_COUNT"] = ($arGadgetParams["MESSAGE_COUNT"]?$arGadgetParams["MESSAGE_COUNT"]:6);
$arGadgetParams["MESSAGE_LENGTH"] = ($arGadgetParams["MESSAGE_LENGTH"]?$arGadgetParams["MESSAGE_LENGTH"]:100);
$arGadgetParams["SOCNET_GROUP_ID"] = ($arGadgetParams["SOCNET_GROUP_ID"]?$arGadgetParams["SOCNET_GROUP_ID"]:false);
$arGadgetParams["GROUP_ID"] = ($arGadgetParams["GROUP_ID"]?$arGadgetParams["GROUP_ID"]:false);

if($arGadgetParams["SHOW"] == "Y"):

	$arP = Array(
			"MESSAGE_COUNT" => $arGadgetParams["MESSAGE_COUNT"],
			"MESSAGE_LENGTH" => $arGadgetParams["MESSAGE_LENGTH"],
			"PATH_TO_BLOG" => $arGadgetParams["PATH_TO_BLOG"],
			"PATH_TO_POST" => $arGadgetParams["PATH_TO_POST"],
			"PATH_TO_GROUP_BLOG_POST" => $arGadgetParams["PATH_TO_GROUP_BLOG_POST"],
			"PATH_TO_GROUP_BLOG" => $arGadgetParams["PATH_TO_GROUP_BLOG"],
			"PATH_TO_USER" => $arGadgetParams["PATH_TO_USER"],
			"PATH_TO_SMILE" => $arGadgetParams["PATH_TO_SMILE"],
			"CACHE_TYPE" => $arGadgetParams["CACHE_TYPE"],
			"CACHE_TIME" => $arGadgetParams["CACHE_TIME"],
			"BLOG_VAR" => $arGadgetParams["BLOG_VAR"],
			"POST_VAR" => $arGadgetParams["POST_VAR"],
			"USER_VAR" => $arGadgetParams["USER_VAR"],
			"PAGE_VAR" => $arGadgetParams["PAGE_VAR"],
			"DATE_TIME_FORMAT" => $arGadgetParams["DATE_TIME_FORMAT"],
			"GROUP_ID" => $arGadgetParams["GROUP_ID"],
			"USE_SOCNET" => "Y",
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
		);

	if (intval($arGadgetParams["SOCNET_GROUP_ID"]) > 0)
		$arP["SOCNET_GROUP_ID"] = $arGadgetParams["SOCNET_GROUP_ID"];
	elseif	(intval($arGadgetParams["USER_ID"]) > 0)
		$arP["USER_ID"] = $arGadgetParams["USER_ID"];

	if($arGadgetParams["SHOW_TITLE"] == "Y"):
		?><h4><?= $arGadgetParams["TITLE"] ?></h4><?
	endif;?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:blog.new_posts",
		$arGadgetParams["TEMPLATE_NAME"],
		$arP,
		false,
		Array("HIDE_ICONS"=>"Y")
	);?><?
else:
	echo GetMessage('GD_SONET_BLOG_NOT_ALLOWED');
endif;?>
