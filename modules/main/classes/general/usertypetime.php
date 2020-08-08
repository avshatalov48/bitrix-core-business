<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserField\TypeBase;
use Bitrix\Main\UserField\Types\DateTimeType;

/**
 * Class CUserTypeDateTime
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeDateTime extends TypeBase
{
	const USER_TYPE_ID = DateTimeType::USER_TYPE_ID;

	function getUserTypeDescription()
	{
		return DateTimeType::getUserTypeDescription();
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return DateTimeType::renderView($userField, $additionalParameters);
	}

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		return DateTimeType::renderEdit($userField, $additionalParameters);
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return DateTimeType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return DateTimeType::renderEditForm($userField, $additionalParameters);
	}

	function getAdminListViewHtml($userField, $additionalParameters)
	{
		return DateTimeType::renderAdminListView($userField, $additionalParameters);
	}

	function getAdminListEditHtml($userField, $additionalParameters)
	{
		return DateTimeType::renderAdminListEdit($userField, $additionalParameters);
	}

	function getFilterHtml($userField, $additionalParameters)
	{
		return DateTimeType::renderFilter($userField, $additionalParameters);
	}

	function getDBColumnType($userField)
	{
		return DateTimeType::getDbColumnType();
	}

	function getFilterData($userField, $additionalParameters)
	{
		return DateTimeType::getFilterData($userField, $additionalParameters);
	}

	function prepareSettings($userField)
	{
		return DateTimeType::prepareSettings($userField);
	}

	function CheckFields($userField, $value)
	{
		return DateTimeType::checkFields($userField, $value);
	}

	public function onAfterFetch($userfield, $fetched)
	{
		return DateTimeType::onAfterFetch($userfield, $fetched);
	}

	public function onBeforeSave($userfield, $value)
	{
		return DateTimeType::onBeforeSave($userfield, $value);
	}

	protected static function getFormat($value, $userField)
	{
		return DateTimeType::getFormat($value, $userField);
	}

	public static function FormatField(array $userField, $fieldName)
	{
		return DateTimeType::formatField($userField, $fieldName);
	}
}