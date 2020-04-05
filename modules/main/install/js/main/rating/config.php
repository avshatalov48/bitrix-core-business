<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/main/rating_like.js");

Loc::loadLanguageFile(__FILE__);

return array(
	'js' => '/bitrix/js/main/rating/main.rating.js',
	'lang_additional' => array(
		'RATING_LIKE_REACTION_DEFAULT' => CUtil::JSEscape(\CRatings::REACTION_DEFAULT),
		'RATING_LIKE_POPUP_ALL' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_POPUP_ALL')),
		'RATING_LIKE_TOP_TEXT_YOU_2_MORE' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_YOU_2_MORE')),
		'RATING_LIKE_TOP_TEXT_YOU_1_MORE' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_YOU_1_MORE')),
		'RATING_LIKE_TOP_TEXT_YOU_2' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_YOU_2')),
		'RATING_LIKE_TOP_TEXT_YOU_1' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_YOU_1')),
		'RATING_LIKE_TOP_TEXT_2_MORE' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_2_MORE')),
		'RATING_LIKE_TOP_TEXT_1_MORE' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_1_MORE')),
		'RATING_LIKE_TOP_TEXT_2' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_2')),
		'RATING_LIKE_TOP_TEXT_1' => CUtil::JSEscape(Loc::getMessage('RATING_LIKE_TOP_TEXT_1')),
		'RATING_LIKE_EMOTION_LIKE_CALC' => CUtil::JSEscape(\CRatingsComponentsMain::getRatingLikeMessage('LIKE')),
		'RATING_LIKE_EMOTION_KISS_CALC' => CUtil::JSEscape(\CRatingsComponentsMain::getRatingLikeMessage('KISS')),
		'RATING_LIKE_EMOTION_LAUGH_CALC' => CUtil::JSEscape(\CRatingsComponentsMain::getRatingLikeMessage('LAUGH')),
		'RATING_LIKE_EMOTION_WONDER_CALC' => CUtil::JSEscape(\CRatingsComponentsMain::getRatingLikeMessage('WONDER')),
		'RATING_LIKE_EMOTION_CRY_CALC' => CUtil::JSEscape(\CRatingsComponentsMain::getRatingLikeMessage('CRY')),
		'RATING_LIKE_EMOTION_ANGRY_CALC' => CUtil::JSEscape(\CRatingsComponentsMain::getRatingLikeMessage('ANGRY'))
	),
	'rel' => array('popup')
);