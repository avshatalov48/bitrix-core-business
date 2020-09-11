<?
namespace Bitrix\Lists\Entity;

use Bitrix\Lists\Service\Param;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\ErrorableImplementation;

class Field implements Controllable, Errorable
{
	use ErrorableImplementation;

	const ERROR_SAVE_FIELD = "ERROR_SAVE_FIELD";
	const ERROR_UPDATE_FIELD = "ERROR_UPDATE_FIELD";

	private $param;
	private $params = [];
	private $fieldList = [];

	private $iblockId;

	public function __construct(Param $param)
	{
		$this->param = $param;
		$this->params = $param->getParams();

		$this->fieldList = ["NAME", "IS_REQUIRED", "MULTIPLE", "TYPE", "SORT", "DEFAULT_VALUE", "LIST", 
			"USER_TYPE_SETTINGS", "LIST_TEXT_VALUES", "LIST_DEF", "CODE", "SETTINGS", "ROW_COUNT", 
			"COL_COUNT", "LINK_IBLOCK_ID"];

		$this->iblockId = Utils::getIblockId($this->params);

		$this->errorCollection = new ErrorCollection;
	}
	
	public function add()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID", ["FIELDS" => ["NAME", "TYPE"]]]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return false;
		}

		$fields = $this->getFields();
		$this->validateFields($fields);
		if ($this->hasErrors())
		{
			return false;
		}

		$object = new \CList($this->iblockId);

		if (!$object->is_field($fields["TYPE"]))
		{
			if (!empty($fields["CODE"]))
			{
				$property = $this->getProperty($this->iblockId, $fields["CODE"]);
				if (!empty($property) && is_array($property))
				{
					$this->errorCollection->setError(new Error("Property already exists", self::ERROR_SAVE_FIELD));
					return false;
				}
			}
			else
			{
				$this->errorCollection->setError(new Error("Please fill the code fields", self::ERROR_SAVE_FIELD));
			}
		}

		$result = $object->addField($fields);

		if ($result)
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->clearByTag("lists_list_".$this->iblockId);
			return $result;
		}
		else
		{
			$this->errorCollection->setError(new Error("Error saving the field", self::ERROR_SAVE_FIELD));
			return false;
		}
	}

	public function get(array $navData = [])
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return [];
		}

		$fields = [];

		if (!empty($this->params["FIELD_ID"]))
		{
			$object = new \CListFieldList($this->iblockId);
			$fieldsObject = $object->getByID($this->params["FIELD_ID"]);
			if ($fieldsObject)
			{
				$fieldData = $fieldsObject->getArray();
				$fields = [$fieldData["TYPE"] => $fieldData];
			}
		}
		else
		{
			$object = new \CList($this->iblockId);
			$fields = $object->getFields();
		}

		return $this->adjustmentFields($fields);
	}

	public function update()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID",
			"FIELD_ID", ["FIELDS" => ["NAME", "TYPE"]]]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return [];
		}

		$fields = $this->getFields();
		$this->validateFields($fields);
		if ($this->hasErrors())
		{
			return false;
		}

		$object = new \CList($this->iblockId);
		if (!$this->canChangeField($fields["TYPE"]))
		{
			return false;
		}

		$result = $object->updateField($this->params["FIELD_ID"], $fields);

		if ($result)
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->clearByTag("lists_list_".$this->iblockId);
			return $result;
		}
		else
		{
			$this->errorCollection->setError(new Error("Error update the field", self::ERROR_UPDATE_FIELD));
			return false;
		}
	}

	public function delete()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID", "FIELD_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return [];
		}

		$object = new \CList($this->iblockId);
		$object->deleteField($this->params["FIELD_ID"]);
		$object->save();

		global $CACHE_MANAGER;
		$CACHE_MANAGER->clearByTag('lists_list_'.$this->iblockId);

		return true;
	}

	public function getAvailableTypes()
	{
		$this->param->checkRequiredInputParams(["IBLOCK_CODE", "IBLOCK_ID"]);
		if ($this->param->hasErrors())
		{
			$this->errorCollection->add($this->param->getErrors());
			return [];
		}

		$fieldId = (!empty($this->params["FIELD_ID"]) ? $this->params["FIELD_ID"] : "");
		$object = new \CList($this->iblockId);
		return $object->getAvailableTypes($fieldId);
	}
	
	private function getFields()
	{
		$fields = [];

		foreach ($this->params["FIELDS"] as $fieldId => $fieldValue)
		{
			if (!in_array($fieldId, $this->fieldList))
			{
				continue;
			}

			if (is_array($fieldValue))
			{
				$fieldValue = array_change_key_case($fieldValue, CASE_UPPER);
			}

			$fields[$fieldId] = $fieldValue;
		}

		if (!is_array($fields["LIST"]))
		{
			$fields["LIST"] = [];
		}
		if (!empty($fields["LIST_TEXT_VALUES"]))
		{
			$maxSort = 0;
			$listMap = array();
			foreach ($fields["LIST"] as $key => $enum)
			{
				if ($enum["SORT"] > $maxSort)
				{
					$maxSort = intval($enum["SORT"]);
				}
				$listMap[trim($enum["VALUE"], " \t\n\r")] = $enum["ID"];
			}
			foreach (explode("\n", $fields["LIST_TEXT_VALUES"]) as $valueLine)
			{
				$value = trim($valueLine, " \t\n\r");
				if ((string) $value <> '' && !isset($listMap[$value]))
				{
					$maxSort += 10;
					$listMap[$value] = "m".$maxSort;
					$fields["LIST"]["m".$maxSort] = array(
						"SORT" => $maxSort,
						"VALUE" => $value,
					);
				}
			}
		}
		if (!empty($fields["LIST_DEF"]) && is_array($fields["LIST_DEF"]))
		{
			foreach ($fields["LIST"] as $key => $enum)
			{
				$fields["LIST"][$key]["DEF"] = "N";
			}
			foreach ($fields["LIST_DEF"] as $def)
			{
				$def = intval($def);
				if ($def > 0 && isset($fields["LIST"][$def]))
				{
					$fields["LIST"][$def]["DEF"] = "Y";
				}
			}
		}

		return $fields;
	}

	private function canChangeField($type)
	{
		$objectFieldList = new \CListFieldList($this->iblockId);
		$fieldObject = $objectFieldList->getByID($this->params["FIELD_ID"]);
		if ($fieldObject)
		{
			$oldType = $fieldObject->getTypeID();
			if ($oldType != $type)
			{
				$this->errorCollection->setError(new Error("Field type can not be changed", self::ERROR_UPDATE_FIELD));
				return false;
			}
		}
		return true;
	}

	private function validateFields($fields)
	{
		if (isset($fields["SETTINGS"]["ADD_READ_ONLY_FIELD"]) && $fields["SETTINGS"]["ADD_READ_ONLY_FIELD"] == "Y")
		{
			$defaultValueError = false;
			switch ($fields["TYPE"])
			{
				case "SORT":
					$defaultValueError = ($fields["DEFAULT_VALUE"] == '');
					break;
				case "L":
					if (is_array($fields["LIST_DEF"]))
					{
						$defaultValueError = (empty(current($fields["LIST_DEF"])));
					}
					break;
				case "S:HTML":
					$defaultValueError = (empty($fields["DEFAULT_VALUE"]["TEXT"]));
					break;
				default:
					$defaultValueError = (empty($fields["DEFAULT_VALUE"]));
			}
			if ($defaultValueError)
			{
				$this->errorCollection->setError(new Error(
					"The default value of the field \"".$fields["NAME"]."\" is required", self::ERROR_SAVE_FIELD));
			}
		}

		$formatError = "";
		if ($fields["TYPE"] == "PREVIEW_PICTURE")
		{
			$fields["DEFAULT_VALUE"]["METHOD"] = "resample";
			$fields["DEFAULT_VALUE"]["COMPRESSION"] = intval(\COption::getOptionString("main", "image_resize_quality", "95"));
		}
		elseif ($fields["TYPE"] == "S:Date")
		{
			if (!empty($fields["DEFAULT_VALUE"]) && !CheckDateTime($fields["DEFAULT_VALUE"], FORMAT_DATE))
			{
				$formatError = "The default value of the field \"".$fields["NAME"]."\" is incorrect";
			}
		}
		elseif($fields["TYPE"] == "S:DateTime")
		{
			if(!empty($fields["DEFAULT_VALUE"]) && !CheckDateTime($fields["DEFAULT_VALUE"]))
			{
				$formatError = "The default value of the field \"".$fields["NAME"]."\" is incorrect";
			}
		}
		if (preg_match("/^(G|G:|E|E:)/", $fields["TYPE"]))
		{
			$blocks = \CLists::getIBlocks($this->params["IBLOCK_TYPE_ID"], "Y", $this->params["SOCNET_GROUP_ID"]);
			if (mb_substr($fields["TYPE"], 0, 1) == "G")
			{
				unset($blocks[$this->params["IBLOCK_ID"]]);
			}
			if (!array_key_exists($fields["LINK_IBLOCK_ID"], $blocks))
			{
				$formatError = "Incorrect lists specified for \"".$fields["NAME"]."\" property";
			}
		}
		if ($formatError)
		{
			$this->errorCollection->setError(new Error(
				"The default value of the field \"".$fields["NAME"]."\" is required", self::ERROR_SAVE_FIELD));
		}
	}

	private function adjustmentFields($fields)
	{
		foreach ($fields as $fieldId => &$field)
		{
			if ($field["TYPE"] == "ACTIVE_FROM")
			{
				if ($field["DEFAULT_VALUE"] === "=now")
					$field["DEFAULT_VALUE"] = ConvertTimeStamp(time()+\CTimeZone::getOffset(), "FULL");
				elseif ($field["DEFAULT_VALUE"] === "=today")
					$field["DEFAULT_VALUE"] = ConvertTimeStamp(time()+\CTimeZone::getOffset(), "SHORT");
			}
			elseif ($field["TYPE"] == "L")
			{
				$option = [];
				$propertyEnum = \CIBlockProperty::getPropertyEnum($field["ID"]);
				while ($listEnum = $propertyEnum->fetch())
				{
					if ($listEnum["DEF"] === "Y")
					{
						$field["DEFAULT_VALUE"] = $listEnum["ID"];
					}
					$option[$listEnum["ID"]] = $listEnum["VALUE"];
				}
				$field["DISPLAY_VALUES_FORM"] = $option;
			}
			elseif ($field["TYPE"] == "G")
			{
				$option = [];
				$sections = \CIBlockSection::getTreeList(["IBLOCK_ID" => $field["LINK_IBLOCK_ID"]]);
				while ($section = $sections->getNext())
					$option[$section["ID"]] = str_repeat(" . ", $section["DEPTH_LEVEL"]).$section["~NAME"];
				$field["DISPLAY_VALUES_FORM"] = $option;
			}
			elseif (preg_match("/^(E|E:)/", $field["TYPE"]))
			{
				$option = [];
				$elements = \CIBlockElement::getList(["NAME"=>"ASC"],
					["IBLOCK_ID" => $field["LINK_IBLOCK_ID"]], false, false, ["ID", "NAME"]);
				while ($element = $elements->fetch())
					$option[$element["ID"]] = $element["NAME"];
				$field["DISPLAY_VALUES_FORM"] = $option;
			}
			elseif ($field["TYPE"] == "N:Sequence")
			{
				$sequence = new \CIBlockSequence($field["IBLOCK_ID"], $field["ID"]);
				$field["USER_TYPE_SETTINGS"]["VALUE"] = $sequence->getNext();
			}
		}

		return $fields;
	}

	private function getProperty($iblockId, $code)
	{
		$queryObject = \CIBlockProperty::getList(array(), array("IBLOCK_ID" => $iblockId, "CODE" => $code));
		return $queryObject->fetch();
	}
}