<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Price extends Base
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
			'PRODUCT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'EXTRA_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'CATALOG_GROUP_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'PRICE'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'CURRENCY'=>[
				'TYPE'=>DataType::TYPE_STRING,
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
			'QUANTITY_FROM'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'QUANTITY_TO'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'PRICE_SCALE'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
		];
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if ($name == 'modify')
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
			$arguments = parent::convertKeysToSnakeCaseArguments($name, $arguments);
		}

		return $arguments;
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name == 'modify')
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

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY], 'ignoredFields'=>['PRODUCT_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY, Attributes::IMMUTABLE], 'skipFields'=>['ID']]]);

		if(isset($fields['PRODUCT']['ID']))
			$result['PRODUCT']['ID'] = (int)$fields['PRODUCT']['ID'];

		if(isset($fields['PRODUCT']['PRICES']))
		{
			foreach ($fields['PRODUCT']['PRICES'] as $k=>$item)
			{
				$result['PRODUCT']['PRICES'][$k] = $this->internalizeFields($item,
					$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
				);
			}
		}

		return $result;
	}

	public function checkArguments($name, $arguments)
	{
		if($name == 'modify')
		{
			$fields = $arguments['fields'];
			return $this->checkFieldsModify($fields);
		}
		else
		{
			return parent::checkArguments($name, $arguments);
		}
	}

	public function checkFieldsModify($fields)
	{
		$r = new Result();

		$emptyFields = [];
		if(!isset($fields['PRODUCT']['ID']))
		{
			$emptyFields[] = '[product][id]';
		}
		if(!isset($fields['PRODUCT']['PRICES']) || !is_array($fields['PRODUCT']['PRICES']))
		{
			$emptyFields[] = '[product][prices][]';
		}

		if(count($emptyFields)>0)
		{
			$r->addError(new Error('Required fields: '.implode(', ', $emptyFields)));
		}
		else
		{
			$required = $this->checkRequiredFieldsModify($fields);
			if(!$required->isSuccess())
				$r->addError(new Error('Required fields: '.implode(' ', $required->getErrorMessages())));
		}

		return $r;
	}

	public function checkRequiredFieldsModify($fields)
	{
		$r = new Result();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY], 'ignoredFields'=>['PRODUCT_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY, Attributes::IMMUTABLE]]]);

		foreach ($fields['PRODUCT']['PRICES'] as $k=>$item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
			if(!$required->isSuccess())
			{
				$r->addError(new Error('[prices]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
			}
		}
		return $r;
	}

	public function externalizeResult($name, $fields)
	{
		if($name == 'modify'
		)
		{
			return $this->externalizeFieldsModify($fields);
		}
		else
		{
			parent::externalizeResult($name, $fields);
		}

		return $fields;
	}

	public function externalizeFieldsModify($fields)
	{
		return $this->externalizeListFields($fields);
	}

	private function isNewItem($fields)
	{
		return (isset($fields['ID']) === false);
	}
}