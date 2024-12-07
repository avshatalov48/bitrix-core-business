<?php

use Bitrix\Main\UserField\TypeBase;
use Bitrix\Main\UserField\Types\DateType;

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 * @deprecated deprecated since main 20.0.700
 */

class CUserTypeDate extends TypeBase
{
	const USER_TYPE_ID = DateType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return DateType::getUserTypeDescription();
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return DateType::renderView($userField, $additionalParameters);
	}

	public static function getPublicEdit($userField, $additionalParameters = array())
	{
		return DateType::renderEdit($userField, $additionalParameters);
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return DateType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return DateType::renderEditForm($userField, $additionalParameters);
	}

	function getAdminListViewHtml($userField, $additionalParameters)
	{
		return DateType::renderAdminListView($userField, $additionalParameters);
	}

	function getAdminListEditHtml($userField, $additionalParameters)
	{
		return DateType::renderAdminListEdit($userField, $additionalParameters);
	}

	function getFilterHtml($userField, $additionalParameters)
	{
		return DateType::renderFilter($userField, $additionalParameters);
	}

	public static function getDbColumnType()
	{
		return DateType::getDbColumnType();
	}

	function getFilterData($userField, $additionalParameters)
	{
		return DateType::getFilterData($userField, $additionalParameters);
	}

	function prepareSettings($userField)
	{
		return DateType::prepareSettings($userField);
	}

	public function onAfterFetch($userfield, $fetched)
	{
		return DateType::onAfterFetch($userfield, $fetched);
	}

	public function onBeforeSave($userfield, $value)
	{
		return DateType::onBeforeSave($userfield, $value);
	}

	public static function formatField(array $userField, $fieldName)
	{
		return DateType::formatField($userField, $fieldName);
	}
}
