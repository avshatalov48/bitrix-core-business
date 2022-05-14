<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class PropertyValue extends Base
{
	public function getFields()
	{
		return [
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'CODE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'ORDER_PROPS_XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY, Attributes::IMMUTABLE] //id is always new
			],
			'ORDER_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'VALUE'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'ORDER_PROPS_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED]
			]
		];
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[]): array
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY, Attributes::IMMUTABLE]]]);

		if(isset($fields['ORDER']['ID']))
			$result['ORDER']['ID'] = (int)$fields['ORDER']['ID'];

		if(isset($fields['ORDER']['PROPERTY_VALUES']))
		{
			foreach ($fields['ORDER']['PROPERTY_VALUES'] as $k=>$item)
			{
				$result['ORDER']['PROPERTY_VALUES'][$k] = $this->internalizeFields($item,
					$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
				);
			}
		}

		return $result;
	}

	protected function getRewriteFields(): array
	{
		return [
			'ORDER_PROPS_XML_ID'=>[
				'REFERENCE_FIELD'=>'ORDER_PROPS.XML_ID'
			]
		];
	}

	public function externalizeFieldsModify($fields)
	{
		return $this->externalizeListFields($fields);
	}

	public function checkFieldsModify($fields): Result
	{
		$r = new Result();

		$emptyFields = [];
		if(!isset($fields['ORDER']['ID']))
		{
			$emptyFields[] = '[order][id]';
		}
		if(!isset($fields['ORDER']['PROPERTY_VALUES']) || !is_array($fields['ORDER']['PROPERTY_VALUES']))
		{
			$emptyFields[] = '[order][propertyValues][]';
		}

		if(count($emptyFields)>0)
		{
			$r->addError(new Error('Required fields: '.implode(', ', $emptyFields)));
		}
		else
		{
			$r = parent::checkFieldsModify($fields);
		}

		return $r;
	}

	public function checkRequiredFieldsModify($fields): Result
	{
		$r = new Result();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::HIDDEN, Attributes::READONLY, Attributes::IMMUTABLE]]]);

		foreach ($fields['ORDER']['PROPERTY_VALUES'] as $k=>$item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);

			if(!$required->isSuccess())
			{
				$r->addError(new Error('[propertyValues]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
			}
		}
		return $r;
	}
}