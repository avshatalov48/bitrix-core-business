<?
$arUserProps = array(
	"ID" => GetMessage("SONET_C241_ID"),
	"LOGIN" => GetMessage("SONET_C241_LOGIN"),
	"NAME" => GetMessage("SONET_C241_NAME"),
	"SECOND_NAME" => GetMessage("SONET_C241_SECOND_NAME"),
	"LAST_NAME" => GetMessage("SONET_C241_LAST_NAME"),
	"EMAIL" => GetMessage("SONET_C241_EMAIL"),
	"LAST_LOGIN" => GetMessage("SONET_C241_LAST_LOGIN"),
	"DATE_REGISTER" => GetMessage("SONET_C241_DATE_REGISTER"),
	"LID" => GetMessage("SONET_C241_LID"),

	"PERSONAL_BIRTHDAY" => GetMessage("SONET_C241_PERSONAL_BIRTHDAY"),
	"PERSONAL_BIRTHDAY_YEAR" => GetMessage("SONET_C241_PERSONAL_BIRTHDAY_YEAR"),
	"PERSONAL_BIRTHDAY_DAY" => GetMessage("SONET_C241_PERSONAL_BIRTHDAY_DAY"),

	"PERSONAL_PROFESSION" => GetMessage("SONET_C241_PERSONAL_PROFESSION"),
	"PERSONAL_WWW" => GetMessage("SONET_C241_PERSONAL_WWW"),
	"PERSONAL_ICQ" => GetMessage("SONET_C241_PERSONAL_ICQ"),
	"PERSONAL_GENDER" => GetMessage("SONET_C241_PERSONAL_GENDER"),
	"PERSONAL_PHOTO" => GetMessage("SONET_C241_PERSONAL_PHOTO"),
	"PERSONAL_NOTES" => GetMessage("SONET_C241_PERSONAL_NOTES"),

	"PERSONAL_PHONE" => GetMessage("SONET_C241_PERSONAL_PHONE"),
	"PERSONAL_FAX" => GetMessage("SONET_C241_PERSONAL_FAX"),
	"PERSONAL_MOBILE" => GetMessage("SONET_C241_PERSONAL_MOBILE"),
	"PERSONAL_PAGER" => GetMessage("SONET_C241_PERSONAL_PAGER"),

	"PERSONAL_COUNTRY" => GetMessage("SONET_C241_PERSONAL_COUNTRY"),
	"PERSONAL_STATE" => GetMessage("SONET_C241_PERSONAL_STATE"),
	"PERSONAL_CITY" => GetMessage("SONET_C241_PERSONAL_CITY"),
	"PERSONAL_ZIP" => GetMessage("SONET_C241_PERSONAL_ZIP"),
	"PERSONAL_STREET" => GetMessage("SONET_C241_PERSONAL_STREET"),
	"PERSONAL_MAILBOX" => GetMessage("SONET_C241_PERSONAL_MAILBOX"),

	"WORK_COMPANY" => GetMessage("SONET_C241_WORK_COMPANY"),
	"WORK_DEPARTMENT" => GetMessage("SONET_C241_WORK_DEPARTMENT"),
	"WORK_POSITION" => GetMessage("SONET_C241_WORK_POSITION"),
	"WORK_WWW" => GetMessage("SONET_C241_WORK_WWW"),
	"WORK_PROFILE" => GetMessage("SONET_C241_WORK_PROFILE"),
	"WORK_LOGO" => GetMessage("SONET_C241_WORK_LOGO"),
	"WORK_NOTES" => GetMessage("SONET_C241_WORK_NOTES"),

	"WORK_PHONE" => GetMessage("SONET_C241_WORK_PHONE"),
	"WORK_FAX" => GetMessage("SONET_C241_WORK_FAX"),
	"WORK_PAGER" => GetMessage("SONET_C241_WORK_PAGER"),

	"WORK_COUNTRY" => GetMessage("SONET_C241_WORK_COUNTRY"),
	"WORK_STATE" => GetMessage("SONET_C241_WORK_STATE"),
	"WORK_CITY" => GetMessage("SONET_C241_WORK_CITY"),
	"WORK_ZIP" => GetMessage("SONET_C241_WORK_ZIP"),
	"WORK_STREET" => GetMessage("SONET_C241_WORK_STREET"),
	"WORK_MAILBOX" => GetMessage("SONET_C241_WORK_MAILBOX"),
);

$arResult["UserFieldsSearchAdv"] = array();
if (count($arParams["USER_FIELDS_SEARCH_ADV"]) > 0)
{
	foreach ($arUserProps as $userFieldName => $userFieldTitle)
	{
		if (in_array($userFieldName, $arParams["USER_FIELDS_SEARCHABLE"])
			&& in_array($userFieldName, $arParams["USER_FIELDS_SEARCH_ADV"]))
		{
			$requestName = mb_strtolower("FLTX_".$userFieldName);
			$arVal = array(
				"VALUE" => htmlspecialcharsex(array_key_exists($requestName, $_REQUEST) ? $_REQUEST[$requestName] : ""),
				"NAME" => $requestName,
				"TITLE" => $userFieldTitle,
			);

			switch ($userFieldName)
			{
				case 'LAST_LOGIN':
				case 'DATE_REGISTER':
				case 'PERSONAL_BIRTHDAY':
					$arVal["TYPE"] = "calendar";
					break;

				case 'PERSONAL_GENDER':
					$arVal["TYPE"] = "select";
					$arVal["VALUES"] = array("M" => GetMessage("SONET_C241_MALE"), "F" => GetMessage("SONET_C241_FEMALE"));
					break;

				case 'PERSONAL_COUNTRY':
				case 'WORK_COUNTRY':
					$arVal["TYPE"] = "select";
					$arVal["VALUES"] = array();
					$arCountriesTmp = GetCountryArray(LANGUAGE_ID);
					for ($i = 0; $i < count($arCountriesTmp["reference_id"]); $i++)
						$arVal["VALUES"][$arCountriesTmp["reference_id"][$i]] = $arCountriesTmp["reference"][$i];
					break;

				default:
					$arVal["TYPE"] = "string";
					break;
			}

			if (in_array($userFieldName, $arParams["USER_FIELDS_SEARCH_ADV"]))
				$arResult["UserFieldsSearchAdv"][$userFieldName] = $arVal;
		}
	}
}

$arResult["UserPropertiesSearchAdv"] = array();
if (count($arParams["USER_PROPERTIES_SEARCH_ADV"]) > 0)
{

	$arResTmp = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
	$arUserCustomProps = array();
	if (!empty($arResTmp))
	{
		foreach ($arResTmp as $key => $value)
		{
			if (in_array($value["FIELD_NAME"], $arParams["USER_PROPERTY_SEARCHABLE"]))
				$arUserCustomProps[mb_strtoupper($value["FIELD_NAME"])] = $value;
		}
	}

	foreach ($arUserCustomProps as $fieldName => $arUserField)
	{
		if (in_array($fieldName, $arParams["USER_PROPERTY_SEARCHABLE"]))
		{
			$arUserField["EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"] <> '' ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
			$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
			$arUserField["FIELD_NAME"] = mb_strtolower("FLTX_".$fieldName);
			$arUserField["~FIELD_NAME"] = mb_strtolower("FLTX_".$fieldName);
			$arUserField["VALUE"] = array($_REQUEST[$arUserField["FIELD_NAME"]]);			
			if (in_array($fieldName, $arParams["USER_PROPERTIES_SEARCH_ADV"]))
				$arResult["UserPropertiesSearchAdv"][$fieldName] = $arUserField;
		}
	}
}

?>