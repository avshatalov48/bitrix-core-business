<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class StatusLang extends Base
{
	public function getFields()
	{
		return [
			'STATUS_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE,
				]
			],
			'LID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE,
				]
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'DESCRIPTION'=>[
				'TYPE'=>DataType::TYPE_STRING
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

	public function checkArguments($name, $arguments): Result
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

	public function internalizeArguments($name, $arguments): array
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

	public function externalizeResult($name, $fields): array
	{
		if($name == 'getlistlangs'){}
		else
		{
			parent::externalizeResult($name, $fields);
		}
		return $fields;
	}
}