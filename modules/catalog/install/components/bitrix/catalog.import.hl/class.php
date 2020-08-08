<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CBitrixCatalogImportHl extends CBitrixComponent
{
	public $error = "";
	public $message = "";
	public $NS = false;
	public $xmlStream = null;
	public $dataClass = "";
	public $step = false;

	public function referenceHead($path, $attr)
	{
		if (is_array($attr) && $attr[GetMessage("CC_BCIH_XML_CHANGES_ONLY")] === 'true')
			$this->NS["CHO"] = true;
		else
			$this->NS["CHO"] = false;
	}

	public function referenceStart($path, $attr)
	{
		$this->NS["fp"] = $this->decodePostion($this->xmlStream->getPosition());
	}

	public function referenceItemsStart($path, $attr)
	{
		if ($this->NS["fp"] <= 0)
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 10));
		}
		else
		{
			$this->deleteBySessid($this->NS["HL"], $this->NS["SESSID"]);

			$xmlPosition = $this->xmlStream->getPosition();
			$filePosition = $this->decodePostion($xmlPosition);
			$xmlString = $this->xmlStream->readFilePart($this->NS["fp"], $filePosition);
			$error = "";
			if ($xmlPosition[0])
				$xmlString = CharsetConverter::convertCharset($xmlString, $xmlPosition[0], LANG_CHARSET, $error);
			$xmlString .= "</".GetMessage("CC_BCIH_XML_REFERENCE").">";
			$xmlObject = new CDataXML;
			if ($xmlObject->loadString($xmlString))
			{
				$highBlockID = $this->checkReference($xmlObject->GetArray());
				$this->dataClass = "";
				$this->step = true;
				$this->message = GetMessage("CC_BCIH_REFERENCE_FOUND_OR_CREATED", array("#CODE#" => $highBlockID));
				if ($highBlockID > 0)
				{
					$this->NS["HL"] = $highBlockID;
					$this->NS["FM"] = array();
					$this->NS["C"] = 0;
				}
				else
				{
					if (!$this->error)
					{
						$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 30));
					}
				}
			}
			else
			{
				$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 20));
			}
		}
	}

	public function checkReference($xmlArray)
	{
		global $APPLICATION, $DB;
		$highBlockName = $this->xml2id($xmlArray[GetMessage("CC_BCIH_XML_REFERENCE")]["#"][GetMessage("CC_BCIH_XML_ID")][0]["#"]);
		if (!is_string($highBlockName) || $highBlockName === "")
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 110));
			return 0;
		}

		$name = $xmlArray[GetMessage("CC_BCIH_XML_REFERENCE")]["#"][GetMessage("CC_BCIH_XML_NAME")][0]["#"];
		if (!is_string($name) || $name === "")
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 120));
			return 0;
		}

		$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
			"filter" => array(
				"=NAME" => $highBlockName,
			)
		))->fetch();

		if ($hlblock)
		{
			$highBlockID = $hlblock["ID"];
		}
		else
		{
			$result = Bitrix\Highloadblock\HighloadBlockTable::add(array(
				'NAME' => $highBlockName,
				'TABLE_NAME' => 'b_'.mb_strtolower($highBlockName),
			));
			if (!$result->isSuccess())
			{
				$this->error = GetMessage("CC_BCIH_REFERENCE_ERROR", array("#MESSAGE#" => implode($result->getErrorMessages())));
				return 0;
			}

			$highBlockID = $result->getId();

			$arFieldsName = array(
				'UF_NAME' => array("Y", "string", "Y"),
				'UF_XML_ID' => array("Y", "string", "N"),
				'UF_VERSION' => array("Y", "string", "N"),
				'UF_DESCRIPTION' => array("N", "string", "Y"),
			);
			$obUserField = new CUserTypeEntity();
			$sort = 100;
			foreach($arFieldsName as $fieldName => $fieldValue)
			{
				$arUserField = array(
					"ENTITY_ID" => "HLBLOCK_".$highBlockID,
					"FIELD_NAME" => $fieldName,
					"USER_TYPE_ID" => $fieldValue[1],
					"XML_ID" => "",
					"SORT" => $sort,
					"MULTIPLE" => "N",
					"MANDATORY" => $fieldValue[0],
					"SHOW_FILTER" => "N",
					"SHOW_IN_LIST" => $fieldValue[2],
					"EDIT_IN_LIST" => $fieldValue[2],
					"IS_SEARCHABLE" => "N",
					"SETTINGS" => array(),
				);
				$res = $obUserField->Add($arUserField);
				if ($res)
				{
					$sort += 100;
				}
				else
				{
					if ($ex = $APPLICATION->GetException())
						$this->error = GetMessage("CC_BCIH_FIELD_ERROR", array("#MESSAGE#" => $ex->GetString()));
					else
						$this->error = GetMessage("CC_BCIH_UNKNOWN_ERROR", array("#CODE#" => 130));

					return 0;
				}
			}
			if ($DB->type === "MYSQL")
				$len = "(50)";
			else
				$len = "";
			$DB->Query("create index IX_HLBLOCK_".$highBlockID."_XML_ID on b_".mb_strtolower($highBlockName)."(UF_XML_ID$len)");
		}

		return $highBlockID;
	}

	public function referenceField(CDataXML $xmlObject)
	{
		global $APPLICATION;

		if ($this->NS["HL"] <= 0)
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 210));
			return 0;
		}
		$entity_id = "HLBLOCK_".$this->NS["HL"];
		$xmlArray = $xmlObject->GetArray();

		$xmlId = $xmlArray[GetMessage("CC_BCIH_XML_FIELD")]["#"][GetMessage("CC_BCIH_XML_ID")][0]["#"];
		if ($xmlId === GetMessage("CC_BCIH_XML_NAME"))
			$xmlId = "NAME";
		elseif ($xmlId === GetMessage("CC_BCIH_XML_DESCRIPTION"))
			$xmlId = "DESCRIPTION";

		$id = $this->xml2id($xmlId);
		if (!is_string($id) || $id === "")
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 220));
			return 0;
		}
		$id = mb_substr("UF_".mb_strtoupper($id), 0, 20);

		$name = $xmlArray[GetMessage("CC_BCIH_XML_FIELD")]["#"][GetMessage("CC_BCIH_XML_NAME")][0]["#"];
		if (!is_string($name) || $name === "")
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 230));
			return 0;
		}

		$type = $xmlArray[GetMessage("CC_BCIH_XML_FIELD")]["#"][GetMessage("CC_BCIH_XML_FIELD_TYPE")][0]["#"];
		if ($type === GetMessage("CC_BCIH_XML_FIELD_TYPE_STRING"))
			$type = "string";
		elseif ($type === GetMessage("CC_BCIH_XML_FIELD_TYPE_BOOL"))
			$type = "boolean";
		elseif ($type === GetMessage("CC_BCIH_XML_FIELD_TYPE_DATE"))
			$type = "datetime";
		elseif ($type === GetMessage("CC_BCIH_XML_FIELD_TYPE_FLOAT"))
			$type = "double";
		else
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 240));
			return 0;
		}

		$rsUserFields = CUserTypeEntity::GetList(array(), array(
			"ENTITY_ID" => $entity_id,
			"FIELD_NAME" => $id,
		));
		$arDBField = $rsUserFields->Fetch();
		if ($arDBField)
		{
			$this->NS["FM"][$arDBField["FIELD_NAME"]] = $arDBField["USER_TYPE_ID"];
			return $arDBField["ID"];
		}
		else
		{
			$sort = 500; //TODO
			$obUserField = new CUserTypeEntity();
			$arUserField = array(
				"ENTITY_ID" => $entity_id,
				"FIELD_NAME" => $id,
				"USER_TYPE_ID" => $type,
				"XML_ID" => $xmlId,
				"SORT" => $sort,
				"MULTIPLE" => "N",
				"MANDATORY" => "N",
				"SHOW_FILTER" => "N",
				"SHOW_IN_LIST" => "Y",
				"EDIT_IN_LIST" => "Y",
				"IS_SEARCHABLE" => "N",
				"SETTINGS" => array(),
				"EDIT_FORM_LABEL" => array(
					LANGUAGE_ID => $name,
				),
			);
			$res = $obUserField->Add($arUserField);
			if ($res)
			{
				$this->NS["FM"][$arUserField["FIELD_NAME"]] = $arUserField["USER_TYPE_ID"];
			}
			else
			{
				if ($ex = $APPLICATION->GetException())
					$this->error = GetMessage("CC_BCIH_FIELD_ERROR", array("#MESSAGE#" => $ex->GetString()));
				else
					$this->error = GetMessage("CC_BCIH_UNKNOWN_ERROR", array("#CODE#" => 250));
			}
			return $res;
		}
	}

	public function referenceValuesStart($path, $attr)
	{
		$this->step = true;
		$this->message = GetMessage("CC_BCIH_ELEMENT_PROGRESS", array("#COUNT#" => intval($this->NS["C"])));
	}

	public function referenceValue(CDataXML $xmlObject)
	{
		global $APPLICATION;
		if ($this->NS["HL"] <= 0)
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 310));
			return;
		}
		$entity_id = "HLBLOCK_".$this->NS["HL"];
		$xmlArray = $xmlObject->GetArray();

		$xmlId = $xmlArray[GetMessage("CC_BCIH_XML_REFERENCE_ITEM")]["#"][GetMessage("CC_BCIH_XML_ID")][0]["#"];
		$xmlVersion = $xmlArray[GetMessage("CC_BCIH_XML_REFERENCE_ITEM")]["#"][GetMessage("CC_BCIH_XML_VERSION")][0]["#"];
		if (!is_string($xmlId) || $xmlId === "")
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 320));
			return;
		}
		$entity_data_class = $this->getDataClass($this->NS["HL"]);
		$rsData = $entity_data_class::getList(array(
			"select" => array("ID", "UF_XML_ID", "UF_VERSION"),
			"filter" => array("=UF_XML_ID" => $xmlId),
		));
		$this->NS["C"]++;

		$arFields = array(
			"UF_XML_ID" => $xmlId,
			"UF_VERSION" => $this->NS["SESSID"]."#".$xmlVersion,
		);
		$xmlFields = $xmlArray[GetMessage("CC_BCIH_XML_REFERENCE_ITEM")]["#"][GetMessage("CC_BCIH_XML_FIELD_VALUES")][0]["#"][GetMessage("CC_BCIH_XML_FIELD_VALUE")];

		if (!is_array($xmlFields))
		{
			$this->error = GetMessage("CC_BCIH_XML_PARSE_ERROR", array("#CODE#" => 330));
			return;
		}

		foreach ($xmlFields as $xml)
		{
			$xmlValue = $xml["#"][GetMessage("CC_BCIH_XML_VALUE")][0]["#"];

			$xmlValueId = $xml["#"][GetMessage("CC_BCIH_XML_NAME")][0]["#"];
			if ($xmlValueId === GetMessage("CC_BCIH_XML_NAME"))
				$xmlValueId = "NAME";
			elseif ($xmlValueId === GetMessage("CC_BCIH_XML_DESCRIPTION"))
				$xmlValueId = "DESCRIPTION";

			$xmlValueId = $this->xml2id($xmlValueId);
			$xmlValueId = mb_substr("UF_".mb_strtoupper($xmlValueId), 0, 20);

			switch ($this->NS["FM"][$xmlValueId])
			{
				case "datetime":
					if ($xmlValue === "0001-01-01T00:00:00")
					{
						$xmlValue = false;
					}
					else
					{
						$xmlValue = str_replace("T" ," ", $xmlValue);
						$xmlValue = MakeTimeStamp($xmlValue, "YYYY-MM-DD HH:MI:SS");
						$xmlValue = ConvertTimeStamp($xmlValue, "FULL");
					}
					break;
				case "boolean":
					if ($xmlValue === "true")
						$xmlValue = true;
					elseif ($xmlValue === "false")
						$xmlValue = false;
					else
						$xmlValue = false;
					break;
				case "double":
					$xmlValue = str_replace(" ", "", $xmlValue);
					$xmlValue = str_replace(",", ".", $xmlValue);
					break;
			}
			$arFields[$xmlValueId] = $xmlValue;
		}

		if ($arData = $rsData->fetch())
		{
			list($dbSessid, $dbVersion) = explode("#", $arData["UF_VERSION"], 2);
			if ($dbVersion === $xmlVersion)
			{
				if (!$this->NS["CHO"] && $dbSessid !== $this->NS["SESSID"])
				{
					$arFields = array(
						"UF_VERSION" => $arFields["UF_VERSION"],
					);
				}
				else
				{
					return;
				}
			}
			$entity_data_class::update($arData["ID"], $arFields);
		}
		else
		{
			$entity_data_class::add($arFields);
		}
	}

	public function deleteBySessid($hlblockId, $sessid)
	{
		if ($hlblockId > 0 && $sessid != "" && !$this->NS["CHO"])
		{
			$entity_data_class = $this->getDataClass($this->NS["HL"]);
			$rsData = $entity_data_class::getList(array(
				"select" => array("ID", "UF_VERSION"),
				"filter" => array("!UF_VERSION" => $sessid."#%"),
			));
			while ($arData = $rsData->fetch())
			{
				$entity_data_class::delete($arData["ID"]);
			}
		}
	}

	private function getDataClass($hlblockId)
	{
		if (!$this->dataClass)
		{
			$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
				"filter" => array(
					"=ID" => $hlblockId,
			)))->fetch();
			$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$this->dataClass = $entity->getDataClass();
		}
		return $this->dataClass;
	}

	private function decodePostion($position)
	{
		$path = explode("/", $position[2]);
		$lastPosition = end($path);
		if ($lastPosition)
			list($filePosition, ) = explode("@", $lastPosition, 2);
		else
			$filePosition = 0;
		return $filePosition;
	}

	private function xml2id($xml)
	{
		$id = CUtil::translit($xml, LANGUAGE_ID, array(
			"max_len" => 50,
			"change_case" => false, // 'L' - toLower, 'U' - toUpper, false - do not change
			"replace_space" => '_',
			"replace_other" => '_',
			"delete_repeat_replace" => true,
		));
		$id = trim($id);
		$id = preg_replace("/([^A-Za-z0-9]+)/", "", $id);
		return $id;
	}
}
?>
