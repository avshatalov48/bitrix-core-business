<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Error;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Rest\View\PropertyValue;
use Bitrix\Sale\Rest\View\TradeBinding;
use Bitrix\Sale\Result;

class Order extends Base
{
	public function getFields()
	{
		return [
			'PERSON_TYPE_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'USER_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Immutable
				]
			],
			'CURRENCY'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
					]
			],
			'LID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable,
					]
			],
			'PERSON_TYPE_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'STATUS_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ACCOUNT_NUMBER'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'CANCELED'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'DATE_CANCELED'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DEDUCTED'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_CANCELED_ID'=>[
					'TYPE'=>self::TYPE_INT
				],
			'REASON_CANCELED'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'STATUS_ID'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'DATE_STATUS'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EMP_STATUS_ID'=>[
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
				/*??*/'TYPE'=>self::TYPE_STRING
			],
			'PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'PAYED'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DISCOUNT_VALUE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'DATE_INSERT'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'DATE_UPDATE'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'USER_DESCRIPTION'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'ADDITIONAL_INFO'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'COMMENTS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'TAX_VALUE'=>[
				/*??*/'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'RECURRING_ID'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'LOCKED_BY'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'DATE_LOCK'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'RECOUNT_FLAG'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'AFFILIATE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'DELIVERY_DOC_NUM'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Hidden]
			],
			'DELIVERY_DOC_DATE'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::Hidden]
			],
			'UPDATED_1C'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'STORE_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::Hidden]
			],
			'ORDER_TOPIC'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'RESPONSIBLE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'DATE_BILL'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::Hidden]
			],
			'DATE_PAY_BEFORE'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::Hidden]
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'ID_1C'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'VERSION_1C'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'VERSION'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'EXTERNAL_ORDER'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'COMPANY_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			//region List fields
			'PAYMENTS'=>['TYPE'=>self::TYPE_LIST, 'ATTRIBUTES'=>[Attributes::Hidden]],
			'SHIPMENTS'=>['TYPE'=>self::TYPE_LIST, 'ATTRIBUTES'=>[Attributes::Hidden]],
			'PROPERTY_VALUES'=>['TYPE'=>self::TYPE_LIST, 'ATTRIBUTES'=>[Attributes::Hidden]],
			'BASKET_ITEMS'=>['TYPE'=>self::TYPE_LIST, 'ATTRIBUTES'=>[Attributes::Hidden]],
			//endregion
		];
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];

		$payment = new Payment();
		$shipment = new Shipment();
		$basketItem = new BasketItem();
		$propertyValue = new PropertyValue();
		$tradeBinding = new TradeBinding();


		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable], 'skipFields'=>['ID']]]);

		$result['ORDER'] = $this->internalizeFields($fields['ORDER'],
			$this->isNewItem($fields['ORDER'])? $listFieldsInfoAdd:$listFieldsInfoUpdate
		);

		if(isset($fields['ORDER']['BASKET_ITEMS']))
		{
			$result['ORDER']['BASKET_ITEMS'] = $basketItem->internalizeFieldsModify($fields)['ORDER']['BASKET_ITEMS'];
		}

		if(isset($fields['ORDER']['PROPERTY_VALUES']))
		{
			$result['ORDER']['PROPERTY_VALUES'] = $propertyValue->internalizeFieldsModify($fields)['ORDER']['PROPERTY_VALUES'];
		}

		if(isset($fields['ORDER']['PAYMENTS']))
		{
			$result['ORDER']['PAYMENTS'] = $payment->internalizeFieldsModify($fields)['ORDER']['PAYMENTS'];
		}

		if(isset($fields['ORDER']['SHIPMENTS']))
		{
			$result['ORDER']['SHIPMENTS'] = $shipment->internalizeFieldsModify($fields)['ORDER']['SHIPMENTS'];
		}

		if(isset($fields['ORDER']['TRADE_BINDINGS']))
		{
			$result['ORDER']['TRADE_BINDINGS'] = $tradeBinding->internalizeFieldsModify($fields)['ORDER']['TRADE_BINDINGS'];
		}

		return $result;
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if($name == 'import')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
					$arguments['fields'] = $this->convertKeysToSnakeCaseFields($fields);
			}
		}
		return $arguments;
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name == 'getdeliveryidlist'
			|| $name == 'getpayments'
			|| $name == 'getpaysystemidlist'
			|| $name == 'getprintedchecks'
			|| $name == 'getshipments'
			|| $name == 'getbasket'
			|| $name == 'getpropertyvalues'
			|| $name == 'getcurrency'
			|| $name == 'getdateinsert'
			|| $name == 'getapplydiscount'
			|| $name == 'getpersontypeid'
			|| $name == 'getprice'
			|| $name == 'getproperties'
			|| $name == 'getsiteid'
			|| $name == 'getsumpaid'
			|| $name == 'gettaxlist'
			|| $name == 'gettaxlocation'
			|| $name == 'gettaxprice'
			|| $name == 'gettaxvalue'
			|| $name == 'getuserid'
			|| $name == 'getvatrate'
			|| $name == 'getvatsum'
			|| $name == 'iscanceled'
			|| $name == 'isexternal'
			|| $name == 'ismarked'
			|| $name == 'ispaid'
			|| $name == 'isshipped'
			|| $name == 'isusedvat'
			|| $name == 'importdelete'
		){}
		elseif($name == 'import')
		{
			$fields = $arguments['fields'];
			if(!empty($fields))
				$arguments['fields'] = $this->internalizeFieldsImport($fields);
		}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	protected function internalizeFieldsImport($fields)
	{
		$result = [];

		$payment = new Payment();
		$shipment = new Shipment();
		$shipmentItem = new ShipmentItem();
		$basketItem = new BasketItem();
		$tradeBinding = new TradeBinding();
		$propertyValue = new PropertyValue();
		$basketProperties = new BasketProperties();


		if(isset($fields['ORDER']))
		{
			$result['ORDER'] = $this->internalizeFields($fields['ORDER'], $this->getFields());//only for importAction PERSON_TYPE_XML_ID, STATUS_XML_ID
		}

		if(isset($fields['ORDER']['BASKET_ITEMS']))
		{
			$result['ORDER']['BASKET_ITEMS'] = $basketItem->internalizeListFields($fields['ORDER']['BASKET_ITEMS']);

			foreach ($fields['ORDER']['BASKET_ITEMS'] as $k=>$items)
			{
				if(isset($items['PROPERTIES']))
				{
					$result['ORDER']['BASKET_ITEMS'][$k]['PROPERTIES'] = $basketProperties->internalizeListFields($items['PROPERTIES']);
				}
			}
		}

		if(isset($fields['ORDER']['PROPERTY_VALUES']))
		{
			$result['ORDER']['PROPERTY_VALUES'] = $propertyValue->internalizeListFields($fields['ORDER']['PROPERTY_VALUES']);//only for importAction ORDER_PROPS_XML_ID
		}

		if(isset($fields['ORDER']['PAYMENTS']))
		{
			$result['ORDER']['PAYMENTS'] = $payment->internalizeListFields($fields['ORDER']['PAYMENTS']);//only for importAction PAY_SYSTEMS_XML_ID
		}

		if(isset($fields['ORDER']['SHIPMENTS']))
		{
			$result['ORDER']['SHIPMENTS'] = $shipment->internalizeListFields($fields['ORDER']['SHIPMENTS']);//only for importAction DELIVERY_XML_ID, STATUS_XML_ID

			foreach ($fields['ORDER']['SHIPMENTS'] as $k=>$items)
			{
				if(isset($items['SHIPMENT_ITEMS']))
				{
					$result['ORDER']['SHIPMENTS'][$k]['SHIPMENT_ITEMS'] = $shipmentItem->internalizeListFields($items['SHIPMENT_ITEMS']);
				}
			}
		}

		if(isset($fields['ORDER']['TRADE_BINDINGS']))
		{
			$result['ORDER']['TRADE_BINDINGS'] = $tradeBinding->internalizeListFields($fields['ORDER']['TRADE_BINDINGS']);//only for importAction TRADING_PLATFORM_XML_ID
		}

		return $result;
	}

	protected function getRewritedFields()
	{
		return [
			'PERSON_TYPE_XML_ID'=>[
				'REFERENCE_FIELD'=>'PERSON_TYPE.XML_ID'
			],
			'STATUS_XML_ID'=>[
				'REFERENCE_FIELD'=>'STATUS_TABLE.XML_ID'
			]
		];
	}

	public function externalizeFields($fields)
	{
		$basketItem = new \Bitrix\Sale\Rest\Entity\BasketItem();
		$payment = new \Bitrix\Sale\Rest\Entity\Payment();
		$shipment = new \Bitrix\Sale\Rest\Entity\Shipment();
		$shipmentItem = new \Bitrix\Sale\Rest\Entity\ShipmentItem();
		$tradeBinding = new TradeBinding();
		$propertyValue = new PropertyValue();
		$basketProperties = new \Bitrix\Sale\Rest\Entity\BasketProperties();

		$result = parent::externalizeFields($fields);

		if(isset($fields['PROPERTY_VALUES']) && count($fields['PROPERTY_VALUES'])>0)
		{
			$result['PROPERTY_VALUES'] = $propertyValue->externalizeListFields($fields['PROPERTY_VALUES']);
		}

		if(isset($fields['BASKET_ITEMS']) && count($fields['BASKET_ITEMS'])>0)
		{
			foreach ($fields['BASKET_ITEMS'] as $k=>$item)
			{
				$result['BASKET_ITEMS'][$k] = $basketItem->externalizeFields($item);
				//$result['BASKET_ITEMS'][$k]['PROPERTIES'] = $basketProperties->externalizeListFields($item['PROPERTIES']);
			}
		}

		if(isset($fields['PAYMENTS']) && count($fields['PAYMENTS'])>0)
		{
			$result['PAYMENTS'] = $payment->externalizeListFields($fields['PAYMENTS']);
		}

		if(isset($fields['SHIPMENTS']) && count($fields['SHIPMENTS'])>0)
		{
			foreach($fields['SHIPMENTS'] as $k=>$item)
			{
				$result['SHIPMENTS'][$k] = $shipment->externalizeFields($item);
				/*if(isset($item['SHIPMENT_ITEMS']))
				{
					$data['SHIPMENTS'][$k]['SHIPMENT_ITEMS'] = $shipmentItem->externalizeListFields($item['SHIPMENT_ITEMS']);
				}*/
			}
		}

		if(isset($fields['TRADE_BINDINGS']) && count($fields['TRADE_BINDINGS'])>0)
			$result['TRADE_BINDINGS'] = $tradeBinding->externalizeListFields($fields['TRADE_BINDINGS']);

		return $result;
	}

	public function externalizeFieldsModify($fields)
	{
		return $this->externalizeFields($fields);
	}

	public function externalizeFieldsTryModify($fields)
	{
		$result = parent::externalizeFieldsTryModify($fields);

		if(isset($fields['PAYMENTS']))
		{
			foreach ($fields['PAYMENTS'] as $k=>$payment)
			{
				if(isset($payment['LIST_PAY_SYSTEM_WITH_RESTRICTIONS']))
				{
					$result['PAYMENTS'][$k]['LIST_PAY_SYSTEM_WITH_RESTRICTIONS'] = $payment['LIST_PAY_SYSTEM_WITH_RESTRICTIONS'];
				}
			}
		}

		if(isset($fields['SHIPMENTS']))
		{
			foreach ($fields['SHIPMENTS'] as $k=>$shipments)
			{
				if(isset($shipments['LIST_DELIIVERY_SERVICES_RESTRICTIONS']))
				{
					$result['SHIPMENTS'][$k]['LIST_DELIIVERY_SERVICES_RESTRICTIONS'] = $shipments['LIST_DELIIVERY_SERVICES_RESTRICTIONS'];
				}
			}
		}

		return $result;
	}

	public function externalizeResult($name, $fields)
	{
		if($name == 'getdeliveryidlist'
			|| $name == 'getpaysystemidlist'
			|| $name == 'getprintedchecks'
		){}
		elseif ($name == 'getbasket')
		{
			$basketItem = new BasketItem();
			$fields = $basketItem->externalizeListFields($fields);
		}
		elseif ($name == 'getpayments')
		{
			$payment = new Payment();
			$fields = $payment->externalizeListFields($fields);
		}
		elseif ($name == 'getshipments')
		{
			$shipment = new Shipment();
			$fields = $shipment->externalizeListFields($fields);
		}
		elseif ($name == 'getpropertyvalues')
		{
			$propertyValue = new PropertyValue();
			$fields = $propertyValue->externalizeListFields($fields);
		}
		elseif($name == 'import')
		{
			$fields = $this->externalizeFieldsModify($fields);
		}
		else
		{
			$fields = parent::externalizeResult($name, $fields);
		}

		return $fields;
	}

	public function checkRequiredFieldsModify($fields)
	{
		$r = new Result();

		$payment = new Payment();
		$shipment = new Shipment();
		$basketItem = new BasketItem();
		$propertyValue = new PropertyValue();
		$tradeBinding = new TradeBinding();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable], 'skipFields'=>['ID']]]);

		$required = $this->checkRequiredFields($fields['ORDER'],
			$this->isNewItem($fields['ORDER'])? $listFieldsInfoAdd:$listFieldsInfoUpdate
		);
		if($required->isSuccess() == false)
		{
			$r->addError(new Error(implode(', ', $required->getErrorMessages()).'.'));
		}

		$required = $propertyValue->checkRequiredFieldsModify($fields);
		if($required->isSuccess() == false)
		{
			$r->addError(new Error(implode(', ', $required->getErrorMessages())));
		}

		if(isset($fields['ORDER']['BASKET_ITEMS']))
		{
			$required = $basketItem->checkRequiredFieldsModify($fields);
			if($required->isSuccess() == false)
			{
				$r->addError(new Error(implode(', ', $required->getErrorMessages())));
			}
		}

		if(isset($fields['ORDER']['PAYMENTS']))
		{
			$required = $payment->checkRequiredFieldsModify($fields);
			if($required->isSuccess() == false)
			{
				$r->addError(new Error(implode(', ', $required->getErrorMessages())));
			}
		}

		if(isset($fields['ORDER']['SHIPMENTS']))
		{
			$required = $shipment->checkRequiredFieldsModify($fields);
			if($required->isSuccess() == false)
			{
				$r->addError(new Error(implode(', ', $required->getErrorMessages())));
			}
		}

		if(isset($fields['ORDER']['TRADE_BINDINGS']))
		{
			$required = $tradeBinding->checkRequiredFieldsModify($fields);
			if($required->isSuccess() == false)
			{
				$r->addError(new Error(implode(', ', $required->getErrorMessages())));
			}
		}

		return $r;
	}
}