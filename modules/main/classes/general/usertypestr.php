<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserField\TypeBase;
use Bitrix\Main\UserField\Types\StringType;

/**
 * Class CUserTypeString
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeString extends TypeBase
{
	const USER_TYPE_ID = StringType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return StringType::getUserTypeDescription();
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return StringType::renderView($userField, $additionalParameters);
	}

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		return StringType::renderEdit($userField, $additionalParameters);
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return StringType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return StringType::renderEditForm($userField, $additionalParameters);
	}

	function getAdminListViewHtml($userField, $additionalParameters)
	{
		return StringType::renderAdminListView($userField, $additionalParameters);
	}

	function getAdminListEditHtml($userField, $additionalParameters)
	{
		return StringType::renderAdminListEdit($userField, $additionalParameters);
	}

	function getFilterHtml($userField, $additionalParameters)
	{
		return StringType::renderFilter($userField, $additionalParameters);
	}

	public static function getDbColumnType()
	{
		return StringType::getDbColumnType();
	}

	function getFilterData($userField, $additionalParameters)
	{
		return StringType::getFilterData($userField, $additionalParameters);
	}

	function prepareSettings($userField)
	{
		return StringType::prepareSettings($userField);
	}

	function checkFields($userField, $value)
	{
		return StringType::checkFields($userField, $value);
	}

	function onSearchIndex($userField)
	{
		return StringType::onSearchIndex($userField);
	}
}