<?php

use Bitrix\Main;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\FileType;

Loc::loadMessages(__FILE__);

/**
 * Class CUserTypeFile
 * @deprecated
 */
class CUserTypeFile extends Main\UserField\TypeBase
{
	const USER_TYPE_ID = FileType::USER_TYPE_ID;

	public static function getUserTypeDescription()
	{
		return FileType::getUserTypeDescription();
	}

	public static function getDbColumnType($arUserField)
	{
		return FileType::getDbColumnType();
	}

	function prepareSettings($userField)
	{
		return FileType::prepareSettings($userField);
	}

	function getSettingsHtml($userField, $additionalParameters, $varsFromForm)
	{
		return FileType::prepareSettings($userField, $additionalParameters, $varsFromForm);
	}

	function getEditFormHtml($arUserField, $arHtmlControl)
	{
		return FileType::getEditFormHtml($arUserField, $arHtmlControl);
	}

	function getEditFormHtmlMulty($userField, $additionalParameters)
	{
		return FileType::getEditFormHtml($userField, $additionalParameters);
	}

	function getFilterHtml($userField, $additionalParameters)
	{
		return FileType::getFilterHtml($userField, $additionalParameters);
	}

	function getAdminListViewHtml($userField, $additionalParameters)
	{
		return FileType::getAdminListViewHtml($userField, $additionalParameters);
	}

	function getAdminListEditHtml($userField, $additionalParameters)
	{
		//TODO edit mode
		return FileType::getAdminListEditHtml($userField, $additionalParameters);
	}

	function getAdminListEditHtmlMulty($userField, $additionalParameters)
	{
		//TODO edit mode
		return FileType::getAdminListEditHTML($userField, $additionalParameters);
	}

	function checkFields($userField, $value)
	{
		return FileType::checkFields($userField, $value);
	}

	function onBeforeSave($userField, $value)
	{
		return FileType::onBeforeSave($userField, $value);
	}

	function onSearchIndex($userField)
	{
		return FileType::onSearchIndex($userField);
	}

	function __getFileContent($fileId)
	{
		return FileType::getFileContent($fileId);
	}

	public static function getPublicView($userField, $additionalParameters = [])
	{
		return FileType::getPublicView($userField, $additionalParameters);
	}

	public static function getPublicEdit($userField, $additionalParameters = [])
	{
		return FileType::getPublicEdit($userField, $additionalParameters);
	}

	public static function getPublicEditMultiple($userField, $additionalParameters = [])
	{
		return FileType::getPublicEdit($userField, $additionalParameters);
	}

	public static function getPublicText($userField)
	{
		return FileType::getPublicText($userField);
	}
}

