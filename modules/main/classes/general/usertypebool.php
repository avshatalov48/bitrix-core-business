<?php

use Bitrix\Main\UserField\Types\BooleanType;
use Bitrix\Main\UserField\TypeBase;

/**
 * Class CUserTypeBoolean
 * @deprecated deprecated since main 20.0.700
 */
class CUserTypeBoolean extends TypeBase
{
	const USER_TYPE_ID = BooleanType::USER_TYPE_ID;

	const DISPLAY_DROPDOWN = BooleanType::DISPLAY_DROPDOWN;
	const DISPLAY_RADIO = BooleanType::DISPLAY_RADIO;
	const DISPLAY_CHECKBOX = BooleanType::DISPLAY_CHECKBOX;

	public static function GetUserTypeDescription()
	{
		return BooleanType::getUserTypeDescription();
	}

	function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
	{
		return BooleanType::renderSettings($arUserField, $arHtmlControl, $bVarsFromForm);
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		return BooleanType::renderEditForm($arUserField, $arHtmlControl);
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		return BooleanType::renderFilter($arUserField, $arHtmlControl);
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		return BooleanType::renderAdminListView($arUserField, $arHtmlControl);
	}

	function getAdminListViewHtmlMulty($userField, $additionalParameters)
	{
		return BooleanType::renderAdminListView($userField, $additionalParameters);
	}

	// @todo must not supporting the ability to add values in multiple fields
	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		return BooleanType::renderAdminListEdit($arUserField, $arHtmlControl);
	}

	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		return BooleanType::renderView($arUserField, $arAdditionalParameters);
	}

	public static function getPublicText($userField)
	{
		return BooleanType::renderText($userField);
	}

	public function getPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		return BooleanType::renderEdit($arUserField, $arAdditionalParameters);
	}

	public static function getLabels($userField)
	{
		return BooleanType::getLabels($userField);
	}

	public static function getDbColumnType($userField)
	{
		return BooleanType::getDbColumnType();
	}

	function prepareSettings($userField)
	{
		return BooleanType::prepareSettings($userField);
	}

	function onBeforeSave($userField, $value)
	{
		return BooleanType::onBeforeSave($userField, $value);
	}

	function getFilterData($userField, $additionalParameters)
	{
		return BooleanType::getFilterData($userField, $additionalParameters);
	}
}