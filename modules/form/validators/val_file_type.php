<?php

IncludeModuleLangFile(__FILE__);

class CFormValidatorFileType
{
	public static function GetDescription()
	{
		return array(
			"NAME" => "file_type", // unique validator string ID
			"DESCRIPTION" => GetMessage('FORM_VALIDATOR_FILE_TYPE_DESCRIPTION'), // validator description
			"TYPES" => array("file"), //  list of types validator can be applied.
			"SETTINGS" => array("CFormValidatorFileType", "GetSettings"), // method returning array of validator settings, optional
			"CONVERT_TO_DB" => array("CFormValidatorFileType", "ToDB"), // method, processing validator settings to string to put to db, optional
			"CONVERT_FROM_DB" => array("CFormValidatorFileType", "FromDB"), // method, processing validator settings from string from db, optional
			"HANDLER" => array("CFormValidatorFileType", "DoValidate") // main validation method
		);
	}

	public static function GetSettings()
	{
		return array(
			"EXT" => array(
				"TITLE" => GetMessage("FORM_VALIDATOR_FILE_TYPE_SETTINGS_EXT"),
				"TYPE" => "DROPDOWN",
				"VALUES" => array(
					GetMessage("FORM_VALIDATOR_FILE_TYPE_SETTINGS_TYPE_EXT_NONE"),
					"doc,rtf,pdf,txt" => GetMessage("FORM_VALIDATOR_FILE_TYPE_SETTINGS_EXT_DOCS"),
					"rar,tar,gz,zip,7z,ace,kgb,arj" => GetMessage("FORM_VALIDATOR_FILE_TYPE_SETTINGS_EXT_ARCH"),
					"jpg,jpeg,bmp,gif,png" => GetMessage("FORM_VALIDATOR_FILE_TYPE_SETTINGS_EXT_IMG"),
				),
				"DEFAULT" => "",
			),

			"EXT_CUSTOM" => array(
				"TITLE" => GetMessage("FORM_VALIDATOR_FILE_TYPE_SETTINGS_EXT_CUSTOM"),
				"TYPE" => "TEXT",
				"DEFAULT" => "",
			),
		);
	}

	public static function ToDB($arParams)
	{
		return serialize($arParams);
	}

	public static function FromDB($strParams)
	{
		return unserialize($strParams, ['allowed_classes' => false]);
	}

	public static function DoValidate($arParams, $arQuestion, $arAnswers, $arValues)
	{
		global $APPLICATION;

		if (!empty($arValues))
		{
			$arExt = array();
			if ($arParams["EXT"] <> '')
				$arExt = array_merge($arExt, explode(",", mb_strtolower($arParams["EXT"])));

			if ($arParams["EXT_CUSTOM"] <> '')
				$arExt = array_merge($arExt, explode(",", mb_strtolower($arParams["EXT_CUSTOM"])));

			if (!empty($arExt))
			{
				foreach ($arExt as $key => $value) $arExt[$key] = trim($value);
				$arExt = array_unique($arExt);
				$arExtKeys = array_fill_keys($arExt, true);
				$res = true;

				foreach ($arValues as $arFile)
				{
					if (empty($arFile) || !is_array($arFile))
					{
						continue;
					}
					if ($arFile["tmp_name"] <> '' && $arFile["error"] == "0")
					{
						$point_pos = mb_strrpos($arFile["name"], ".");
						if ($point_pos === false)
						{
							$res = false;
							break;
						}

						$ext = mb_strtolower(mb_substr($arFile["name"], $point_pos + 1));
						if (!isset($arExtKeys[$ext]))
						{
							$res = false;
							break;
						}
					}
				}

				if (!$res)
				{
					$APPLICATION->ThrowException(GetMessage("FORM_VALIDATOR_FILE_TYPE_ERROR"));
					return false;
				}
			}
		}

		return true;

	}
}

AddEventHandler("form", "onFormValidatorBuildList", array("CFormValidatorFileType", "GetDescription"));
