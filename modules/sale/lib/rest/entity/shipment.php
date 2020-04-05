<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Error;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

class Shipment extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ORDER_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Immutable,
					Attributes::Required
				]
			],
			'STATUS_ID'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'BASE_PRICE_DELIVERY'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'PRICE_DELIVERY'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'ALLOW_DELIVERY'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::Required]//for builder
			],
			'DATE_ALLOW_DELIVERY'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_ALLOW_DELIVERY_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'DEDUCTED'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::Required]//for builder
			],
			'DATE_DEDUCTED'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_DEDUCTED_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'REASON_UNDO_DEDUCTED'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DELIVERY_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::Required]//for builder
			],
			'DELIVERY_DOC_NUM'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DELIVERY_DOC_DATE'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'TRACKING_NUMBER'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DELIVERY_NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'COMPANY_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'MARKED'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'DATE_MARKED'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_MARKED_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'REASON_MARKED'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CANCELED'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'DATE_CANCELED'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_CANCELED_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'RESPONSIBLE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'DATE_RESPONSIBLE_ID'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_RESPONSIBLE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'COMMENTS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CURRENCY'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CUSTOM_PRICE_DELIVERY'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'UPDATED_1C'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'EXTERNAL_DELIVERY'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'VERSION_1C'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'ID_1C'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'TRACKING_STATUS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'TRACKING_LAST_CHECK'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'TRACKING_DESCRIPTION'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DISCOUNT_PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DATE_INSERT'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'SYSTEM'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'STATUS_XML_ID'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DELIVERY_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ACCOUNT_NUMBER'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'SHIPMENT_ITEMS'=>[
				'TYPE'=>self::TYPE_LIST,
				'ATTRIBUTES'=>[Attributes::Hidden]
			],
		];
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];
		$shipmentItem = new ShipmentItem();

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable], 'skipFields'=>['ID']]]);

		if(isset($fields['ORDER']['ID']))
			$result['ORDER']['ID'] = (int)$fields['ORDER']['ID'];

		if(isset($fields['ORDER']['SHIPMENTS']))
		{
			foreach ($fields['ORDER']['SHIPMENTS'] as $k=>$item)
			{
				$result['ORDER']['SHIPMENTS'][$k] = $this->internalizeFields($item,
					$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
				);

				if(isset($item['SHIPMENT_ITEMS']))
				{
					$result['ORDER']['SHIPMENTS'][$k]['SHIPMENT_ITEMS'] = $shipmentItem->internalizeFieldsModify(['SHIPMENT'=>['SHIPMENT_ITEMS'=>$item['SHIPMENT_ITEMS']]])['SHIPMENT']['SHIPMENT_ITEMS'];
				}
			}
		}

		return $result;
	}

	protected function getRewritedFields()
	{
		return [
			'STATUS_XML_ID'=>[
				'REFERENCE_FIELD'=>'STATUS_TABLE.XML_ID'
			],
			'DELIVERY_XML_ID'=>[
				'REFERENCE_FIELD'=>'DELIVERY.XML_ID'
			]
		];
	}

	public function externalizeFields($fields)
	{
		$shipmentItem = new \Bitrix\Sale\Rest\Entity\ShipmentItem();

		$result = parent::externalizeFields($fields);

		if(isset($fields['SHIPMENT_ITEMS']))
			$result['SHIPMENT_ITEMS'] = $shipmentItem->externalizeListFields($fields['SHIPMENT_ITEMS']);

		return $result;
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
		if(!isset($fields['ORDER']['SHIPMENTS']) || !is_array($fields['ORDER']['SHIPMENTS']))
		{
			$emptyFields[] = '[order][shipments][]';
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

		$shipmentItem = new ShipmentItem();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

		foreach ($fields['ORDER']['SHIPMENTS'] as $k=>$item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
			if(!$required->isSuccess())
			{
				$r->addError(new Error('[shipments]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
			}

			if(isset($item['SHIPMENT_ITEMS']))
			{
				$requiredShipmentItems = $shipmentItem->checkRequiredFieldsModify(['SHIPMENT'=>['SHIPMENT_ITEMS'=>$item['SHIPMENT_ITEMS']]]);
				if(!$requiredShipmentItems->isSuccess())
				{
					$requiredPShipmentItemsFields = [];
					foreach ($requiredShipmentItems->getErrorMessages() as $errorMessage)
					{
						$requiredPShipmentItemsFields[] = '[shipments]['.$k.']'.$errorMessage;
					}
					$r->addError(new Error(implode( ' ', $requiredPShipmentItemsFields)));
				}
			}
		}
		return $r;
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name = 'getallowdeliverydate'
			|| $name == 'getallowdeliveryuserid'
			|| $name == 'getcompanyid'
			|| $name == 'getcurrency'
			|| $name == 'getdeliveryid'
			|| $name == 'getdeliveryname'
			|| $name == 'getparentorderid'
			|| $name == 'getpersontypeid'
			|| $name == 'getprice'
			|| $name == 'getphippeddate'
			|| $name == 'getshippeduserId'
			|| $name == 'getstoreid'
			|| $name == 'getunshipreason'
			|| $name == 'getvatrate'
			|| $name == 'getvatsum'
			|| $name == 'getweight'
			|| $name == 'setbasepricedelivery'
		){}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}
}