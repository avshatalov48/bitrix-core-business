<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserField\Types\UrlType;
use Bitrix\Main\UserField\TypeBase;

/**
 * Class CUserTypeUrl
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeUrl extends CUserTypeString
{
	const USER_TYPE_ID = UrlType::USER_TYPE_ID;

	function getUserTypeDescription()
	{
		return UrlType::getUserTypeDescription();
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return UrlType::renderView($userField, $additionalParameters);
	}

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		return UrlType::renderEdit($userField, $additionalParameters);
	}

	function getSettingsHtml($userField, $arHtmlControl, $bVarsFromForm)
	{
		return UrlType::renderSettings($userField, $arHtmlControl, $bVarsFromForm);
	}

	function getEditFormHtml($userField, $arHtmlControl)
	{
		return UrlType::renderEditForm($userField, $arHtmlControl);
	}

	function getAdminListViewHtml($userField, $arHtmlControl)
	{
		return UrlType::renderAdminListView($userField, $arHtmlControl);
	}

	function getAdminListEditHtml($userField, $arHtmlControl)
	{
		return UrlType::renderAdminListEdit($userField, $arHtmlControl);
	}

	function getFilterHtml($userField, $arHtmlControl)
	{
		return UrlType::renderFilter($userField, $arHtmlControl);
	}

	public static function getPublicText($userField)
	{
		return UrlType::renderText($userField);
	}

	function prepareSettings($userField)
	{
		return UrlType::prepareSettings($userField);
	}

	protected static function encodeUrl($url)
	{
		if(!preg_match('/^(callto:|mailto:|[a-z0-9]+:\/\/)/i', $url))
		{
			$url = 'http://' . $url;
		}

		$uri = new \Bitrix\Main\UserField\Uri($url);

		return $uri->getUri();
	}

}