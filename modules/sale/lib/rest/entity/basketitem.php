<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Main\Error;
use Bitrix\Sale\Helpers\Order\Builder\BasketBuilderRest;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

class BasketItem extends Base
{
	public function getFields()
	{
		return $this->getFieldsInfoItem() + $this->getCustomProductFieldsInfo();
	}

	public function getFieldsCatalogProduct()
	{
		return $this->getFieldsInfoItem() + $this->getFieldsInfoCatalogProduct();
	}

	private function getFieldsInfoItem()
	{
		return [
			'ORDER_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Immutable,
					Attributes::Required
				]
			],
			'ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'FUSER_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'LID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'SORT'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PRODUCT_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Immutable,
					Attributes::Required
				]
			],
			'PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'CUSTOM_PRICE'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'CURRENCY'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'QUANTITY'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			/*&&*/'SUBSCRIBE'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			/*&&*/'RECOMMENDATION'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DATE_INSERT'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DATE_UPDATE'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DATE_REFRESH'=>[
				'TYPE'=>self::TYPE_DATETIME,
				'ATTRIBUTES'=>[Attributes::ReadOnly],
			],
			'PROPERTIES'=>[
				'TYPE'=>self::TYPE_LIST,
				'ATTRIBUTES'=>[Attributes::Hidden]
			]
		];
	}

	private function getFieldsInfoCatalogProduct()
	{
		return [
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PRODUCT_PRICE_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PRICE_TYPE_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DETAIL_PAGE_URL'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'BASE_PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DISCOUNT_PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'WEIGHT'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DIMENSIONS'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'MEASURE_CODE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'MEASURE_NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'CAN_BUY'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'NOTES'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'VAT_RATE'=>[
				'TYPE'=>self::TYPE_FLOAT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'VAT_INCLUDED'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'BARCODE_MULTI'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'TYPE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'SET_PARENT_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DISCOUNT_NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DISCOUNT_VALUE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'DISCOUNT_COUPON'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'CATALOG_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PRODUCT_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PRODUCT_PROVIDER_CLASS'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'MODULE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
		];
	}

	private function getCustomProductFieldsInfo()
	{
		return [
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PRODUCT_PRICE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PRICE_TYPE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'DETAIL_PAGE_URL'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'BASE_PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'DISCOUNT_PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'WEIGHT'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'DIMENSIONS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'MEASURE_CODE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'MEASURE_NAME'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CAN_BUY'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			/*??*/'NOTES'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'VAT_RATE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'VAT_INCLUDED'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'BARCODE_MULTI'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'TYPE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'SET_PARENT_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			/*??*/'DISCOUNT_NAME'=>[
				'TYPE'=>self::TYPE_STRING
			],
			/*??*/'DISCOUNT_VALUE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			/*??*/'DISCOUNT_COUPON'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CATALOG_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Immutable]
			],
			'PRODUCT_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Immutable]
			],
			'PRODUCT_PROVIDER_CLASS'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Immutable]
			],
			'MODULE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Immutable]
			],
		];
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if($name == 'getfieldscatalogproduct'){}
		elseif ($name == 'addcatalogproduct'
			|| $name == 'updatecatalogproduct'
			|| $name == 'modifycatalogproduct')
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

	public function checkArguments($name, $arguments)
	{
		$r = new Result();

		if($name == 'getfieldscatalogproduct'){}
		elseif($name == 'addcatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo($this->getFieldsCatalogProduct(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]);

			if(!empty($fields))
			{
				$required = $this->checkRequiredFields($fields, $fieldsInfo);
				if(!$required->isSuccess())
					$r->addError(new Error('Required fields: '.implode(', ', $required->getErrorMessages())));
			}
		}
		elseif($name == 'updatecatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo($this->getFieldsCatalogProduct(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

			if(!empty($fields))
			{
				$required = $this->checkRequiredFields($fields, $fieldsInfo);
				if(!$required->isSuccess())
					$r->addError(new Error('Required fields: '.implode(', ', $required->getErrorMessages())));
			}
		}
		else
		{
			$r = parent::checkArguments($name, $arguments);
		}

		return $r;
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name == 'canbuy'
			|| $name == 'getbaseprice'
			|| $name == 'getbasepricewithvat'
			|| $name == 'getcurrency'
			|| $name == 'getdefaultprice'
			|| $name == 'getdiscountprice'
			|| $name == 'getfinalprice'
			|| $name == 'getinitialprice'
			|| $name == 'getprice'
			|| $name == 'getpricewithvat'
			|| $name == 'getproductid'
			|| $name == 'getquantity'
			|| $name == 'getreservedquantity'
			|| $name == 'getvat'
			|| $name == 'getvatrate'
			|| $name == 'getweight'
			|| $name == 'isbarcodemulti'
			|| $name == 'iscustommulti'
			|| $name == 'iscustomprice'
			|| $name == 'isdelay'
			|| $name == 'isvatinprice'
			|| $name == 'getfieldscatalogproduct'
		){}
		elseif($name == 'addcatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo($this->getFieldsCatalogProduct(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly]]]);

			if(!empty($fields))
			{
				$arguments['fields'] = $this->internalizeFields($fields, $fieldsInfo);
			}
		}
		elseif($name == 'updatecatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo($this->getFieldsCatalogProduct(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

			if(!empty($fields))
			{
				$arguments['fields'] = $this->internalizeFields($fields, $fieldsInfo);
			}
		}
		elseif($name == 'modifycatalogproduct'){}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	public function externalizeResult($name, $fields)
	{
		if($name == 'getfieldscatalogproduct'){}
		elseif($name == 'addcatalogproduct'
			|| $name == 'updatecatalogproduct')
		{
			$fields = $this->externalizeFields($fields);
		}
		else
		{
			parent::externalizeResult($name, $fields);
		}

		return $fields;
	}

	protected function isNewItem($fields)
	{
		if(isset($fields['ID']) === false)
		{
			return true;
		}
		else
		{
			return BasketBuilderRest::isBasketItemNew($fields['ID']);
		}
	}

	public function internalizeFieldsModify($fields, $fieldsInfo=[])
	{
		$result = [];
		$basketProperties = new BasketProperties();

		$fieldsInfo = empty($fieldsInfo)? $this->getFields():$fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($fieldsInfo, ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable], 'skipFields'=>['ID']]]);

		if(isset($fields['ORDER']['ID']))
			$result['ORDER']['ID'] = (int)$fields['ORDER']['ID'];

		if(isset($fields['ORDER']['BASKET_ITEMS']))
		{
			foreach ($fields['ORDER']['BASKET_ITEMS'] as $k=>$item)
			{
				$result['ORDER']['BASKET_ITEMS'][$k] = $this->internalizeFields($item,
					$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
				);

				// n1 - ref shipmentItem.basketId
				if($this->isNewItem($item) && isset($item['ID']))
					$result['ORDER']['BASKET_ITEMS'][$k]['ID'] = $item['ID'];

				if(isset($item['PROPERTIES']))
				{
					$result['ORDER']['BASKET_ITEMS'][$k]['PROPERTIES'] = $basketProperties->internalizeFieldsModify(['BASKET_ITEM'=>['PROPERTIES'=>$item['PROPERTIES']]])['BASKET_ITEM']['PROPERTIES'];
				}
			}
		}

		return $result;
	}

	public function externalizeFields($fields)
	{
		$basketProperties = new \Bitrix\Sale\Rest\Entity\BasketProperties();

		$result = parent::externalizeFields($fields);

		if(isset($fields['PROPERTIES']))
			$result['PROPERTIES'] = $basketProperties->externalizeListFields($fields['PROPERTIES']);

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
		if(!isset($fields['ORDER']['BASKET_ITEMS']) || !is_array($fields['ORDER']['BASKET_ITEMS']))
		{
			$emptyFields[] = '[order][basketItems][]';
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

		$basketProperties = new BasketProperties();

		$listFieldsInfoAdd = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly], 'ignoredFields'=>['ORDER_ID']]]);
		$listFieldsInfoUpdate = $this->getListFieldInfo($this->getFields(), ['filter'=>['ignoredAttributes'=>[Attributes::Hidden, Attributes::ReadOnly, Attributes::Immutable]]]);

		foreach ($fields['ORDER']['BASKET_ITEMS'] as $k=>$item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
			if(!$required->isSuccess())
			{
				$r->addError(new Error('[basketItems]['.$k.'] - '.implode(', ', $required->getErrorMessages()).'.'));
			}

			if(isset($item['PROPERTIES']))
			{
				$requiredProperties = $basketProperties->checkRequiredFieldsModify(['BASKET_ITEM'=>['PROPERTIES'=>$item['PROPERTIES']]]);
				if(!$requiredProperties->isSuccess())
				{
					$requiredPropertiesFields = [];
					foreach ($requiredProperties->getErrorMessages() as $errorMessage)
					{
						$requiredPropertiesFields[] = '[basketItems]['.$k.']'.$errorMessage;
					}
					$r->addError(new Error(implode( ' ', $requiredPropertiesFields)));
				}

			}
		}
		return $r;
	}
}