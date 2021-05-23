<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @var array $arResult */
/** @var array $arParams */
/** @var CBitrixComponent $this */
$ajaxMode = isset($templateData['BLOG']['BLOG_FROM_AJAX']) && $templateData['BLOG']['BLOG_FROM_AJAX'];
if (!$ajaxMode)
{
	CJSCore::Init(array('window', 'ajax'));
}

if (isset($templateData['BLOG_USE']) && $templateData['BLOG_USE'] == 'Y')
{
	if ($ajaxMode)
	{
		$APPLICATION->ShowAjaxHead();
		$arBlogCommentParams = array(
			'SEO_USER' => 'N',
			'ID' => $arResult['BLOG_DATA']['BLOG_POST_ID'],
			'BLOG_URL' => $arResult['BLOG_DATA']['BLOG_URL'],
			'PATH_TO_SMILE' => $arParams['PATH_TO_SMILE'],
			'COMMENTS_COUNT' => $arParams['COMMENTS_COUNT'],
			"DATE_TIME_FORMAT" => $DB->DateFormatToPhp(FORMAT_DATETIME),
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"AJAX_POST" => $arParams["AJAX_POST"],
			"AJAX_MODE" => "Y",
			"AJAX_OPTION_HISTORY" => "N",
			"SIMPLE_COMMENT" => "Y",
			"SHOW_SPAM" => $arParams["SHOW_SPAM"],
			"NOT_USE_COMMENT_TITLE" => "Y",
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
			"PATH_TO_POST" => $arResult["URL_TO_COMMENT"],
			"IBLOCK_ID" => $templateData['BLOG']['AJAX_PARAMS']['IBLOCK_ID'],
			"ELEMENT_ID" => $templateData['BLOG']['AJAX_PARAMS']['ELEMENT_ID'],
			"NO_URL_IN_COMMENTS" => "L",
		);
		if(isset($arParams["USER_CONSENT"]))
			$arBlogCommentParams["USER_CONSENT"] = $arParams["USER_CONSENT"];
		if(isset($arParams["USER_CONSENT_ID"]))
			$arBlogCommentParams["USER_CONSENT_ID"] = $arParams["USER_CONSENT_ID"];
		if(isset($arParams["USER_CONSENT_IS_CHECKED"]))
			$arBlogCommentParams["USER_CONSENT_IS_CHECKED"] = $arParams["USER_CONSENT_IS_CHECKED"];
		if(isset($arParams["USER_CONSENT_IS_LOADED"]))
			$arBlogCommentParams["USER_CONSENT_IS_LOADED"] = $arParams["USER_CONSENT_IS_LOADED"];
		$APPLICATION->IncludeComponent(
			'bitrix:blog.post.comment',
			'adapt',
			$arBlogCommentParams,
			$this,
			array('HIDE_ICONS' => 'Y')
		);
		return;
	}
	else
	{
		$_SESSION['IBLOCK_CATALOG_COMMENTS_PARAMS_'.$templateData['BLOG']['AJAX_PARAMS']["IBLOCK_ID"].'_'.$templateData['BLOG']['AJAX_PARAMS']["ELEMENT_ID"]] = $templateData['BLOG']['AJAX_PARAMS'];
		$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
		$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/green/style.css');
		if ($templateData['BLOG']['AJAX_PARAMS']['SHOW_RATING'] == 'Y')
		{
			ob_start();
			$APPLICATION->IncludeComponent(
				"bitrix:rating.vote", $arParams['RATING_TYPE'],
				array()
			);
			ob_end_clean();
		}
	}
}

if (!$ajaxMode)
{
	if (isset($templateData['FB_USE']) && $templateData['FB_USE'] == "Y")
	{
		if (isset($arParams["FB_USER_ADMIN_ID"]) && $arParams["FB_USER_ADMIN_ID"] <> '')
		{
			$APPLICATION->AddHeadString('<meta property="fb:admins" content="'.$arParams["FB_USER_ADMIN_ID"].'"/>');
		}
		if (isset($arParams["FB_APP_ID"]) && $arParams["FB_APP_ID"] != '')
		{
			$APPLICATION->AddHeadString('<meta property="fb:app_id" content="'.$arParams["FB_APP_ID"].'"/>');
		}
	}

	if (isset($templateData['TEMPLATE_THEME']))
	{
		$APPLICATION->SetAdditionalCSS($templateData['TEMPLATE_THEME']);
	}
}