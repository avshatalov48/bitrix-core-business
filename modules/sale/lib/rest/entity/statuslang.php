<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class StatusLang extends Base
{
	public function getFields()
	{
		return [
			'STATUS_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable,
				]
			],
			'LID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable,
				]
			],
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'DESCRIPTION'=>[
				'TYPE'=>self::TYPE_STRING
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
		if($name == 'getlistlangs'){}
		elseif($name == 'deletebyfilter')
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

	public function externalizeResult($name, $fields)
	{
		if($name == 'getlistlangs'){}
		else
		{
			parent::externalizeResult($name, $fields);
		}
		return $fields;
	}
}