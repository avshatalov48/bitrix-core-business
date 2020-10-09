<?
IncludeModuleLangFile(__FILE__);

class CFormValidatorNumber
{
	function GetDescription()
	{
		return array(
			"NAME" => "number", // validator string ID
			"DESCRIPTION" => GetMessage("FORM_VALIDATOR_VAL_NUM_DESCRIPTION"), // validator description
			"TYPES" => array("text", "textarea"), //  list of types validator can be applied.
			"HANDLER" => array("CFormValidatorNumber", "DoValidate") // main validation method
		);
	}

	function DoValidate($arParams, $arQuestion, $arAnswers, $arValues)
	{
		global $APPLICATION;

		$prepared = [];

		foreach ($arValues as $value)
		{
			if (is_int($value))
			{
				continue;
			}
			elseif (is_string($value))
			{
				// empty string is not a number but we won't return error - crossing with "required" mark
				if ($value != "")
				{
					if (!preg_match('/^(-)?[0-9]+$/', $value, $prepared))
					{
						$APPLICATION->ThrowException(GetMessage("FORM_VALIDATOR_VAL_NUM_ERROR"));
						return false;
					}
				}
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage("FORM_VALIDATOR_VAL_NUM_ERROR"));
				return false;
			}
		}

		return true;
	}
}

AddEventHandler("form", "onFormValidatorBuildList", array("CFormValidatorNumber", "GetDescription"));