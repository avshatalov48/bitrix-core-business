<?php

use Bitrix\Main\UserField\Types\StringFormattedType;

IncludeModuleLangFile(__FILE__);

class CUserTypeStringFormatted extends \CUserTypeString
{
	const USER_TYPE_ID = StringFormattedType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return StringFormattedType::getUserTypeDescription();
	}

	function prepareSettings($userField)
	{
		return StringFormattedType::prepareSettings($userField);
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return StringFormattedType::renderSettings($userField, $additionalParameters, $varsFromForm);
	}

	public static function getPublicViewHtml($userField, $additionalParameters)
	{
		return StringFormattedType::getPublicViewHtml($userField, $additionalParameters);
	}

	public static function getPublicView($userField, $additionalParameters = array())
	{
		return StringFormattedType::renderView($userField, $additionalParameters);
	}
}
