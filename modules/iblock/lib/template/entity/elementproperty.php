<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\Template\Entity;

class ElementProperty extends Base
{
	protected $iblockId = 0;
	protected $properties = array();
	protected $elementLinkProperties = array();
	protected $sectionLinkProperties = array();
	/**
	 * @param integer $id Iblock element identifier.
	 */
	public function __construct($id)
	{
		parent::__construct($id);
	}

	/**
	 * Set the iblock of the element.
	 *
	 * @param integer $iblockId Iblock identifier.
	 *
	 * @return void
	 */
	public function setIblockId($iblockId)
	{
		$this->iblockId = intval($iblockId);
	}

	/**
	 * Used to find entity for template processing.
	 *
	 * @param string $entity What to find.
	 *
	 * @return \Bitrix\Iblock\Template\Entity\Base
	 */
	public function resolve($entity)
	{
		if ($this->loadFromDatabase())
		{
			if (isset($this->elementLinkProperties[$entity]))
			{
				if (!is_object($this->elementLinkProperties[$entity]))
					$this->elementLinkProperties[$entity] = Element::getInstance($this->elementLinkProperties[$entity]);
				return $this->elementLinkProperties[$entity];
			}
			elseif (isset($this->sectionLinkProperties[$entity]))
			{
				if (!is_object($this->sectionLinkProperties[$entity]))
					$this->sectionLinkProperties[$entity] = Element::getInstance($this->sectionLinkProperties[$entity]);
				return $this->sectionLinkProperties[$entity];
			}
		}
		return parent::resolve($entity);
	}

	/**
	 * Used to initialize entity fields from some external source.
	 *
	 * @param array $fields Entity fields.
	 *
	 * @return void
	 */
	public function setFields(array $fields)
	{
		parent::setFields($fields);
		if (
			is_array($this->fields)
			&& $this->iblockId > 0
		)
		{
			$properties = array();
			$propertyList = \Bitrix\Iblock\PropertyTable::getList(array(
				"select" => array("*"),
				"filter" => array("=IBLOCK_ID" => $this->iblockId),
			));
			while ($row = $propertyList->fetch())
			{
				if ($row["USER_TYPE_SETTINGS"])
				{
					$row["USER_TYPE_SETTINGS"] = unserialize(
						$row["USER_TYPE_SETTINGS"],
						array('allowed_classes' => false)
					);
				}

				$properties[$row["ID"]] = $row;
				if ($row["CODE"] != "")
					$properties[$row["CODE"]] = &$properties[$row["ID"]];
			}

			foreach ($fields as $propertyCode => $propertyValues)
			{
				if (is_array($propertyValues))
				{
					foreach ($propertyValues as $i => $propertyValue)
					{
						if (is_array($propertyValue) && array_key_exists("VALUE", $propertyValue))
						{
							if ($propertyValue["VALUE"] != "")
								$propertyValues[$i] = $propertyValue["VALUE"];
							else
								unset($propertyValues[$i]);
						}
					}
				}

				if (isset($properties[$propertyCode]))
				{
					$property = $properties[$propertyCode];
					$fieldCode = mb_strtolower($propertyCode);

					if ($property["PROPERTY_TYPE"] === "L")
					{
						if (is_numeric($propertyValues))
						{
							$value = new ElementPropertyEnum($propertyValues);
						}
						elseif (is_array($propertyValues))
						{
							$value = array();
							foreach ($propertyValues as $propertyValue)
							{
								if (is_numeric($propertyValue))
									$value[] = new ElementPropertyEnum($propertyValue);
							}
						}
						else
						{
							$value = $propertyValues;
						}
					}
					elseif ($property["PROPERTY_TYPE"] === "E")
					{
						if ($propertyValues instanceof Element)
						{
							$this->elementLinkProperties[$fieldCode] = $propertyValues;
							$value = $propertyValues->getField("name");
						}
						elseif (is_numeric($propertyValues))
						{
							$this->elementLinkProperties[$fieldCode] = $propertyValues;
							$value = new ElementPropertyElement($propertyValues);
						}
						elseif (is_array($propertyValues))
						{
							$value = array();
							foreach ($propertyValues as $propertyValue)
							{
								if (is_numeric($propertyValue))
									$value[] = new ElementPropertyElement($propertyValue);
							}
						}
						else
						{
							$value = $propertyValues;
						}
					}
					elseif ($property["PROPERTY_TYPE"] === "G")
					{
						if ($propertyValues instanceof Section)
						{
							$this->sectionLinkProperties[$fieldCode] = $propertyValues;
							$value = $propertyValues->getField("name");
						}
						elseif (is_numeric($propertyValues))
						{
							$this->sectionLinkProperties[$fieldCode] = $propertyValues;
							$value = new ElementPropertySection($propertyValues);
						}
						elseif (is_array($propertyValues))
						{
							$value = array();
							foreach ($propertyValues as $propertyValue)
							{
								if (is_numeric($propertyValue))
									$value[] = new ElementPropertySection($propertyValue);
							}
						}
						else
						{
							$value = $propertyValues;
						}
					}
					else
					{
						if($property["USER_TYPE"] <> '')
						{
							if(is_array($propertyValues))
							{
								$value = array();
								foreach($propertyValues as $propertyValue)
								{
									$value[] = new ElementPropertyUserField($propertyValue, $property);
								}
							}
							else
							{
								$value = new ElementPropertyUserField($propertyValues, $property);
							}
						}
						else
						{
							$value = $propertyValues;
						}
					}

					$this->fieldMap[$fieldCode] = $property["ID"];
					$this->fieldMap[$property["ID"]] = $property["ID"];
					if ($property["CODE"] != "")
						$this->fieldMap[mb_strtolower($property["CODE"])] = $property["ID"];

					$this->fields[$property["ID"]] = $value;
				}
			}
		}
	}

