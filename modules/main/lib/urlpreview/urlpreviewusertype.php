<?php

namespace Bitrix\Main\UrlPreview;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class UrlPreviewUserType
{
	/**
	 * Returns url_preview user type description
	 *
	 * @return array
	 */
	public static function getUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => "url_preview",
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => Loc::getMessage('MAIN_URL_PREVIEW_USER_TYPE_NAME'),
			"BASE_TYPE" => "int"
		);
	}

	/**
	 * Return internal type for storing url_preview user type values
	 *
	 * @param array $userField Array containing parameters of the user field.
	 * @return string
	 */
	public static function getDBColumnType($userField)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\IntegerField('x'));
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings($userField)
	{
		//this usertype does not support setting yet
		return array();
	}

	/**
	 * @param array $userField Array containing parameters of the user field.
	 * @param $htmlControl
	 * @param $varsFromForm
	 * @return string
	 */
	public static function getSettingsHTML($userField, $htmlControl, $varsFromForm)
	{
		return "&nbsp;";
	}

	/**
	 * @param array $userField Array containing parameters of the user field.
	 * @param array $htmlControl
	 * @return string
	 */
	public static function getEditFormHTML($userField, $htmlControl)
	{
		return UrlPreview::showEdit($userField, array());
	}

	/**
	 * @param array $userField Array containing parameters of the user field.
	 * @param array $htmlControl
	 * @return string
	 */
	public static function getFilterHTML($userField, $htmlControl)
	{
		return '&nbsp;';
	}

	/**
	 * @param array $userField Array containing parameters of the user field.
	 * @param array $htmlControl
	 * @return string
	 */
	public static function getAdminListViewHTML($userField, $htmlControl)
	{
		return "&nbsp;";
	}

	/**
	 * @param array $userField Array containing parameters of the user field.
	 * @param array $htmlControl
	 * @return string
	 */
	public static function getAdminListEditHTML($userField, $htmlControl)
	{
		return "&nbsp;";
	}

	/**
	 * @param array $userField Array containing parameters of the user field.
	 * @param array $htmlControl
	 * @return string
	 */
	public static function getAdminListEditHTMLMulty($userField, $htmlControl)
	{
		return "&nbsp;";
	}

	/**
	 * @param array $userField Array containing parameters of the user field.
	 * @param array $params
	 * @param array $setting
	 * @return string
	 */
	public static function getPublicViewHTML($userField, $id, $params = "", $settings = array())
	{
		return UrlPreview::showView($userField, $params, $cacheTag);
	}

	/**
	 * Checks for current user's access to $value.
	 *
	 * @param array $userField Array containing parameters of the user field.
	 * @param int $value
	 * @return array
	 */
	public static function checkfields($userField, $value)
	{
		$result = array();

		$signer = new Signer();
		try
		{
			$value = $signer->unsign($value, UrlPreview::SIGN_SALT);
		}
		catch (SystemException $e)
		{
			return $result;
		}

		$value = (int)$value;

		if($value === 0)
			return $result;

		$metadata = UrlMetadataTable::getById($value)->fetch();
		if(!is_array($metadata))
		{
			$result[] = array(
					"id" => $userField["FIELD_NAME"],
					"text" => GetMessage("MAIN_URL_PREVIEW_VALUE_NOT_FOUND")
			);
		}
		else if($metadata['TYPE'] === UrlMetadataTable::TYPE_DYNAMIC
				&& !UrlPreview::checkDynamicPreviewAccess($metadata['URL']))
		{
			$result[] = array(
					"id" => $userField["FIELD_NAME"],
					"text" => GetMessage("MAIN_URL_PREVIEW_VALUE_NO_ACCESS",
							array('#URL#' => $metadata['URL'])
					)
			);
		}

		return $result;
	}

	/**
	 * Hook executed before saving url_preview user type value. Checks and removes signature of the $value.
	 * If signature is correct, checks current user's access to $value.
	 *
	 * @param array $userField Array containing parameters of the user field.
	 * @param string $value Signed value of the user field.
	 * @return int Unsigned value of the user field, or null in case of errors.
	 */
	public static function onBeforeSave($userField, $value)
	{
		$imageUrl = null;
		if(str_contains($value, ';'))
		{
			[$value, $imageUrl] = explode(';', $value);
		}

		$signer = new Signer();
		try
		{
			$value = $signer->unsign($value, UrlPreview::SIGN_SALT);
		}
		catch (SystemException $e)
		{
			return null;
		}
		$metadata = UrlMetadataTable::getById($value)->fetch();
		if(!is_array($metadata))
			return null;

		if($metadata['TYPE'] === UrlMetadataTable::TYPE_STATIC)
		{
			if($imageUrl && is_array($metadata['EXTRA']['IMAGES']) && in_array($imageUrl, $metadata['EXTRA']['IMAGES']))
			{
				UrlPreview::setMetadataImage((int)$value, $imageUrl);
			}
			return $value;
		}
		else if($metadata['TYPE'] === UrlMetadataTable::TYPE_DYNAMIC
				&& UrlPreview::checkDynamicPreviewAccess($metadata['URL']))
		{
			return $value;
		}

		return null;
	}
}