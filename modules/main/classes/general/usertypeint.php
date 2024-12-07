<?php

use Bitrix\Main\UserField\Types\IntegerType;
use Bitrix\Main\UserField\TypeBase;

/**
 * Class CUserTypeInteger
 * Type for custom properties - STRING
 *
 * @package usertype
 * @subpackage classes
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeInteger extends TypeBase
{
	const USER_TYPE_ID = IntegerType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return IntegerType::getUserTypeDescription();
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return IntegerType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return IntegerType::renderEditForm($userField, $additionalParameters);
	}

	function getFilterHtml($userField, $additionalParameters)
	{
		return IntegerType::renderFilter($userField, $additionalParameters);
	}

	function getAdminListViewHtml($userField, $additionalParameters)
	{
		return IntegerType::renderAdminListView($userField, $additionalParameters);
	}

	function getAdminListEditHtml($userField, $additionalParameters)
	{
		return IntegerType::renderAdminListEdit($userField, $additionalParameters);
	}

	public function getPublicView($userField, $additionalParameters = array())
	{
		return IntegerType::renderView($userField, $additionalParameters);
	}

	public function getPublicEdit($userField, $additionalParameters = array())
	{
		return IntegerType::renderEdit($userField, $additionalParameters);
	}

	public static function getDbColumnType($userField)
	{
		return IntegerType::getDbColumnType();
	}

	function getFilterData($userField, $additionalParameters)
	{
		return IntegerType::getFilterData($userField, $additionalParameters);
	}

	function prepareSettings($userField)
	{
		return IntegerType::prepareSettings($userField);
	}

	function checkFields($userField, $value)
	{
		return IntegerType::checkFields($userField, $value);
	}

}