	/**
	 * Loads values from database.
	 * Returns true on success.
	 *
	 * @return boolean
	 */
	protected function loadFromDatabase()
	{
		if (!isset($this->fields) && $this->iblockId > 0)
		{
			$this->fields = array();
			$this->fieldMap = array();
			$propertyList = \CIBlockElement::getProperty(
				$this->iblockId,
				$this->id,
				array("sort" => "asc"),
				array("EMPTY" => "N")
			);
			while ($property = $propertyList->fetch())
			{
				if ($property["VALUE_ENUM"] != "")
				{
					$value = $property["VALUE_ENUM"];
				}
				elseif ($property["PROPERTY_TYPE"] === "E")
				{
					$this->elementLinkProperties[$property["ID"]] = $property["VALUE"];
					if ($property["CODE"] != "")
						$this->elementLinkProperties[mb_strtolower($property["CODE"])] = $property["VALUE"];
					$value = new ElementPropertyElement($property["VALUE"]);
				}
				elseif ($property["PROPERTY_TYPE"] === "G")
				{
					$this->sectionLinkProperties[$property["ID"]] = $property["VALUE"];
					if ($property["CODE"] != "")
						$this->sectionLinkProperties[mb_strtolower($property["CODE"])] = $property["VALUE"];
					$value = new ElementPropertySection($property["VALUE"]);
				}
				else
				{
					if($property["USER_TYPE"] <> '')
					{
						$value = new ElementPropertyUserField($property["VALUE"], $property);
					}
					else
					{
						$value = $property["VALUE"];
					}
				}

				$this->fieldMap[$property["ID"]] = $property["ID"];
				if ($property["CODE"] != "")
					$this->fieldMap[mb_strtolower($property["CODE"])] = $property["ID"];

				if ($property["MULTIPLE"] == "Y")
					$this->fields[$property["ID"]][] = $value;
				else
					$this->fields[$property["ID"]] = $value;
			}
		}
		return is_array($this->fields);
	}
}

class ElementPropertyUserField extends LazyValueLoader
{
	/** @var array  */
	private $property = null;

	/**
	 * @param integer $key  Iblock element identifier.
	 * @param array|mixed $property Iblock property array.
	 */
	function __construct($key, $property)
	{
		parent::__construct($key);
		if (is_array(($property)))
		{
			$this->property = $property;
		}
	}
	/**
	 * Actual work method which have to retrieve data from the DB.
	 *
	 * @return mixed
	 */
	protected function load()
	{
		$propertyFormatFunction = $this->getFormatFunction();
		if ($propertyFormatFunction)
		{
			return call_user_func_array($propertyFormatFunction,
				array(
					$this->property,
					array("VALUE" => $this->key),
					array("MODE" => "ELEMENT_TEMPLATE"),
				)
			);
		}
		else
		{
			return $this->key;
		}
	}
	/**
	 * Retruns GetPublicViewHTML handler function for $this->property.
	 * Returns false if no handler defined.
	 *
	 * @return callable|false
	 */
	protected function getFormatFunction()
	{
		static $propertyFormatFunction = array();
		if (!isset($propertyFormatFunction[$this->property["ID"]]))
		{
			$propertyFormatFunction[$this->property["ID"]] = false;
			if ($this->property && mb_strlen($this->property["USER_TYPE"]))
			{
				$propertyUserType = \CIBlockProperty::getUserType($this->property["USER_TYPE"]);
				if(
					array_key_exists("GetPublicViewHTML", $propertyUserType)
					&& is_callable($propertyUserType["GetPublicViewHTML"])
				)
				{
					$propertyFormatFunction[$this->property["ID"]] = $propertyUserType["GetPublicViewHTML"];
				}
			}
		}
		return $propertyFormatFunction[$this->property["ID"]];
	}
}

class ElementPropertyEnum extends LazyValueLoader
{
	/**
	 * Actual work method which have to retrieve data from the DB.
	 *
	 * @return mixed
	 */
	protected function load()
	{
		$enumList = \Bitrix\Iblock\PropertyEnumerationTable::getList(array(
			"select" => array("VALUE"),
			"filter" => array("=ID" => $this->key),
		));
		$enum = $enumList->fetch();
		if ($enum)
			return $enum["VALUE"];
		else
			return "";
	}
}

class ElementPropertyElement extends LazyValueLoader
{
	/**
	 * Actual work method which have to retrieve data from the DB.
	 *
	 * @return mixed
	 */
	protected function load()
	{
		$elementList = \Bitrix\Iblock\ElementTable::getList(array(
			"select" => array("NAME"),
			"filter" => array("=ID" => $this->key),
		));
		$element = $elementList->fetch();
		if ($element)
			return $element["NAME"];
		else
			return "";
	}
}

class ElementPropertySection extends LazyValueLoader
{
	/**
	 * Actual work method which have to retrieve data from the DB.
	 *
	 * @return mixed
	 */
	protected function load()
	{
		$sectionList = \Bitrix\Iblock\SectionTable::getList(array(
			"select" => array("NAME"),
			"filter" => array("=ID" => $this->key),
		));
		$section = $sectionList->fetch();
		if ($section)
			return $section["NAME"];
		else
			return "";
	}
}
