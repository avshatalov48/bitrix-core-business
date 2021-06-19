<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 * @deprecated
 */

use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Main\UserField\TypeBase;

/**
 * Class CUserTypeEnum
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeEnum extends TypeBase
{
	public const
		USER_TYPE_ID = EnumType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return EnumType::getUserTypeDescription();
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return EnumType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getFilterHtml($userField, $additionalParameters)
	{
		return EnumType::renderFilter($userField, $additionalParameters);
	}

	function getAdminListViewHtml($userField, $additionalParameters)
	{
		return EnumType::renderAdminListView($userField, $additionalParameters);
	}

	function getAdminListEditHtml($userField, $additionalParameters)
	{
		return EnumType::renderAdminListEdit($userField, $additionalParameters);
	}

	function getAdminListEditHtmlMulty($userField, $additionalParameters)
	{
		return EnumType::renderAdminListEdit($userField, $additionalParameters);
	}

	function getEditFormHtml($userField, $additionalParameters)
	{
		return EnumType::renderEditForm($userField, $additionalParameters);
	}

	function getEditFormHtmlMulty($userField, $additionalParameters)
	{
		return EnumType::renderEditForm($userField, $additionalParameters);
	}

	public static function getPublicView($userField, $additionalParameters = [])
	{
		return EnumType::renderView($userField, $additionalParameters);
	}

	public function getPublicEdit($userField, $additionalParameters = [])
	{
		return EnumType::renderEdit($userField, $additionalParameters);
	}

	public static function getPublicText($userField)
	{
		return EnumType::getPublicText($userField);
	}

	public static function getDbColumnType($userField)
	{
		return EnumType::getDbColumnType();
	}

	function prepareSettings($userField)
	{
		return EnumType::prepareSettings($userField);
	}

	function getGroupActionData($userField, $additionalParameters)
	{
		return EnumType::getGroupActionData($userField, $additionalParameters);
	}

	function getFilterData($userField, $additionalParameters)
	{
		return EnumType::getFilterData($userField, $additionalParameters);
	}

	function checkFields($userField, $value)
	{
		return EnumType::checkFields($userField, $value);
	}

	public static function getList($userField)
	{
		return EnumType::getList($userField);
	}

	public static function getListMultiple(array $userFields)
	{
		return EnumType::getListMultiple($userFields);
	}

	function onSearchIndex($userField)
	{
		return EnumType::onSearchIndex($userField);
	}

	protected static function getEnumList(&$userField, $arParams = array())
	{
		EnumType::getEnumList($userField, $arParams);
	}

	protected static function getEmptyCaption($userField)
	{
		return EnumType::getEmptyCaption($userField);
	}

}
