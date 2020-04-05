<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$arResult['AVAILABLE'] = \Bitrix\Socialnetwork\Item\UserContentView::getAvailability();

if ($arResult['AVAILABLE'])
{
	CJSCore::Init(array('popup', 'ajax', 'content_view'));
}

if (!empty($arParams['CONTENT_ID']))
{
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/like/popup.css");

	$arResult['CONTENT_ID'] = $arParams['CONTENT_ID'];
	$arResult['CONTENT_VIEW_CNT'] = (isset($arParams["CONTENT_VIEW_CNT"]) ? intval($arParams["CONTENT_VIEW_CNT"]) : 0);

	if (!empty($arParams['PATH_TO_USER_PROFILE']))
	{
		$arResult['PATH_TO_USER_PROFILE'] = $arParams['PATH_TO_USER_PROFILE'];
	}
	else
	{
		$userPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', SITE_DIR.'company/personal/');
		$arResult['PATH_TO_USER_PROFILE'] = $userPage.'user/#USER_ID#/';
	}

	if (
		$USER->isAuthorized()
		&& \Bitrix\Main\Loader::includeModule('pull')
		&& \CPullOptions::getNginxStatus()
	)
	{
		\CPullWatch::Add($USER->getId(), 'CONTENTVIEW'.$arParams['CONTENT_ID']);
	}

	$this->IncludeComponentTemplate();
}


?>