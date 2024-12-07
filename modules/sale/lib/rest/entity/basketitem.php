<?php

namespace Bitrix\Sale\Rest\Entity;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Sale\Helpers\Order\Builder\BasketBuilderRest;
use Bitrix\Sale\Rest\Attributes;
use Bitrix\Sale\Result;

class BasketItem extends Base
{
	private bool $bitrix24Included;
	private bool $catalogIncluded;

	public function getFields()
	{
		$this->checkModules();

		return $this->getFieldsInfoItem() + $this->getCustomProductFieldsInfo();
	}

	public function getFieldsCatalogProduct()
	{
		$this->checkModules();

		return $this->getFieldsInfoItem() + $this->getFieldsInfoCatalogProduct();
	}

	private function getFieldsInfoItem(): array
	{
		$result = [];
		$result['ID'] = [
			'TYPE' => self::TYPE_INT,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['XML_ID'] = [
			'TYPE' => self::TYPE_STRING,
		];
		$result['ORDER_ID'] = [
			'TYPE' => self::TYPE_INT,
			'ATTRIBUTES' => [
				Attributes::Immutable,
				Attributes::Required,
			],
		];

		if (!$this->bitrix24Included)
		{
			$result['FUSER_ID'] = [
				'TYPE' => self::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
			$result['LID'] = [
				'TYPE' => self::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
		}

		$result['SORT'] = [
			'TYPE' => self::TYPE_INT,
		];
		$result['PRODUCT_ID'] = [
			'TYPE' => self::TYPE_INT,
			'ATTRIBUTES' => [
				Attributes::Immutable,
				Attributes::Required,
			],
		];
		$result['PRICE'] = [
			'TYPE' => self::TYPE_FLOAT,
		];
		$result['CURRENCY'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::Immutable,
				Attributes::Required,
			],
		];
		$result['CUSTOM_PRICE'] = [
			'TYPE' => self::TYPE_CHAR,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['QUANTITY'] = [
			'TYPE' => self::TYPE_FLOAT,
			'ATTRIBUTES' => [
				Attributes::Required,
			],
		];
		$result['DATE_INSERT'] = [
			'TYPE' => self::TYPE_DATETIME,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['DATE_UPDATE'] = [
			'TYPE' => self::TYPE_DATETIME,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['PROPERTIES'] = [
			'TYPE' => self::TYPE_LIST,
			'ATTRIBUTES' => [
				Attributes::Hidden,
			],
		];

		return $result;
	}

	private function getFieldsInfoCatalogProduct(): array
	{
		$result['NAME'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];

		if (!$this->bitrix24Included)
		{
			$result['PRODUCT_PRICE_ID'] = [
				'TYPE' => self::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
			$result['PRICE_TYPE_ID'] = [
				'TYPE' => self::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
			$result['DETAIL_PAGE_URL'] = [
				'TYPE' => self::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
		}

		$result['BASE_PRICE'] = [
			'TYPE' => self::TYPE_FLOAT,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['DISCOUNT_PRICE'] = [
			'TYPE' => self::TYPE_FLOAT,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['WEIGHT'] = [
			'TYPE' => self::TYPE_FLOAT,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['DIMENSIONS'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['MEASURE_CODE'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['MEASURE_NAME'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];

		$result['CAN_BUY'] = [
			'TYPE' => self::TYPE_CHAR,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];

		$result['VAT_RATE'] = [
			'TYPE' => self::TYPE_FLOAT,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['VAT_INCLUDED'] = [
			'TYPE' => self::TYPE_CHAR,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];

		if (
			$this->catalogIncluded
			&& \Bitrix\Catalog\Config\State::isUsedInventoryManagement()
		)
		{
			$result['BARCODE_MULTI'] = [
				'TYPE' => self::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
		}

		$result['TYPE'] = [
			'TYPE' => self::TYPE_INT,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];

		if (!$this->bitrix24Included)
		{
			$result['PRODUCT_PROVIDER_CLASS'] = [
				'TYPE' => self::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
			$result['MODULE'] = [
				'TYPE' => self::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
			$result['SET_PARENT_ID'] = [
				'TYPE' => self::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
		}

		$result['CATALOG_XML_ID'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];
		$result['PRODUCT_XML_ID'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::ReadOnly,
			],
		];

		return $result;
	}

	private function getCustomProductFieldsInfo(): array
	{
		$result = [];
		$result['NAME'] = [
			'TYPE' => self::TYPE_STRING,
		];

		if (!$this->bitrix24Included)
		{
			$result['PRODUCT_PRICE_ID'] = [
				'TYPE' => self::TYPE_INT,
			];
			$result['PRICE_TYPE_ID'] = [
				'TYPE' => self::TYPE_INT,
			];
			$result['DETAIL_PAGE_URL'] = [
				'TYPE' => self::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			];
		}

		$result['BASE_PRICE'] = [
			'TYPE' => self::TYPE_FLOAT,
		];
		$result['DISCOUNT_PRICE'] = [
			'TYPE' => self::TYPE_FLOAT,
		];
		$result['WEIGHT'] = [
			'TYPE' => self::TYPE_FLOAT,
		];
		$result['DIMENSIONS'] = [
			'TYPE' => self::TYPE_STRING,
		];
		$result['MEASURE_CODE'] = [
			'TYPE' => self::TYPE_STRING,
		];
		$result['MEASURE_NAME'] = [
			'TYPE' => self::TYPE_STRING,
		];
		$result['CAN_BUY'] = [
			'TYPE' => self::TYPE_CHAR,
		];
		$result['VAT_RATE'] = [
			'TYPE' => self::TYPE_FLOAT,
		];
		$result['VAT_INCLUDED'] = [
			'TYPE' => self::TYPE_CHAR,
		];

		if (
			$this->catalogIncluded
			&& \Bitrix\Catalog\Config\State::isUsedInventoryManagement()
		)
		{
			$result['BARCODE_MULTI'] = [
				'TYPE' => self::TYPE_CHAR,
			];
		}
		$result['TYPE'] = [
			'TYPE' => self::TYPE_INT,
		];

		if (!$this->bitrix24Included)
		{
			$result['PRODUCT_PROVIDER_CLASS'] = [
				'TYPE' => self::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::Immutable,
				],
			];
			$result['MODULE'] = [
				'TYPE' => self::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::Immutable,
				],
			];
			$result['SET_PARENT_ID'] = [
				'TYPE' => self::TYPE_INT,
			];
		}

		$result['CATALOG_XML_ID'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::Immutable,
			],
		];
		$result['PRODUCT_XML_ID'] = [
			'TYPE' => self::TYPE_STRING,
			'ATTRIBUTES' => [
				Attributes::Immutable,
			],
		];

		return $result;
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if ($name === 'getfieldscatalogproduct')
		{
			return $arguments;
		}

		if (
			$name === 'addcatalogproduct'
			|| $name === 'updatecatalogproduct'
			|| $name === 'modifycatalogproduct')
		{
			if (isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if (!empty($fields))
				{
					$arguments['fields'] = $this->convertKeysToSnakeCaseFields($fields);
				}
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

		if ($name === 'getfieldscatalogproduct')
		{
			return $r;
		}

		if ($name === 'addcatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo(
				$this->getFieldsCatalogProduct(),
				[
					'filter' => [
						'ignoredAttributes' => [
							Attributes::Hidden,
							Attributes::ReadOnly,
						],
					],
				]
			);

			if (!empty($fields))
			{
				$required = $this->checkRequiredFields($fields, $fieldsInfo);
				if (!$required->isSuccess())
				{
					$r->addError(new Error(
						'Required fields: ' . implode(', ', $required->getErrorMessages())
					));
				}
			}
		}
		elseif ($name === 'updatecatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo(
				$this->getFieldsCatalogProduct(),
				[
					'filter' => [
						'ignoredAttributes' => [
							Attributes::Hidden,
							Attributes::ReadOnly,
							Attributes::Immutable,
						],
					],
				],
			);

			if (!empty($fields))
			{
				$required = $this->checkRequiredFields($fields, $fieldsInfo);
				if (!$required->isSuccess())
				{
					$r->addError(new Error(
						'Required fields: ' . implode(', ', $required->getErrorMessages())
					));
				}
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
		if (
			$name === 'canbuy'
			|| $name === 'getbaseprice'
			|| $name === 'getbasepricewithvat'
			|| $name === 'getcurrency'
			|| $name === 'getdefaultprice'
			|| $name === 'getdiscountprice'
			|| $name === 'getfinalprice'
			|| $name === 'getinitialprice'
			|| $name === 'getprice'
			|| $name === 'getpricewithvat'
			|| $name === 'getproductid'
			|| $name === 'getquantity'
			|| $name === 'getreservedquantity'
			|| $name === 'getvat'
			|| $name === 'getvatrate'
			|| $name === 'getweight'
			|| $name === 'isbarcodemulti'
			|| $name === 'iscustommulti'
			|| $name === 'iscustomprice'
			|| $name === 'isdelay'
			|| $name === 'isvatinprice'
			|| $name === 'getfieldscatalogproduct'
			|| $name === 'modifycatalogproduct'
		)
		{
			return $arguments;
		}

		if ($name === 'addcatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo(
				$this->getFieldsCatalogProduct(),
				[
					'filter' => [
						'ignoredAttributes' => [
							Attributes::Hidden,
							Attributes::ReadOnly,
						],
					],
				]
			);

			if (!empty($fields))
			{
				$arguments['fields'] = $this->internalizeFields($fields, $fieldsInfo);
			}
		}
		elseif ($name === 'updatecatalogproduct')
		{
			$fields = $arguments['fields'];
			$fieldsInfo = $this->getListFieldInfo(
				$this->getFieldsCatalogProduct(),
				[
					'filter' => [
						'ignoredAttributes' => [
							Attributes::Hidden,
							Attributes::ReadOnly,
							Attributes::Immutable,
						],
					],
				]
			);

			if (!empty($fields))
			{
				$arguments['fields'] = $this->internalizeFields($fields, $fieldsInfo);
			}
		}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	public function externalizeResult($name, $fields)
	{
		if ($name === 'getfieldscatalogproduct')
		{
			return $fields;
		}

		if (
			$name === 'addcatalogproduct'
			|| $name === 'updatecatalogproduct'
		)
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
		if (!isset($fields['ID']))
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

		$fieldsInfo = empty($fieldsInfo) ? $this->getFields() : $fieldsInfo;
		$listFieldsInfoAdd = $this->getListFieldInfo(
			$fieldsInfo,
			[
				'filter' => [
					'ignoredAttributes' => [
						Attributes::Hidden,
						Attributes::ReadOnly,
					],
					'ignoredFields' => [
						'ORDER_ID',
					],
				],
			]
		);
		$listFieldsInfoUpdate = $this->getListFieldInfo(
			$fieldsInfo,
			[
				'filter' => [
					'ignoredAttributes' => [
						Attributes::Hidden,
						Attributes::ReadOnly,
						Attributes::Immutable,
					],
					'skipFields' => [
						'ID',
					],
				],
			]
		);

		if (isset($fields['ORDER']['ID']))
		{
			$result['ORDER']['ID'] = (int)$fields['ORDER']['ID'];
		}

		if (isset($fields['ORDER']['BASKET_ITEMS']))
		{
			foreach ($fields['ORDER']['BASKET_ITEMS'] as $k => $item)
			{
				$result['ORDER']['BASKET_ITEMS'][$k] = $this->internalizeFields($item,
					$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
				);

				// n1 - ref shipmentItem.basketId
				if ($this->isNewItem($item) && isset($item['ID']))
				{
					$result['ORDER']['BASKET_ITEMS'][$k]['ID'] = $item['ID'];
				}

				if (isset($item['PROPERTIES']))
				{
					$result['ORDER']['BASKET_ITEMS'][$k]['PROPERTIES'] = $basketProperties->internalizeFieldsModify(['BASKET_ITEM' => ['PROPERTIES'=>$item['PROPERTIES']]])['BASKET_ITEM']['PROPERTIES'];
				}
			}
		}

		return $result;
	}

	public function externalizeFields($fields)
	{
		$basketProperties = new BasketProperties();
		$basketReservation = new BasketReservation();

		$result = parent::externalizeFields($fields);

		if (isset($fields['PROPERTIES']))
		{
			$result['PROPERTIES'] = $basketProperties->externalizeListFields($fields['PROPERTIES']);
		}

		if (isset($fields['RESERVATIONS']))
		{
			$result['RESERVATIONS'] = $basketReservation->externalizeListFields($fields['RESERVATIONS']);
		}

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
		if (!isset($fields['ORDER']['ID']))
		{
			$emptyFields[] = '[order][id]';
		}
		if (!isset($fields['ORDER']['BASKET_ITEMS']) || !is_array($fields['ORDER']['BASKET_ITEMS']))
		{
			$emptyFields[] = '[order][basketItems][]';
		}

		if (!empty($emptyFields))
		{
			$r->addError(new Error('Required fields: ' . implode(', ', $emptyFields)));
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

		$listFieldsInfoAdd = $this->getListFieldInfo(
			$this->getFields(),
			[
				'filter' => [
					'ignoredAttributes' => [
						Attributes::Hidden,
						Attributes::ReadOnly,
					],
					'ignoredFields' => [
						'ORDER_ID',
					],
				],
			]
		);
		$listFieldsInfoUpdate = $this->getListFieldInfo(
			$this->getFields(),
			[
				'filter' => [
					'ignoredAttributes' => [
						Attributes::Hidden,
						Attributes::ReadOnly,
						Attributes::Immutable,
					],
				],
			]
		);

		foreach ($fields['ORDER']['BASKET_ITEMS'] as $k => $item)
		{
			$required = $this->checkRequiredFields($item,
				$this->isNewItem($item)? $listFieldsInfoAdd:$listFieldsInfoUpdate
			);
			if (!$required->isSuccess())
			{
				$r->addError(new Error(
					'[basketItems]['.$k.'] - ' . implode(', ', $required->getErrorMessages()) . '.'
				));
			}

			if (isset($item['PROPERTIES']))
			{
				$requiredProperties = $basketProperties->checkRequiredFieldsModify([
					'BASKET_ITEM' => [
						'PROPERTIES' => $item['PROPERTIES'],
					],
				]);
				if (!$requiredProperties->isSuccess())
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

	private function checkModules(): void
	{
		if (!isset($this->bitrix24Included))
		{
			$this->bitrix24Included = Loader::includeModule('bitrix24');
		}
		if (!isset($this->catalogIncluded))
		{
			$this->catalogIncluded = Loader::includeModule('catalog');
		}
	}
}
