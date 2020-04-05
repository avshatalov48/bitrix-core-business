<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Error;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

class PropertyValue extends Base
{
	public function getFields()
	{
		return [
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CODE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'ORDER_PROPS_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly, Attributes::Immutable] //особенность работы с значениями свойств. id всегда новый.
			],
			'ORDER_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'VALUE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'ORDER_PROPS_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					//Attributes::Immutable
				]
			]
		];
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

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

	protected function getRewritedFields()
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

	public function checkFieldsModify($fields)
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

	public function checkRequiredFieldsModify($fields)
	{
		$r = new Result();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

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