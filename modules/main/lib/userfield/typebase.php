<?php

namespace Bitrix\Main\UserField;

/**
 * Class TypeBase
 * @package Bitrix\Main\UserField
 * @deprecated
 */

abstract class TypeBase
{
	static $helper = array();

	const USER_TYPE_ID = '_generic';

	/**
	 * @return TypeHelper
	 */
	public static function getHelper()
	{
		if(!array_key_exists(static::USER_TYPE_ID, static::$helper))
		{
			static::setHelper(new TypeHelper(static::USER_TYPE_ID));
		}

		return static::$helper[static::USER_TYPE_ID];
	}

	/**
	 * @param TypeHelper $helper
	 */
	public static function setHelper(TypeHelper $helper)
	{
		static::$helper[static::USER_TYPE_ID] = $helper;
	}

	protected static function initDisplay(array $additional = array())
	{
		\CJSCore::init(array_merge(array('uf'), $additional));
	}

	protected static function buildTagAttributes(array $attributes)
	{
		$s = '';
		foreach($attributes as $attribute => $value)
		{
			$s .= htmlspecialcharsbx($attribute) . '="' . htmlspecialcharsbx($value) . '" ';
		}

		return $s;
	}

	protected static function getFieldName($arUserField, $arAdditionalParameters = array())
	{
		$fieldName = $arUserField["FIELD_NAME"];
		if($arUserField["MULTIPLE"] == "Y")
		{
			$fieldName .= "[]";
		}

		return $fieldName;
	}

	protected static function normalizeFieldValue($value)
	{
		if(!is_array($value))
		{
			$value = array($value);
		}
		if(empty($value))
		{
			$value = array(null);
		}

		return $value;
	}

	protected static function getFieldValue($arUserField, $arAdditionalParameters = array())
	{
		if(!$arAdditionalParameters["bVarsFromForm"])
		{
			if($arUserField["ENTITY_VALUE_ID"] <= 0)
			{
				switch($arUserField['USER_TYPE_ID'])
				{
					case \CUserTypeDate::USER_TYPE_ID:
					case \CUserTypeDateTime::USER_TYPE_ID:

						$full = $arUserField['USER_TYPE_ID'] === \CUserTypeDateTime::USER_TYPE_ID;
						if($arUserField["SETTINGS"]["DEFAULT_VALUE"]["TYPE"] == "NOW")
						{
							$value = $full
								? \ConvertTimeStamp(time() + \CTimeZone::getOffset(), "FULL")
								: \ConvertTimeStamp(time(), 'SHORT');
						}
						else
						{
							$value = $full
								? str_replace(" 00:00:00", "", \CDatabase::formatDate($arUserField["SETTINGS"]["DEFAULT_VALUE"]["VALUE"], "YYYY-MM-DD HH:MI:SS", \CLang::getDateFormat("FULL")))
								: \CDatabase::formatDate($arUserField["SETTINGS"]["DEFAULT_VALUE"]["VALUE"], "YYYY-MM-DD", \CLang::getDateFormat('SHORT'));
						}

						break;
					case \CUserTypeEnum::USER_TYPE_ID:

						$value = $arUserField['MULTIPLE'] === 'Y' ? array() : null;
						foreach($arUserField['ENUM'] as $enum)
						{
							if($enum['DEF'] === 'Y')
							{
								if($arUserField['MULTIPLE'] === 'Y')
								{
									$value[] = $enum['ID'];
								}
								else
								{
									$value = $enum['ID'];
									break;
								}
							}
						}

						break;
					default:
						$value = $arUserField["SETTINGS"]["DEFAULT_VALUE"] ?? null;

						break;
				}
			}
			else
			{
				$value = $arUserField['VALUE'] ?? null;
			}
		}
		else
		{
			$value = $_REQUEST[$arUserField["FIELD_NAME"]] ?? null;
		}

		return static::normalizeFieldValue($value);
	}

	public static function getPublicText($userField)
	{
		$value = static::normalizeFieldValue($userField['VALUE']);

		return join(', ', array_map(function($v)
		{
			return is_null($v) || is_scalar($v) ? (string)$v : '';
		}, $value));
	}

}