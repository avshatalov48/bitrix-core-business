<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

$arResult['VOTE_AVAILABLE'] = isset($arParams['VOTE_AVAILABLE'])? $arParams['VOTE_AVAILABLE'] == 'Y'? 'Y': 'N' : 'Y';
if ($arResult['VOTE_AVAILABLE'] == 'Y')
	$arAllowVote = CRatings::CheckAllowVote($arParams);

$sRatingVoteType = COption::GetOptionString("main", "rating_vote_type", "standart");
$sRatingTemplate = $this->GetTemplateName();
if ($sRatingTemplate == "" || $sRatingTemplate == ".default")
{
	$sRatingTemplate = COption::GetOptionString("main", "rating_vote_template", $sRatingVoteType == "like"? "like": "standart");
	$this->SetTemplateName($sRatingTemplate);
}
$arResult['ENTITY_TYPE_ID'] = $arParams['ENTITY_TYPE_ID'];
$arResult['ENTITY_ID'] = intval($arParams['ENTITY_ID']);
$arResult['OWNER_ID'] = intval($arParams['OWNER_ID']);
$arResult['TOTAL_VALUE'] = (float)($arParams['TOTAL_VALUE'] ?? 0);
$arResult['TOTAL_VOTES'] = (int)($arParams['TOTAL_VOTES'] ?? 0);
$arResult['TOTAL_POSITIVE_VOTES'] = (int)($arParams['TOTAL_POSITIVE_VOTES'] ?? 0);
$arResult['TOTAL_NEGATIVE_VOTES'] = (int)($arParams['TOTAL_NEGATIVE_VOTES'] ?? 0);
$arResult['USER_HAS_VOTED'] = (!isset($arParams['USER_HAS_VOTED']) || $arParams['USER_HAS_VOTED'] !== 'Y'? 'N': 'Y');

$arResult['AJAX_MODE'] = (!isset($arParams['AJAX_MODE']) || $arParams['AJAX_MODE'] !== 'Y'? 'N': 'Y');

$arResult['USER_VOTE'] = (float)($arParams['USER_VOTE'] ?? 0);
$arResult['ALLOW_VOTE'] = $arAllowVote;
$arResult['PATH_TO_USER_PROFILE'] = $arParams['PATH_TO_USER_PROFILE'];

$isLikeTemplate = in_array($sRatingTemplate, array("like", "like_graphic", "mobile_like", "like_react"));
if ($isLikeTemplate)
{
	$arResult['TOTAL_VOTES'] = $arResult['TOTAL_POSITIVE_VOTES'];
}

if (!array_key_exists('TOTAL_VALUE', $arParams) ||
	!array_key_exists('TOTAL_VOTES', $arParams) ||
	!array_key_exists('TOTAL_POSITIVE_VOTES', $arParams) ||
	!array_key_exists('TOTAL_NEGATIVE_VOTES', $arParams) ||
	!array_key_exists('USER_HAS_VOTED', $arParams) ||
	!array_key_exists('USER_VOTE', $arParams))
{
	$arComponentVoteResult = CRatings::GetRatingVoteResult($arResult['ENTITY_TYPE_ID'], $arResult['ENTITY_ID']);
	if (!empty($arComponentVoteResult))
	{
		$arResult['TOTAL_VALUE'] = $arComponentVoteResult['TOTAL_VALUE'];
		$arResult['TOTAL_VOTES'] = $arComponentVoteResult['TOTAL_VOTES'];
		$arResult['TOTAL_POSITIVE_VOTES'] = $arComponentVoteResult['TOTAL_POSITIVE_VOTES'];
		$arResult['TOTAL_NEGATIVE_VOTES'] = $arComponentVoteResult['TOTAL_NEGATIVE_VOTES'];
		$arResult['USER_VOTE'] = $arComponentVoteResult['USER_VOTE'];
		$arResult['USER_HAS_VOTED'] = $arComponentVoteResult['USER_HAS_VOTED'];
		$arResult['USER_REACTION'] = $arComponentVoteResult['USER_REACTION'];
		$arResult['REACTIONS_LIST'] = $arComponentVoteResult['REACTIONS_LIST'];

		if (in_array($sRatingTemplate, array("like", "like_graphic", "mobile_like")))
		{
			$arResult['TOTAL_VOTES'] = $arComponentVoteResult['TOTAL_POSITIVE_VOTES'];
		}
	}
}

$arResult['VOTE_BUTTON'] = $arResult['USER_HAS_VOTED'] == 'Y'? ($arResult['USER_VOTE'] >= 0? 'PLUS': 'MINUS'): 'NONE';
if ($isLikeTemplate && $arResult['VOTE_BUTTON'] == 'MINUS')
	$arResult['USER_HAS_VOTED'] = 'N';

if (!$arResult['ALLOW_VOTE']['RESULT'])
	$arResult['VOTE_AVAILABLE'] = 'N';

$arResult['VOTE_TITLE'] = (
	$arResult['TOTAL_VOTES'] == 0
		? GetMessage("RATING_COMPONENT_NO_VOTES")
		: sprintf(GetMessage("RATING_COMPONENT_DESC"), $arResult['TOTAL_VOTES'], $arResult['TOTAL_POSITIVE_VOTES'], $arResult['TOTAL_NEGATIVE_VOTES'])
);
$arResult['VOTE_ID'] = (
	!empty($arParams['VOTE_ID'])
		? $arParams['VOTE_ID']
		: $arResult['ENTITY_TYPE_ID'].'-'.$arResult['ENTITY_ID'].'-'.($arParams["VOTE_RAND"] > 0 ? intval($arParams["VOTE_RAND"]) : (time()+rand(0, 1000)))
);

$isMobileLog = defined("BX_MOBILE_LOG") && BX_MOBILE_LOG == true;

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
{
	if (!defined('MAIN_RATING_VOTE_JS_INCLUDE'))
	{
		define("MAIN_RATING_VOTE_JS_INCLUDE", true);

		if (!$isMobileLog)
			CJSCore::Init(array('popup', 'ajax'));

		if (!$isMobileLog)
		{
			if ($isLikeTemplate)
				$APPLICATION->AddHeadScript("/bitrix/js/main/rating_like.js");
			else
				$APPLICATION->AddHeadScript("/bitrix/js/main/rating.js");
		}
	}
	if (!$isMobileLog)
		$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/like/popup.css");

	if ($isLikeTemplate)
	{
		$arResult['RATING_TEXT_LIKE_Y'] = COption::GetOptionString("main", "rating_text_like_y", GetMessage("RATING_TEXT_LIKE_Y"));
		$arResult['RATING_TEXT_LIKE_N'] = COption::GetOptionString("main", "rating_text_like_n", GetMessage("RATING_TEXT_LIKE_N"));
		$arResult['RATING_TEXT_LIKE_D'] = COption::GetOptionString("main", "rating_text_like_d", GetMessage("RATING_TEXT_LIKE_D"));
	}
	else if ($sRatingTemplate == "standart_text")
	{
		$arResult['RATING_TEXT_A'] = GetMessage("RATING_TEXT_A");
		$arResult['RATING_TEXT_D'] = GetMessage("RATING_TEXT_D");
		$arResult['RATING_TEXT_Y'] = GetMessage("RATING_TEXT_Y");
		$arResult['RATING_TEXT_N'] = GetMessage("RATING_TEXT_N");
	}
	$this->IncludeComponentTemplate();
}

return $arResult;
