<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

class BusinessValuePersonDomain extends Base
{
	public function getFields(): array
	{
		return [
			'PERSON_TYPE_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'DOMAIN'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
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