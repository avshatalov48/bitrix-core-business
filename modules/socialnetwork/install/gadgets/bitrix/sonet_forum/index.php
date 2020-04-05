<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arGadgetParams["SHOW"] = ($arGadgetParams["SHOW"]?$arGadgetParams["SHOW"]:false);

if(!CModule::IncludeModule("socialnetwork"))
	return false;

if (strlen(trim($arGadgetParams["TITLE"])) > 0)	
	$arGadget["TITLE"] = htmlspecialcharsback($arGadgetParams["TITLE"]);
elseif ($arParams["MODE"] == "SG")
	$arGadget["TITLE"] = GetMessage('GD_SONET_FORUM_TITLE_GROUP');
elseif ($arParams["MODE"] == "SU")
	$arGadget["TITLE"] = GetMessage('GD_SONET_FORUM_TITLE_USER');

$arGadgetParams["TOPICS_COUNT"] = ($arGadgetParams["TOPICS_COUNT"] ? $arGadgetParams["TOPICS_COUNT"] : 3);
	
$arP = array(
	"FID" => $arGadgetParams["FID"],
	"SORT_BY" => "LAST_POST_DATE",
	"SORT_ORDER" => "DESC",
	"URL_TEMPLATES_MESSAGE" => $arGadgetParams["URL_TEMPLATES_MESSAGE"],
	"URL_TEMPLATES_TOPIC" => $arGadgetParams["URL_TEMPLATES_TOPIC"],
	"URL_TEMPLATES_USER" => $arGadgetParams["URL_TEMPLATES_USER"],
	"TOPICS_COUNT" => $arGadgetParams["TOPICS_COUNT"],
	"DATE_TIME_FORMAT" => $arGadgetParams["DATE_TIME_FORMAT"],
	"CACHE_TYPE" => $arGadgetParams["CACHE_TYPE"],
	"CACHE_TIME" => $arGadgetParams["CACHE_TIME"],
);
if (intval($arGadgetParams["SOCNET_GROUP_ID"]) > 0)
	$arP["SOCNET_GROUP_ID"] = $arGadgetParams["SOCNET_GROUP_ID"];
elseif	(intval($arGadgetParams["USER_ID"]) > 0)
	$arP["USER_ID"] = $arGadgetParams["USER_ID"];
	
if($arGadgetParams["SHOW"] == "Y"):
	?><table width="100%" cellspacing="2" cellpadding="2">
	<tr>
		<td><?$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:socialnetwork.forum.topic.last",
				"",
				$arP,
				false,
				Array("HIDE_ICONS"=>"Y")
		);?></td>
	</tr>
	</table><?
else:
	echo GetMessage('GD_SONET_FORUM_NOT_ALLOWED');
endif;
?>