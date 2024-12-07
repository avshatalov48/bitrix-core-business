<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/install/js/main/rating/config.php');

return [
	'css' => 'main.rating.css',
	'js' => 'main.rating.js',
	'lang_additional' => [
		'RATING_LIKE_REACTION_DEFAULT' => \CRatings::REACTION_DEFAULT,
		'RATING_LIKE_POPUP_ALL' => Loc::getMessage('RATING_LIKE_POPUP_ALL'),
		'RATING_LIKE_TOP_TEXT2_YOU_2_MORE' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_YOU_2_MORE'),
		'RATING_LIKE_TOP_TEXT2_YOU_1_MORE' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_YOU_1_MORE'),
		'RATING_LIKE_TOP_TEXT2_YOU_2' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_YOU_2'),
		'RATING_LIKE_TOP_TEXT2_YOU_1' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_YOU_1'),
		'RATING_LIKE_TOP_TEXT2_2_MORE' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_2_MORE'),
		'RATING_LIKE_TOP_TEXT2_1_MORE' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_1_MORE'),
		'RATING_LIKE_TOP_TEXT2_2' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_2'),
		'RATING_LIKE_TOP_TEXT2_1' => Loc::getMessage('RATING_LIKE_TOP_TEXT2_1'),
		'RATING_LIKE_EMOTION_LIKE_CALC' => \CRatingsComponentsMain::getRatingLikeMessage('LIKE'),
		'RATING_LIKE_EMOTION_KISS_CALC' => \CRatingsComponentsMain::getRatingLikeMessage('KISS'),
		'RATING_LIKE_EMOTION_LAUGH_CALC' => \CRatingsComponentsMain::getRatingLikeMessage('LAUGH'),
		'RATING_LIKE_EMOTION_WONDER_CALC' => \CRatingsComponentsMain::getRatingLikeMessage('WONDER'),
		'RATING_LIKE_EMOTION_CRY_CALC' => \CRatingsComponentsMain::getRatingLikeMessage('CRY'),
		'RATING_LIKE_EMOTION_ANGRY_CALC' => \CRatingsComponentsMain::getRatingLikeMessage('ANGRY'),
		'RATING_LIKE_EMOTION_FACEPALM_CALC' => \CRatingsComponentsMain::getRatingLikeMessage('FACEPALM'),
	],
	'rel' => [
		'ui.design-tokens',
		'ui.lottie',
		'ajax',
		'popup'
	],
];
