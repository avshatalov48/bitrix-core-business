<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Rest\Attributes;

class Basket extends Base
{
	public function getFields()
	{
		return [
			'ORDER_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PRODUCT_PROVIDER_CLASS'=>[
				'TYPE'=>self::TYPE_STRING,
			],
			'MODULE'=>[
				'TYPE'=>self::TYPE_STRING,
			],
			'TYPE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'SET_PARENT_ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'LID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'SORT'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PRODUCT_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PRODUCT_PRICE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'PRICE_TYPE_ID'=>[
				'TYPE'=>self::TYPE_INT
			],
			'CATALOG_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'PRODUCT_XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DETAIL_PAGE_URL'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'BASE_PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'DISCOUNT_PRICE'=>[
				'TYPE'=>self::TYPE_FLOAT
			],
			'CURRENCY'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CUSTOM_PRICE'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'QUANTITY'=>[
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
			'NOTES'=>[
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
			'SUBSCRIBE'=>[
				'TYPE'=>self::TYPE_CHAR
			],
			'DISCOUNT_NAME'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DISCOUNT_VALUE'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DISCOUNT_COUPON'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'RECOMMENDATION'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'DATE_INSERT'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'DATE_UPDATE'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'DATE_REFRESH'=>[
				'TYPE'=>self::TYPE_DATETIME
			],
			'PROPERTIES'=>[
				'TYPE'=>self::TYPE_LIST,
				'ATTRIBUTES'=>[Attributes::Hidden]
			]
		];
	}

	public function internalizeFieldsModify($fields)
	{
		$result = [];

		if(isset($fields['ORDER']['ID']))
			$result['ORDER']['ID'] = (int)$fields['ORDER']['ID'];

		if(isset($fields['ORDER']['BASKET']['ITEMS']))
			$result['ORDER']['BASKET']['ITEMS'] = $this->internalizeFieldsCollectionWithExcludeFields($fields['ORDER']['BASKET']['ITEMS'], new \Bitrix\Sale\Rest\Entity\Basket());

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
}