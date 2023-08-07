<?php

namespace Bitrix\Fileman\UserField;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Fileman\UserField\Types\AddressType;

Loader::includeModule('location');

Loc::loadMessages(__FILE__);

class Address extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = AddressType::USER_TYPE_ID;

	const BITRIX24_RESTRICTION = AddressType::BITRIX24_RESTRICTION;
	const BITRIX24_RESTRICTION_CODE = AddressType::BITRIX24_RESTRICTION_CODE;

	protected static $restrictionCount = null;

	public static function getUserTypeDescription()
	{
		return AddressType::getUserTypeDescription();
	}

	public static function getApiKey()
	{
		return AddressType::getApiKey();
	}

	public static function getApiKeyHint()
	{
		return AddressType::getApiKeyHint();
	}

	public static function getTrialHint()
	{
		return AddressType::getTrialHint();
	}

	public static function canUseMap()
	{
		return AddressType::canUseMap();
	}

	public static function checkRestriction()
	{
		return AddressType::checkRestriction();
	}

	public static function useRestriction()
	{
		return AddressType::useRestriction();
	}

	function prepareSettings($userField)
	{
		return AddressType::prepareSettings($userField);
	}

	public static function getDbColumnType($userField)
	{
		return AddressType::getDbColumnType();
	}

	function checkFields($userField, $value)
	{
		return AddressType::checkFields($userField, $value);
	}

	function onBeforeSave($userField, $value)
	{
		return AddressType::onBeforeSave($userField, $value);
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return AddressType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return AddressType::renderEditForm($userField, $additionalParameters);
	}

	function getEditFormHtmlMulty($userField, $additionalParameters)
	{
		return AddressType::renderEditForm($userField, $additionalParameters);
	}

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		return AddressType::RenderEdit($userField, $additionalParameters);
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return AddressType::renderView($userField, $additionalParameters);
	}

	public static function getPublicText($userField)
	{
		return AddressType::renderText($userField);
	}

	public static function getAdminListViewHtml($userField, $additionalParameters){
		return AddressType::renderAdminListView($userField, $additionalParameters);
	}

	public static function getAdminListEditHtml($userField, $additionalParameters){
		return AddressType::renderAdminListEdit($userField, $additionalParameters);
	}
}
