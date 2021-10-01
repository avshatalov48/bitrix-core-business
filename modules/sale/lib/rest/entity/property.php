<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Sale\Rest\Attributes;

class Property extends Base
{
	const PROPERTY_TYPE_STRING = 'STRING';
	const PROPERTY_TYPE_YN = 'Y/N';
	const PROPERTY_TYPE_NUMBER = 'NUMBER';
	const PROPERTY_TYPE_ENUM = 'ENUM';
	const PROPERTY_TYPE_FILE = 'FILE';
	const PROPERTY_TYPE_DATE = 'DATE';
	const PROPERTY_TYPE_LOCATION = 'LOCATION';
	const PROPERTY_TYPE_ADDRESS = 'ADDRESS';

	public function prepareFieldInfos($fields)
	{
		$fieldsInfo = parent::prepareFieldInfos($fields);

		foreach($fields as $name => $info)
		{
			if($name == 'SETTINGS')
			{
				$fieldsInfo[$name]['FIELDS'] = parent::prepareFieldInfos($info['FIELDS']);
			}
		}
		return $fieldsInfo;
	}

	public function getFields()
	{
		return array_merge(
			[
				'ID'=>[
					'TYPE'=>self::TYPE_INT,
					'ATTRIBUTES'=>[Attributes::ReadOnly]
				],
				'PERSON_TYPE_ID'=>[
					'TYPE'=>self::TYPE_INT,
					'ATTRIBUTES'=>[
						Attributes::Required,
						Attributes::Immutable
					]
				],
				'PROPS_GROUP_ID'=>[
					'TYPE'=>self::TYPE_INT,
					'ATTRIBUTES'=>[
						Attributes::Required,
						Attributes::Immutable
					]
				],
				'NAME'=>[
					'TYPE'=>self::TYPE_STRING,
					'ATTRIBUTES'=>[Attributes::Required]
				],
				'CODE'=>[
					'TYPE'=>self::TYPE_STRING
				],
				'ACTIVE'=>[
					'TYPE'=>self::TYPE_CHAR
				],
				'UTIL' =>[
					'TYPE'=>self::TYPE_CHAR
				],
				'USER_PROPS'=>[
					'TYPE'=>self::TYPE_CHAR
				],
				'IS_FILTERED'=>[
					'TYPE'=>self::TYPE_CHAR
				],
				'SORT'=>[
					'TYPE'=>self::TYPE_INT
				],
				'DESCRIPTION'=>[
					'TYPE'=>self::TYPE_STRING
				],
				'XML_ID'=>[
					'TYPE'=>self::TYPE_STRING
				],
				'TYPE'=>[
					'TYPE'=>self::TYPE_STRING,
					'ATTRIBUTES'=>[
						Attributes::Required,
						Attributes::Immutable
					]
				],
				'REQUIRED'=>[
					'TYPE'=>self::TYPE_CHAR
				],
				'MULTIPLE'=>[
					'TYPE'=>self::TYPE_CHAR
				],
				'DEFAULT_VALUE'=>[
					'TYPE'=>self::TYPE_STRING
				],
				'SETTINGS'=>[
					'TYPE'=>self::TYPE_DATATYPE,
					//'ATTRIBUTES'=>[Attributes::ReadOnly]
				],
			],
			$this->getFieldsByTypeString(),
			$this->getFieldsByTypeLocation(),
			$this->getFieldsByTypeAddress()
		);
	}

	public function getFieldsByType($type)
	{
		$filterMap = [
			self::PROPERTY_TYPE_STRING => function ($k)
			{
				return (
					is_set($this->getFieldsByTypeLocation(), $k) === false
					&& is_set($this->getFieldsByTypeAddress(), $k) === false
				);
			},
			self::PROPERTY_TYPE_LOCATION => function ($k)
			{
				return (
					is_set($this->getFieldsByTypeString(), $k) === false
					&& is_set($this->getFieldsByTypeAddress(), $k) === false
				);
			},
			self::PROPERTY_TYPE_ADDRESS => function ($k)
			{
				return (
					is_set($this->getFieldsByTypeString(), $k) === false
					&& is_set($this->getFieldsByTypeLocation(), $k) === false
				);
			},
			'DEFAULT' => function ($k)
			{
				return (
					is_set($this->getFieldsByTypeString(), $k) === false
					&& is_set($this->getFieldsByTypeLocation(), $k) === false
					&& is_set($this->getFieldsByTypeAddress(), $k) === false
				);
			},
		];

		$filter = isset($filterMap[$type]) ? $filterMap[$type] : $filterMap['DEFAULT'];

		$r = array_filter($this->getFields(), $filter, ARRAY_FILTER_USE_KEY);

		$r['SETTINGS']['FIELDS'] = $this->getFieldsSettingsByType($type);

		return $r;
	}

