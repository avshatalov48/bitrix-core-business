<?php

namespace Bitrix\Sale;

use Bitrix\Location\Entity\Address;
use Bitrix\Sale\Internals\Input;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\OrderPropsGroupTable;

/**
 * Class PropertyValueBase
 * @package Bitrix\Sale
 */
abstract class PropertyBase
{
	protected $fields = [];

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getRegistryType()
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function getField($name)
	{
		return $this->fields[$name];
	}

	/**
	 * @param array $parameters
	 * @throws Main\NotImplementedException
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		throw new Main\NotImplementedException();
	}

	/**
	 * @param $propertyId
	 * @return PropertyBase
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getObjectById($propertyId)
	{
		$dbRes = static::getList([
			'filter' => [
				'=ID' => $propertyId
			]
		]);

		$data = $dbRes->fetch();
		if ($data)
		{
			return new static($data);
		}

		return null;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getGroupInfo()
	{
		static $groupList = [];

		if (!isset($groupList[$this->getPersonTypeId()]))
		{
			$dbRes = OrderPropsGroupTable::getList([
				'filter' => [
					'=PERSON_TYPE_ID' => $this->getPersonTypeId()
				]
			]);
			while ($group = $dbRes->fetch())
			{
				$groupList[$this->getPersonTypeId()][$group['ID']] = $group;
			}
		}

		$groupId = $this->getGroupId();

		if (!isset($groupList[$this->getPersonTypeId()][$groupId]))
		{
			return [
				'ID' => 0,
				'NAME' => Loc::getMessage('SOP_UNKNOWN_GROUP'),
			];
		}

		return $groupList[$this->getPersonTypeId()][$groupId];
	}

	/**
	 * PropertyBase constructor.
	 * @param array $property
	 * @param array|null $relation
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function __construct(array $property, array $relation = null)
	{
		if (is_array($property['SETTINGS']))
		{
			$property += $property['SETTINGS'];
			unset ($property['SETTINGS']);
		}

		$this->fields = $property;

		if ($relation)
		{
			$this->fields['RELATION'] = $relation;
		}
		else
		{
			$relation = $this->loadRelations();
			if ($relation)
			{
				$this->fields['RELATION'] = $relation;
			}
		}

		if ($this->fields['TYPE'] === 'ENUM')
		{
			if (!isset($property['OPTIONS']))
			{
				$this->fields['OPTIONS'] = $this->loadOptions();
			}
		}

		$this->fields['DEFAULT_VALUE'] = $this->normalizeValue($this->fields['DEFAULT_VALUE']);
	}

	/**
	 * @param $value
	 * @return array|mixed|string|null
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function normalizeValue($value)
	{
		if ($this->fields['TYPE'] === 'FILE')
		{
			return Input\File::loadInfo($value);
		}
		elseif ($this->fields['TYPE'] === 'ADDRESS' && Main\Loader::includeModule('location'))
		{
			if (is_array($value))
			{
				/**
				 * Already normalized
				 */
				return $value;
			}
			elseif (is_numeric($value))
			{
				/**
				 * DB value
				 */
				$address = Address::load((int)$value);

				$value = ($address instanceof Address) ? $address->toArray() : null;
			}
			elseif (is_string($value) && !empty($value))
			{
				/**
				 * JSON most likely
				 */
				return Main\Web\Json::decode(
					Main\Text\Encoding::convertEncoding(
						$value,
						SITE_CHARSET,
						'UTF-8'
					)
				);
			}
		}
		elseif ($this->fields['TYPE'] === "STRING")
		{
			if ($this->fields['IS_EMAIL'] === "Y" && !empty($value))
			{
				$value = trim((string)$value);
			}

			if (Input\StringInput::isMultiple($value))
			{
				foreach ($value as $key => $data)
				{
					if (Input\StringInput::isDeletedSingle($data))
					{
						unset($value[$key]);
					}
				}
			}

			return $value;
		}

		return $value;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function loadOptions()
	{
		$options = array();

		$dbRes = Internals\OrderPropsVariantTable::getList([
			'select' => ['VALUE', 'NAME'],
			'filter' => ['ORDER_PROPS_ID' => $this->getId()],
			'order' => ['SORT' => 'ASC']
		]);

		while ($data = $dbRes->fetch())
		{
			$options[$data['VALUE']] = $data['NAME'];
		}

		return $options;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function loadRelations()
	{
		$relations = array();

		$dbRes = Internals\OrderPropsRelationTable::getList([
			'select' => ['ENTITY_ID', 'ENTITY_TYPE'],
			'filter' => ['=PROPERTY_ID' => $this->getId()]
		]);

		while ($data = $dbRes->fetch())
		{
			$relations[] = $data;
		}

		return $relations;
	}

	/**
	 * @param $personTypeId
	 * @param $request
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\SystemException
	 */
	public static function getMeaningfulValues($personTypeId, $request)
	{
		$result = [];

		$personTypeId = intval($personTypeId);
		if ($personTypeId <= 0 || !is_array($request))
		{
			return [];
		}

		$dbRes = static::getList([
			'select' => [
				'ID', 'IS_LOCATION', 'IS_EMAIL', 'IS_PROFILE_NAME',
				'IS_PAYER', 'IS_LOCATION4TAX', 'IS_ZIP', 'IS_PHONE',
				'IS_ADDRESS',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=UTIL' => 'N',
				'=PERSON_TYPE_ID' => $personTypeId
			]
		]);

		while ($row = $dbRes->fetch())
		{
			if (array_key_exists($row["ID"], $request))
			{
				foreach ($row as $key => $value)
				{
					if (($value === "Y") && (mb_substr($key, 0, 3) === "IS_"))
					{
						$result[mb_substr($key, 3)] = $request[$row["ID"]];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param $value
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function checkValue($value)
	{
		$result = new Result();

		static $errors = [];

		if (
			$this->getField('TYPE') === "STRING"
			&& (int)$this->getField('MAXLENGTH') <= 0
		)
		{
			$this->fields['MAXLENGTH'] = 500;
		}

		$error = Input\Manager::getError($this->fields, $value);

		if (!is_array($error))
		{
			$error = array($error);
		}

		foreach ($error as $item)
		{
			if (!is_array($item))
			{
				$item = [$item];
			}

			foreach ($item as $message)
			{
				if (isset($errorsList[$this->getId()]) && in_array($message, $errors[$this->getId()]))
				{
					continue;
				}

				$result->addError(
					new Main\Error(
						Loc::getMessage(
							"SALE_PROPERTY_ERROR",
							["#PROPERTY_NAME#" => $this->getField('NAME'), "#ERROR_MESSAGE#" => $message]
						)
					)
				);
			}
		}

		if (
			!is_array($value)
			&& $this->getField('IS_EMAIL') === 'Y'
			&& trim($value) !== ''
			&& !check_email(trim($value), true)
		)
		{
			$result->addError(new Main\Error(
				str_replace(
					["#EMAIL#", "#NAME#"],
					[htmlspecialcharsbx($value), htmlspecialcharsbx($this->getField('NAME'))],
					Loc::getMessage("SALE_GOPE_WRONG_EMAIL")
				)
			));
		}

		return $result;
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function checkRequiredValue($value)
	{
		static $errors = [];

		$result = new Result();

		$errorList = Input\Manager::getRequiredError($this->fields, $value);

		foreach ($errorList as $error)
		{
			if (is_array($error))
			{
				foreach ($error as $message)
				{
					$result->addError(new ResultError($this->getField('NAME').' '.$message));
					$errors[$this->getId()][] = $message;
				}
			}
			else
			{
				$result->addError(new ResultError($this->getName().' '.$error));
				$errors[$this->getId()][] = $error;
			}
		}

		return $result;
	}

	/**
	 * @param PropertyValueBase $propertyValue
	 * @return array|mixed|string|null
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	public function getPreparedValueForSave(PropertyValueBase $propertyValue)
	{
		$value = $propertyValue->getField('VALUE');

		if ($this->getType() == 'FILE')
		{
			$value = Input\File::asMultiple($value);

			foreach ($value as $i => $file)
			{
				if (Input\File::isDeletedSingle($file))
				{
					unset($value[$i]);
				}
				else
				{
					if (Input\File::isUploadedSingle($file))
					{
						$fileId = \CFile::SaveFile(array('MODULE_ID' => 'sale') + $file, 'sale/order/properties');
						if (is_numeric($fileId))
						{
							$file = $fileId;
						}
					}

					$value[$i] = Input\File::loadInfoSingle($file);
				}
			}

			$property = $this->getFields();
			$propertyValue->setField('VALUE', $value);
			$value = Input\File::getValue($property, $value);

			$originalFields = $propertyValue->getFields()->getOriginalValues();
			foreach (
				array_diff(
					Input\File::asMultiple(Input\File::getValue($property, $originalFields['VALUE'])),
					Input\File::asMultiple($value),
					Input\File::asMultiple(Input\File::getValue($property, $property['DEFAULT_VALUE']))
				)
				as $fileId
			)
			{
				\CFile::Delete($fileId);
			}
		}
		elseif ($this->getType() == 'ADDRESS'  && Main\Loader::includeModule('location'))
		{
			if (is_array($value))
			{
				$address = Address::fromArray($value);

				$result = $address->save();
				if (!$result->isSuccess())
				{
					return null;
				}

				return (int)$result->getId();
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @return string
	 * @throws Main\SystemException
	 */
	public function getViewHtml($value)
	{
		return Input\Manager::getViewHtml($this->fields, $value);
	}

	/**
	 * @param array $values
	 * @return string
	 * @throws Main\SystemException
	 */
	public function getEditHtml(array $values)
	{
		$key = isset($this->property["ID"]) ? $this->getId() : "n".$values['ORDER_PROPS_ID'];
		return Input\Manager::getEditHtml("PROPERTIES[".$key."]", $this->fields, $values['VALUE']);
	}

	/**
	 * @return mixed
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->getField('ID');
	}

	/**
	 * @return mixed
	 */
	public function getPersonTypeId()
	{
		return $this->getField('PERSON_TYPE_ID');
	}

	/**
	 * @return mixed
	 */
	public function getGroupId()
	{
		return $this->getField('PROPS_GROUP_ID');
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->getField('NAME');
	}

	/**
	 * @return mixed
	 */
	public function getRelations()
	{
		return $this->getField('RELATION');
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->getField('DESCRIPTION');
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->getField('TYPE');
	}

	/**
	 * @return bool
	 */
	public function isRequired()
	{
		return $this->getField('REQUIRED') === 'Y';
	}

	/**
	 * @return bool
	 */
	public function isUtil()
	{
		return $this->getField('UTIL') === 'Y';
	}

	/**
	 * @return mixed
	 */
	public function getOptions()
	{
		return $this->getField('OPTIONS');
	}

	/**
	 * @param $value
	 */
	public function onValueDelete($value)
	{
		if ($this->getType() === 'FILE')
		{
			foreach (Input\File::asMultiple($value) as $fileId)
			{
				\CFile::Delete($fileId);
			}
		}
	}
}
