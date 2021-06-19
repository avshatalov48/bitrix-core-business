<?php
use Bitrix\Main\UserField\Types\DoubleType;
use Bitrix\Main\Loader;
use Bitrix\Main\UserField\TypeBase;

/**
 * Class CUserTypeDouble
 * @deprecated deprecated since main 20.0.700
 */

class CUserTypeDouble extends TypeBase
{
	const USER_TYPE_ID = DoubleType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return DoubleType::getUserTypeDescription();
	}

	function getSettingsHtml($userField, $additionalSettings, $varsFromForm)
	{
		return DoubleType::renderSettings($userField, $additionalSettings, $varsFromForm);
	}

	function getEditFormHtml($userField, $additionalSettings)
	{
		return DoubleType::renderEditForm($userField, $additionalSettings);
	}

	function getFilterHtml($userField, $additionalSettings)
	{
		return DoubleType::renderFilter($userField, $additionalSettings);
	}

	function getAdminListViewHtml($userField, $additionalSettings)
	{
		return DoubleType::renderAdminListView($userField, $additionalSettings);
	}

	function getAdminListEditHtml($userField, $additionalSettings)
	{
		return DoubleType::renderAdminListEdit($userField, $additionalSettings);
	}

	public static function getPublicView($userField, $arAdditionalParameters = array())
	{
		return DoubleType::renderView($userField, $arAdditionalParameters);
	}

	public function getPublicEdit($userField, $arAdditionalParameters = array())
	{
		return DoubleType::renderEdit($userField, $arAdditionalParameters);
	}

	public static function getDbColumnType($userField)
	{
		return DoubleType::getDbColumnType();
	}

	function getFilterData($userField, $additionalSettings)
	{
		return DoubleType::getFilterData($userField, $additionalSettings);
	}

	function prepareSettings($userField)
	{
		return DoubleType::prepareSettings($userField);
	}

	function checkFields($userField, $value)
	{
		return DoubleType::checkFields($userField, $value);
	}

	function onBeforeSave($userField, $value)
	{
		return DoubleType::onBeforeSave($userField, $value);
	}

	function onSearchIndex($userField)
	{
		return DoubleType::onSearchIndex($userField);
	}

}