	protected function getFieldsSettingsByType($type)
	{
		$r = [];

		if($type == self::PROPERTY_TYPE_STRING)
		{
			$r = $this->getFieldsSettingsByTypeString();
		}
		elseif($type == self::PROPERTY_TYPE_YN)
		{
			$r = $this->getFieldsSettingsByEitherYNType();
		}
		elseif($type == self::PROPERTY_TYPE_NUMBER)
		{
			$r = $this->getFieldsSettingsByNumberType();
		}
		elseif($type == self::PROPERTY_TYPE_ENUM)
		{
			$r = $this->getFieldsSettingsByEnumType();
		}
		elseif($type == self::PROPERTY_TYPE_FILE)
		{
			$r = $this->getFieldsSettingsByFileType();
		}
		elseif($type == self::PROPERTY_TYPE_DATE)
		{
			$r = $this->getFieldsSettingsByDateType();
		}
		elseif($type == self::PROPERTY_TYPE_LOCATION)
		{
			$r = $this->getFieldsSettingsByLocation();
		}

		return $r;
	}

	protected function getFieldsByTypeString()
	{
		return [
			'IS_PROFILE_NAME'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'IS_PAYER'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'IS_EMAIL'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'IS_PHONE'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'IS_ZIP'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'IS_ADDRESS'=>[
				'TYPE'=>self::TYPE_CHAR
			],
		];
	}

	protected function getFieldsByTypeAddress()
	{
		return [
			'IS_ADDRESS_FROM'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'IS_ADDRESS_TO'=>[
				'TYPE'=>self::TYPE_CHAR
			],
		];
	}

	protected function getFieldsByTypeLocation()
	{
		$r = [
			'IS_LOCATION'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'INPUT_FIELD_LOCATION'=>[
				'TYPE'=>self::TYPE_STRING // enum
			],
			'IS_LOCATION4TAX'=>[
				'TYPE'=>self::TYPE_CHAR
			]
		];
		return $r;
	}

	protected function getFieldsSettingsByTypeString()
	{
		return [
			'MINLENGTH'=>[
				'TYPE'=>self::TYPE_INT,
			],
			'MAXLENGTH'=>[
				'TYPE'=>self::TYPE_INT,
			],
			'PATTERN'=>[
				'TYPE'=>self::TYPE_STRING,
			],
			'MULTILINE'=>[
				'TYPE'=>self::TYPE_CHAR,
			],
			'COLS'=>[
				'TYPE'=>self::TYPE_INT,
			],
			'ROWS'=>[
				'TYPE'=>self::TYPE_INT,
			],
			'SIZE'=>[
				'TYPE'=>self::TYPE_INT,
			],
		];
	}

	protected function getFieldsSettingsByEitherYNType()
	{
		return [];
	}

	protected function getFieldsSettingsByNumberType()
	{
		return [
			'MIN'=>[
				'TYPE'=>self::TYPE_INT,
			],
			'MAX'=>[
				'TYPE'=>self::TYPE_INT,
			],
			'STEP'=>[
				'TYPE'=>self::TYPE_INT,
			]
		];
	}

	protected function getFieldsSettingsByEnumType()
	{
		return [
			'MULTIELEMENT'=>[
				'TYPE'=>self::TYPE_CHAR,
			],
			'SIZE'=>[
				'TYPE'=>self::TYPE_INT,
			]
		];
	}

	protected function getFieldsSettingsByFileType()
	{
		return [
			'MAXSIZE'=>[
				'TYPE'=>self::TYPE_INT,
			],
			'ACCEPT'=>[
				'TYPE'=>self::TYPE_STRING,
			]
		];
	}

	protected function getFieldsSettingsByDateType()
	{
		return [
			'TIME'=>[
				'TYPE'=>self::TYPE_CHAR,
			]
		];
	}

	protected function getFieldsSettingsByLocation()
	{
		return [];
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if($name == 'getfieldssettingsbytype'
			|| $name == 'getfieldsbytype'
		){
			if(isset($arguments['type']))
			{
				$fields = $arguments['type'];
				if(!empty($fields))
				{
					$converter = new Converter(Converter::VALUES | Converter::TO_UPPER);
					$arguments['type'] = $converter->process($fields);
				}
			}
		}

		return $arguments;
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name == 'getfieldssettingsbytype'
			|| $name == 'getfieldsbytype'
		){}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	public function externalizeResult($name, $fields)
	{
		if($name == 'getfieldssettingsbytype'
			|| $name == 'getfieldsbytype'
		){}
		else
		{
			parent::externalizeResult($name, $fields);
		}
		return $fields;
	}
}