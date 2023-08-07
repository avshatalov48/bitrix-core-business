<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Error;
use Bitrix\Sale\Internals\ShipmentItemTable;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

class ShipmentItem extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ORDER_DELIVERY_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'BASKET_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'QUANTITY'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DATE_INSERT'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'RESERVED_QUANTITY'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'STORES'=>[
				'TYPE'=>self::TYPE_LIST,
				'ATTRIBUTES'=>[Attributes::Hidden]
			]
		];
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable], 'skipFields'=>['ID']]]);

		foreach ($fields['SHIPMENT']['SHIPMENT_ITEMS'] as $k=>$item)
		{
			$result['SHIPMENT']['SHIPMENT_ITEMS'][$k] = $this->internalizeFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
		}

		return $result;
	}

	public function checkFieldsModify($fields)
	{
		$r = new Result();

		$emptyFields = [];

		if(!isset($fields['SHIPMENT']['ID']))
		{
			$emptyFields[] = '[shipment][id]';
		}
		if(!isset($fields['SHIPMENT']['SHIPMENT_ITEMS']) || !is_array($fields['SHIPMENT']['SHIPMENT_ITEMS']))
		{
			$emptyFields[] = '[shipment][shipmentItems][]';
		}

		if(count($emptyFields)>0)
		{
			$r->addError(new Error(implode(', ', $emptyFields)));
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

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_DELIVERY_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

		foreach ($fields['SHIPMENT']['SHIPMENT_ITEMS'] as $k=>$item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
			if(!$required->isSuccess())
			{
				$r->addError(new Error('[shipmentItems]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
			}
		}

		return $r;
	}
}