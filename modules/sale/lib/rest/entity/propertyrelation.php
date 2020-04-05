<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class PropertyRelation extends Base
{
	public function getFields()
	{
		return [
			'PROPERTY_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'ENTITY_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'ENTITY_TYPE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			]
		];
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if($name == 'deletebyfilter')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
					$arguments['fields'] = $this->convertKeysToSnakeCaseFields($fields);
			}
		}
		else
		{
			$arguments =  parent::convertKeysToSnakeCaseArguments($name, $arguments);
		}

		return $arguments;
	}

	public function checkArguments($name, $arguments)
	{
		if($name == 'deletebyfilter')
		{
			$r = $this->checkFieldsAdd($arguments['fields']);
		}
		else
		{
			$r = parent::checkArguments($name, $arguments);
		}

		return $r;
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name == 'deletebyfilter')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $this->internalizeFieldsAdd($fields);
		}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}
}