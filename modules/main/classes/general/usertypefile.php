<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Main;
use Bitrix\Main\UI\FileInputUtility;

class CUserTypeFile extends Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'file';

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_FILE_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_FILE,
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "int(18)";
			case "oracle":
				return "number(18)";
			case "mssql":
				return "int";
		}
	}

	function PrepareSettings($arUserField)
	{
		$size = intval($arUserField["SETTINGS"]["SIZE"]);
		$ar = array();

		if(is_array($arUserField["SETTINGS"]["EXTENSIONS"]))
		{
			$ext = $arUserField["SETTINGS"]["EXTENSIONS"];
		}
		else
		{
			$ext = explode(",", $arUserField["SETTINGS"]["EXTENSIONS"]);
		}

		foreach($ext as $k => $v)
		{
			if($v === true)
			{
				$v = trim($k);
			}
			else
			{
				$v = trim($v);
			}
			if(strlen($v) > 0)
			{
				$ar[$v] = true;
			}
		}

		return array(
			"SIZE" => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			"LIST_WIDTH" => intval($arUserField["SETTINGS"]["LIST_WIDTH"]),
			"LIST_HEIGHT" => intval($arUserField["SETTINGS"]["LIST_HEIGHT"]),
			"MAX_SHOW_SIZE" => intval($arUserField["SETTINGS"]["MAX_SHOW_SIZE"]),
			"MAX_ALLOWED_SIZE" => intval($arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"]),
			"EXTENSIONS" => $ar
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';

		if($bVarsFromForm)
		{
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["SIZE"]);
		}
		elseif(is_array($arUserField))
		{
			$value = intval($arUserField["SETTINGS"]["SIZE"]);
		}
		else
		{
			$value = 20;
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_FILE_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$width = intval($GLOBALS[$arHtmlControl["NAME"]]["LIST_WIDTH"]);
		}
		elseif(is_array($arUserField))
		{
			$width = intval($arUserField["SETTINGS"]["LIST_WIDTH"]);
		}
		else
		{
			$width = 200;
		}
		if($bVarsFromForm)
		{
			$height = intval($GLOBALS[$arHtmlControl["NAME"]]["LIST_HEIGHT"]);
		}
		elseif(is_array($arUserField))
		{
			$height = intval($arUserField["SETTINGS"]["LIST_HEIGHT"]);
		}
		else
		{
			$height = 200;
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_FILE_WIDTH_AND_HEIGHT").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[LIST_WIDTH]" size="7"  maxlength="20" value="'.$width.'">
				&nbsp;x&nbsp;
				<input type="text" name="'.$arHtmlControl["NAME"].'[LIST_HEIGHT]" size="7"  maxlength="20" value="'.$height.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_SHOW_SIZE"]);
		}
		elseif(is_array($arUserField))
		{
			$value = intval($arUserField["SETTINGS"]["MAX_SHOW_SIZE"]);
		}
		else
		{
			$value = 0;
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_FILE_MAX_SHOW_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_SHOW_SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_ALLOWED_SIZE"]);
		}
		elseif(is_array($arUserField))
		{
			$value = intval($arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"]);
		}
		else
		{
			$value = 0;
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_FILE_MAX_ALLOWED_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_ALLOWED_SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
		{
			$value = htmlspecialcharsbx($GLOBALS[$arHtmlControl["NAME"]]["EXTENSIONS"]);
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_FILE_EXTENSIONS").':</td>
				<td>
					<input type="text" size="20" name="'.$arHtmlControl["NAME"].'[EXTENSIONS]" value="'.$value.'">
				</td>
			</tr>
			';
		}
		else
		{
			if(is_array($arUserField))
			{
				$arExt = $arUserField["SETTINGS"]["EXTENSIONS"];
			}
			else
			{
				$arExt = "";
			}
			$value = array();
			if(is_array($arExt))
			{
				foreach($arExt as $ext => $flag)
				{
					$value[] = htmlspecialcharsbx($ext);
				}
			}
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_FILE_EXTENSIONS").':</td>
				<td>
					<input type="text" size="20" name="'.$arHtmlControl["NAME"].'[EXTENSIONS]" value="'.implode(", ", $value).'">
				</td>
			</tr>
			';
		}

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		CModule::IncludeModule("fileman");

		$arHtmlControl["VALIGN"] = "middle";
		$arHtmlControl["ROWCLASS"] = "adm-detail-file-row";

		if(($p = strpos($arHtmlControl["NAME"], "[")) > 0)
		{
			$strOldIdName = substr($arHtmlControl["NAME"], 0, $p)."_old_id".substr($arHtmlControl["NAME"], $p);
		}
		else
		{
			$strOldIdName = $arHtmlControl["NAME"]."_old_id";
		}

		return CFileInput::Show($arHtmlControl["NAME"], $arHtmlControl["VALUE"], array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => array("W" => 200, "H" => 200)
			),
				array(
					'upload' => $arUserField["EDIT_IN_LIST"] == "Y",
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => true,
					'description' => false
				)
			).'<input type="hidden" name="'.$strOldIdName.'" value="'.$arHtmlControl["VALUE"].'">';
	}

	function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		$arHtmlControl["ROWCLASS"] = "adm-detail-file-row";

		CModule::IncludeModule("fileman");

		$values = array();
		$fieldName = substr($arHtmlControl["NAME"], 0, -2);
		$result = "";
		foreach($arHtmlControl["VALUE"] as $key => $fileId)
		{
			$result .= '<input type="hidden" name="'.$fieldName.'_old_id['.$key.']" value="'.$fileId.'">';
			$values[$fieldName."[".$key."]"] = $fileId;
		}

		return CFileInput::ShowMultiple($values, $fieldName."[n#IND#]", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => array("W" => 200, "H" => 200)
			),
				false,
				array(
					'upload' => $arUserField["EDIT_IN_LIST"] == "Y",
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => true,
					'description' => false
				)
			).$result;
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		return '&nbsp;';
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		return CFile::ShowFile($arHtmlControl["VALUE"], $arUserField["SETTINGS"]["MAX_SHOW_SIZE"], $arUserField["SETTINGS"]["LIST_WIDTH"], $arUserField["SETTINGS"]["LIST_HEIGHT"], true);
	}

	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		//TODO edit mode
		return CFile::ShowFile($arHtmlControl["VALUE"], $arUserField["SETTINGS"]["MAX_SHOW_SIZE"], $arUserField["SETTINGS"]["LIST_WIDTH"], $arUserField["SETTINGS"]["LIST_HEIGHT"], true);
	}

	function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
	{
		//TODO edit mode
		$result = "&nbsp;";
		foreach($arHtmlControl["VALUE"] as $value)
		{
			$result .= CFile::ShowFile($value, $arUserField["SETTINGS"]["MAX_SHOW_SIZE"], $arUserField["SETTINGS"]["LIST_WIDTH"], $arUserField["SETTINGS"]["LIST_HEIGHT"], true)."<br>";
		}

		return $result;
	}

	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		if(!is_array($value))
		{
			if($value > 0)
			{
				$fileInputUtility = FileInputUtility::instance();

				$checkResult = $fileInputUtility->checkFiles($fileInputUtility->getUserFieldCid($arUserField), array($value));

				if(!in_array($value, $checkResult))
				{
					$aMsg[] = array(
						"id" => $arUserField["FIELD_NAME"],
						"text" => GetMessage("FILE_BAD_TYPE"),
					);
				}
			}

			if($value > 0)
			{
				$fileInfo = \CFile::GetFileArray($value);
				if($fileInfo)
				{
					$value = \CFile::MakeFileArray($fileInfo['SRC']);
				}
			}
		}

		if(is_array($value))
		{
			if($arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"] > 0 && $value["size"] > $arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"])
			{
				$aMsg[] = array(
					"id" => $arUserField["FIELD_NAME"],
					"text" => GetMessage("USER_TYPE_FILE_MAX_SIZE_ERROR",
						array(
							"#FIELD_NAME#" => $arUserField["EDIT_FORM_LABEL"],
							"#MAX_ALLOWED_SIZE#" => $arUserField["SETTINGS"]["MAX_ALLOWED_SIZE"]
						)
					),
				);
			}

			//Extention check
			if(is_array($arUserField["SETTINGS"]["EXTENSIONS"]) && count($arUserField["SETTINGS"]["EXTENSIONS"]))
			{
				foreach($arUserField["SETTINGS"]["EXTENSIONS"] as $ext => $tmp_val)
				{
					$arUserField["SETTINGS"]["EXTENSIONS"][$ext] = $ext;
				}
				$error = CFile::CheckFile($value, 0, false, implode(",", $arUserField["SETTINGS"]["EXTENSIONS"]));
			}
			else
			{
				$error = "";
			}

			if(strlen($error))
			{
				$aMsg[] = array(
					"id" => $arUserField["FIELD_NAME"],
					"text" => $error,
				);
			}

			//For user without edit php permissions
			//we allow only pictures upload
			global $USER;
			if(!is_object($USER) || !$USER->IsAdmin())
			{
				if(HasScriptExtension($value["name"]))
				{
					$aMsg[] = array(
						"id" => $arUserField["FIELD_NAME"],
						"text" => GetMessage("FILE_BAD_TYPE")." (".$value["name"].").",
					);
				}
			}
		}

		return $aMsg;
	}

	function OnBeforeSave($arUserField, $value)
	{
		// old mechanism
		if(is_array($value))
		{
			//Protect from user manipulation
			if(isset($value["old_id"]) && $value["old_id"] > 0)
			{
				if(is_array($arUserField["VALUE"]))
				{
					if(!in_array($value["old_id"], $arUserField["VALUE"]))
					{
						unset($value["old_id"]);
					}
				}
				else
				{
					if($arUserField["VALUE"] != $value["old_id"])
					{
						unset($value["old_id"]);
					}
				}
			}

			if($value["del"] && $value["old_id"])
			{
				\CFile::Delete($value["old_id"]);
				$value["old_id"] = false;
			}

			if($value["error"])
			{
				return $value["old_id"];
			}
			else
			{
				if($value["old_id"])
				{
					\CFile::Delete($value["old_id"]);
				}
				$value["MODULE_ID"] = "main";
				$id = \CFile::SaveFile($value, "uf");

				return $id;
			}
		}
		// new mechanism - mail.file.input
		else
		{
			$fileInputUtility = FileInputUtility::instance();
			$controlId = $fileInputUtility->getUserFieldCid($arUserField);

			if($value > 0)
			{
				$checkResult = $fileInputUtility->checkFiles($controlId, array($value));

				if(!in_array($value, $checkResult))
				{
					$value = false;
				}
			}

			if($value > 0)
			{
				$delResult = $fileInputUtility->checkDeletedFiles($controlId);
				if(in_array($value, $delResult))
				{
					$value = false;
				}
			}

			return $value;
		}
	}

	function OnSearchIndex($arUserField)
	{
		static $max_file_size = null;
		$res = '';

		if(is_array($arUserField["VALUE"]))
		{
			$val = $arUserField["VALUE"];
		}
		else
		{
			$val = array($arUserField["VALUE"]);
		}

		$val = array_filter($val, "strlen");
		if(count($val))
		{
			$val = array_map(array("CUserTypeFile", "__GetFileContent"), $val);
			$res = implode("\r\n", $val);
		}

		return $res;
	}

	function __GetFileContent($FILE_ID)
	{
		static $max_file_size = null;

		$arFile = CFile::MakeFileArray($FILE_ID);
		if($arFile && $arFile["tmp_name"])
		{
			if(!isset($max_file_size))
			{
				$max_file_size = COption::GetOptionInt("search", "max_file_size", 0) * 1024;
			}

			if($max_file_size > 0 && $arFile["size"] > $max_file_size)
			{
				return "";
			}

			$arrFile = false;
			foreach(GetModuleEvents("search", "OnSearchGetFileContent", true) as $arEvent)
			{
				if($arrFile = ExecuteModuleEventEx($arEvent, array($arFile["tmp_name"])))
				{
					break;
				}
			}

			if(is_array($arrFile))
			{
				return $arrFile["CONTENT"];
			}
		}

		return "";
	}

	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';
		$first = true;

		foreach($value as $res)
		{
			if(!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			$first = false;

			$tag = '';

			$fileInfo = CFile::GetFileArray($res);
			if($fileInfo)
			{
				if(CFile::IsImage($fileInfo["SRC"], $fileInfo["CONTENT_TYPE"]))
				{
					$tag .= CFile::ShowImage(
						$fileInfo,
						$arAdditionalParameters["FILE_MAX_WIDTH"],
						$arAdditionalParameters["FILE_MAX_HEIGHT"],
						"",
						"",
						($arAdditionalParameters["FILE_SHOW_POPUP"] == "Y"),
						false,
						0,
						0,
						$arAdditionalParameters["URL_TEMPLATE"]
					);
				}
				else
				{
					if($arAdditionalParameters["URL_TEMPLATE"] <> '')
					{
						$src = \CComponentEngine::MakePathFromTemplate($arAdditionalParameters["URL_TEMPLATE"], array('file_id' => $fileInfo["ID"]));
					}
					else
					{
						$src = $fileInfo["SRC"];
					}
					$tag .= '<a href="'.htmlspecialcharsbx($src).'">'.htmlspecialcharsbx($fileInfo["ORIGINAL_NAME"]).'</a> ('.\CFile::formatSize($fileInfo["FILE_SIZE"]).')';
				}

				$html .= static::getHelper()->wrapSingleField($tag);
			}
		}

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		// fieldName will be normalized inside main.file.input
		$fieldName = $arUserField["FIELD_NAME"];
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		ob_start();

		global $APPLICATION;

		$fileInputUtility = FileInputUtility::instance();
		$APPLICATION->IncludeComponent(
			'bitrix:main.file.input',
			'.default',
			array(
				'CONTROL_ID' => $fileInputUtility->getUserFieldCid($arUserField),
				'INPUT_NAME' => $fieldName,
				'INPUT_NAME_UNSAVED' => $fieldName.'_tmp',
				'INPUT_VALUE' => $value,
				'MULTIPLE' => $arUserField['MULTIPLE'] === 'Y' ? 'Y' : 'N',
				'MODULE_ID' => 'uf',
				'ALLOW_UPLOAD' => 'A',
			)
		);

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult(ob_get_clean());
	}
}

