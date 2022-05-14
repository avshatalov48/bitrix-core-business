<?php


namespace Bitrix\Sale\Exchange\Integration\RestView;


use Bitrix\Main\Error;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Result;

final class Statistic extends Base
{

	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'ENTITY_TYPE_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'ENTITY_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'DATE_UPDATE'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'TIMESTAMP_X'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'PROVIDER_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'CURRENCY'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'STATUS'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'AMOUNT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			]
		];
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if($name == 'upsert')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
				{
					$arguments['fields'] = $this->convertKeysToSnakeCaseFields($fields);
				}
			}
		}
		elseif ($name == 'modify')
		{
			$fields = $arguments['fields'];
			$arguments['fields'] = $this->convertKeysToSnakeCaseFieldsModify($fields);
		}
		else
		{
			throw new NotImplementedException('Convert arguments. The method '.$name.' is not implemented.');
		}

		return $arguments;
	}

	protected function convertKeysToSnakeCaseFieldsModify($fields)
	{
		$fields = $this->convertKeysToSnakeCaseFields($fields);

		return $fields;
	}

	public function checkArguments($name, $arguments): \Bitrix\Main\Result
	{
		$r = new Result();

		if($name == 'upsert')
		{
			$r = $this->checkFieldsAdd($arguments['fields']);
		}
		elseif ($name == 'modify')
		{
			$required = $this->checkFieldsModify($arguments['fields']);

			if($required->isSuccess() === false)
			{
				$r->addError(new Error(implode(', ', $required->getErrorMessages())));
			}
		}
		else
		{
			throw new NotImplementedException('Check arguments. The method '.$name.' is not implemented.');
		}
		return $r;
	}

	protected function checkFieldsModify($fields)
	{
		$r = new Result();

		$emptyFields = [];
		if(!isset($fields['PROVIDER']['ID']))
		{
			$emptyFields[] = '[provider][id]';
		}
		if(!isset($fields['STATISTICS']) || !is_array($fields['STATISTICS']))
		{
			$emptyFields[] = '[statistics][]';
		}

		if(count($emptyFields)>0)
		{
			$r->addError(new Error('Required fields: '.implode(', ', $emptyFields)));
		}
		else
		{
			foreach ($fields['STATISTICS'] as $k=>$fields)
			{
				$required = $this->checkFieldsAdd($fields);
				if($required->isSuccess() === false)
				{
					$r->addError(new Error('[fields][statistics]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
				}
			}
		}

		return $r;
	}

	public function internalizeArguments($name, $arguments): array
	{
		if($name == 'upsert')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
			{
				$arguments['fields'] = $this->internalizeFieldsAdd($fields);
			}
		}
		elseif ($name == 'modify')
		{
			$fields = $arguments['fields'];
			$arguments['fields'] = $this->internalizeFieldsModify($fields);
		}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	protected function internalizeFieldsModify($fields)
	{
		$result = [];

		$result['PROVIDER']['ID'] = (int)$fields['PROVIDER']['ID'];

		foreach ($fields['STATISTICS'] as $k=>$statistic)
		{
			$result['STATISTICS'][$k] = $this->internalizeFieldsAdd($statistic);
		}

		return $result;
	}

	public function externalizeResult($name, $fields): array
	{
		if($name == 'upsert')
		{
			return $this->externalizeFieldsGet($fields);
		}
		elseif ($name == 'modify')
		{
			$provider = new StatisticProvider();
			$fields['PROVIDER'] = $this->externalizeFieldsGet($fields['PROVIDER'], $provider->getFields());
		}
		else
		{
			parent::externalizeResult($name, $fields);
		}

		return $fields;
	}
}