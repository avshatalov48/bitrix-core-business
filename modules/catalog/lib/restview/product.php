<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Product extends Base
{
	/**
	 * @return array
	 * return fields all type product
	 */
	public function getFields()
	{
		return array_merge($this->getFieldsIBlockElement(), $this->getFieldsCatalogProduct());
	}

	protected function prepareFieldAttributs($info, $attributs)
	{
		$r = parent::prepareFieldAttributs($info, $attributs);

		if($info['TYPE'] == DataType::TYPE_PRODUCT_PROPERTY)
		{
			$r['IS_DYNAMIC'] = true;
			$r['IS_MULTIPLE'] = in_array(Attributes::MULTIPLE, $attributs, true);
			$r['PROPERTY_TYPE'] = $info['PROPERTY_TYPE'];
			$r['USER_TYPE'] = $info['USER_TYPE'];
			$r['NAME'] = $info['NAME'];
			if(isset($info['VALUES']))
			{
				$r['VALUES'] = $info['VALUES'];
			}
		}
		return $r;
	}

	private function getFieldsIBlockElement()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'CREATED_BY'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'DATE_CREATE'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
			],
			'MODIFIED_BY'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'TIMESTAMP_X'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'ACTIVE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'DATE_ACTIVE_FROM'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
			],
			'DATE_ACTIVE_TO'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'CODE'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'PREVIEW_TEXT'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'PREVIEW_TEXT_TYPE'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'PREVIEW_PICTURE'=>[
				'TYPE'=>DataType::TYPE_FILE,
			],
			'DETAIL_TEXT'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'DETAIL_TEXT_TYPE'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'DETAIL_PICTURE'=>[
				'TYPE'=>DataType::TYPE_FILE,
			],
			'IBLOCK_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'IBLOCK_SECTION_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
		];
	}
	private function getFieldsIBlockPropertyValuesByFilter($filter)
	{
		$result = new Result();
		$fieldsInfo = [];

		if(!isset($filter['IBLOCK_ID']) || intval($filter['IBLOCK_ID'])<=0)
		{
			$result->addError(new Error('paramentr - iblockId is empty'));
		}

		if($result->isSuccess())
		{
			$res = \CIBlockProperty::GetList(
				array('SORT' => 'ASC', 'ID' => 'ASC'),
				[
					'IBLOCK_ID' => $filter['IBLOCK_ID'],
					'CHECK_PERMISSIONS' => 'N',
					//'!PROPERTY_TYPE' => 'G'
				]
			);
			while ($property = $res->Fetch())
			{
				if(isset($property['USER_TYPE']))
				{
					if(!in_array($property['PROPERTY_TYPE'].':'.$property['USER_TYPE'], self::getUserType()))
						continue;
				}

				$info = array(
					'TYPE' => DataType::TYPE_PRODUCT_PROPERTY,
					'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
					'USER_TYPE' => $property['USER_TYPE'],
					'ATTRIBUTES' => array(Attributes::DYNAMIC),
					'NAME' => $property['NAME']
				);

				$isMultuple = isset($property['MULTIPLE']) && $property['MULTIPLE'] === 'Y';
				$isRequired = isset($property['IS_REQUIRED']) && $property['IS_REQUIRED'] === 'Y';
				if($isMultuple || $isRequired)
				{
					if($isMultuple)
						$info['ATTRIBUTES'][] = Attributes::MULTIPLE;
					if($isRequired)
						$info['ATTRIBUTES'][] = Attributes::REQUIRED;
				}

				if ($property['PROPERTY_TYPE'] === 'L')
				{
					$values = array();
					$enum = \CIBlockProperty::GetPropertyEnum($property['ID'], array('SORT' => 'ASC','ID' => 'ASC'));
					while($enumValue = $enum->Fetch())
					{
						$values[intval($enumValue['ID'])] = array(
							'ID' => $enumValue['ID'],
							'VALUE' => $enumValue['VALUE']
						);
					}
					$info['VALUES'] = $values;
				}

				$fieldsInfo['PROPERTY_'.$property['ID']] = $info;
			}

			$result->setData($fieldsInfo);
		}

		return $result;
	}
	private function getFieldsCatalogProductCommonFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'TIMESTAMP_X'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
			],
			'PRICE_TYPE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			/*'RECUR_SCHEME_LENGTH'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'RECUR_SCHEME_TYPE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'TRIAL_PRICE_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'WITHOUT_ORDER'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'SELECT_BEST_PRICE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],

			'TMP_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],*/

			'TYPE'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'AVAILABLE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'BUNDLE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			]
		];
	}
	private function getFieldsCatalogProductByFilter($filter)
	{
		$result = new Result();

		if(!isset($filter['IBLOCK_ID']) || intval($filter['IBLOCK_ID'])<=0)
		{
			$result->addError(new Error('paramentr - iblockId is empty'));
		}

		if(!isset($filter['PRODUCT_TYPE']) || intval($filter['PRODUCT_TYPE'])<=0)
		{
			$result->addError(new Error('parametr - productType is empty'));
		}

		if($result->isSuccess())
		{
			$iblockId = (int)$filter['IBLOCK_ID'];
			$productTypeId = (int)$filter['PRODUCT_TYPE'];

			$iblockData = \CCatalogSku::GetInfoByIBlock($iblockId);
			if (empty($iblockData))
			{
				$result->addError(new Error('iblock is not catalog'));
			}
			else
			{
				$allowedTypes = self::getProductTypes($iblockData['CATALOG_TYPE']);

				if (!isset($allowedTypes[$productTypeId]))
				{
					$result->addError(new Error('productType is not allowed for this catalog'));
				}
				else
				{
					$result->setData($this->getFieldsCatalogProductByType($productTypeId));
				}
			}
		}

		return $result;
	}
	private function getFieldsCatalogProduct()
	{
		return [
			'TYPE'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'AVAILABLE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'BUNDLE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'QUANTITY'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'QUANTITY_RESERVED'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'QUANTITY_TRACE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'CAN_BUY_ZERO'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'SUBSCRIBE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'VAT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'VAT_INCLUDED'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'PURCHASING_PRICE'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'PURCHASING_CURRENCY'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'BARCODE_MULTI'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'WEIGHT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'LENGTH'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'WIDTH'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'HEIGHT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'MEASURE'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'RECUR_SCHEME_LENGTH'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'RECUR_SCHEME_TYPE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'TRIAL_PRICE_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'WITHOUT_ORDER'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			//QUANTITY_TRACE_RAW
			//PAYMENT_TYPE
			//SUBSCRIBE_RAW
			//CAN_BUY_ZERO_RAW
		];
	}

	private function getFieldsCatalogProductByType($id)
	{
		$r = [];
		switch ($id)
		{
			case ProductTable::TYPE_PRODUCT:
				$r = $this->getFieldsCatalogProductByTypeProduct();
				break;
			case ProductTable::TYPE_SET:
				$r = $this->getFieldsCatalogProductByTypeSet();
				break;
			case ProductTable::TYPE_SKU:
			case ProductTable::TYPE_EMPTY_SKU:
				$r = $this->getFieldsCatalogProductByTypeSKU();
				break;
			case ProductTable::TYPE_OFFER:
			case ProductTable::TYPE_FREE_OFFER:
				$r = $this->getFieldsCatalogProductByTypeOffer();
				break;
		}
		return $r;
	}
	private function getFieldsCatalogProductByTypeProduct()
	{
		return [
			'PURCHASING_PRICE'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'PURCHASING_CURRENCY'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'VAT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'VAT_INCLUDED'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'QUANTITY'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'QUANTITY_RESERVED'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'MEASURE'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'QUANTITY_TRACE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'CAN_BUY_ZERO'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'NEGATIVE_AMOUNT_TRACE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'SUBSCRIBE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'WEIGHT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'LENGTH'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'WIDTH'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'HEIGHT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],

		];
	}
	private function getFieldsCatalogProductByTypeSKU()
	{
		return [];
	}
	private function getFieldsCatalogProductByTypeOffer()
	{
		return $this->getFieldsCatalogProductByTypeProduct();
	}
	private function getFieldsCatalogProductByTypeSet()
	{
		return [
			'PURCHASING_PRICE'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'PURCHASING_CURRENCY'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'VAT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
			],
			'VAT_INCLUDED'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'QUANTITY'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'MEASURE'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'QUANTITY_TRACE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'CAN_BUY_ZERO'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'NEGATIVE_AMOUNT_TRACE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'SUBSCRIBE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			],
			'WEIGHT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'LENGTH'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'WIDTH'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
			'HEIGHT'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
			],
		];
	}

	public function getFieldsByFilter(array $filter)
	{
		$result = new Result();

		$filter['IBLOCK_ID'];
		if(!isset($filter['IBLOCK_ID']) || intval($filter['IBLOCK_ID'])<=0)
		{
			$result->addError(new Error('paramentr - iblockId is empty'));
		}

		if(!isset($filter['PRODUCT_TYPE']) || intval($filter['PRODUCT_TYPE'])<=0)
		{
			$result->addError(new Error('parametr - productType is empty'));
		}

		if($result->isSuccess())
		{
			$iblockId = (int)$filter['IBLOCK_ID'];
			$productTypeId = (int)$filter['PRODUCT_TYPE'];

			$iblockData = \CCatalogSku::GetInfoByIBlock($iblockId);
			if (empty($iblockData))
			{
				$result->addError(new Error('iblock is not catalog'));
			}
			else
			{
				$allowedTypes = self::getProductTypes($iblockData['CATALOG_TYPE']);

				if (!isset($allowedTypes[$productTypeId]))
				{
					$result->addError(new Error('productType is not allowed for this catalog'));
				}
				else
				{
					$result->setData(
						array_merge(
							$this->getFieldsIBlockElement(),
							$this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID'=>$iblockId])->getData(),
							$this->getFieldsCatalogProductCommonFields(),
							$this->getFieldsCatalogProductByType($productTypeId))
					);
				}
			}
		}

		return $result;
	}

	private static function getProductTypes($catalogType)
	{
		//TODO: remove after create \Bitrix\Catalog\Model\CatalogIblock

		$result = array();

		switch ($catalogType)
		{
			case \CCatalogSku::TYPE_CATALOG:
				$result = array(
					ProductTable::TYPE_PRODUCT => true,
					ProductTable::TYPE_SET => true
				);
				break;
			case \CCatalogSku::TYPE_OFFERS:
				$result = array(
					ProductTable::TYPE_OFFER => true,
					ProductTable::TYPE_FREE_OFFER => true
				);
				break;
			case \CCatalogSku::TYPE_FULL:
				$result = array(
					ProductTable::TYPE_PRODUCT => true,
					ProductTable::TYPE_SET => true,
					ProductTable::TYPE_SKU => true,
					ProductTable::TYPE_EMPTY_SKU => true
				);
				break;
			case \CCatalogSku::TYPE_PRODUCT:
				$result = array(
					ProductTable::TYPE_SKU => true,
					ProductTable::TYPE_EMPTY_SKU => true
				);
				break;
		}

		return $result;
	}

	private static function getUserType()
	{
		return [
			'S:Date',
			'S:DateTime',
			'S:HTML',
			'E:EList',
			'N:Sequence',
			'S:Money',
			'S:map_yandex',
			'S:map_google',
			'S:employee',
			'S:ECrm',
			'E:SKU',
			'S:ElementXmlID',
			//TODO: support types
			//'S:video',
			//'S:UserID',
			//'G:SectionAuto',
			//'S:TopicID',
			//'S:FileMan',
			//'E:EAutocomplete',
			//'S:DiskFile',
		];
	}

	public function internalizeFieldsList($arguments, $fieldsInfo = [])
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = $arguments['filter']['IBLOCK_ID'];

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID'=>$iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess()? $propertyValues->getData():[])
		);

		return parent::internalizeFieldsList($arguments, $fieldsInfo);
	}

	public function internalizeFieldsAdd($fields, $fieldsInfo = [])
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = $fields['IBLOCK_ID'];

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID'=>$iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess()? $propertyValues->getData():[])
		);

		return parent::internalizeFieldsAdd($fields, $fieldsInfo);
	}

	public function internalizeFieldsUpdate($fields, $fieldsInfo = [])
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = $fields['IBLOCK_ID'];

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID'=>$iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess()? $propertyValues->getData():[])
		);

		return parent::internalizeFieldsUpdate($fields, $fieldsInfo);
	}

	protected function internalizeDateValue($value)
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDate($value);

		if($date instanceof Date)
		{
			$value = $date->format('d.m.Y');
		}
		else
		{
			$r->addError(new Error('Wrong type data'));
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateTimeValue($value)
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDateTime($value);
		if($date instanceof DateTime)
		{
			$value = $date->format('d.m.Y H:i:s');
		}
		else
		{
			$r->addError(new Error('Wrong type datetime'));
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateProductPropertyValue($value)
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDate($value);

		if($date instanceof Date)
		{
			$value = $date->format('Y-m-d');
		}
		else
		{
			$r->addError(new Error('Wrong type data'));
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateTimeProductPropertyValue($value)
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDateTime($value);
		if($date instanceof DateTime)
		{
			$value = $date->format('Y-m-d H:i:s');
		}
		else
		{
			$r->addError(new Error('Wrong type datetime'));
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeExtendedTypeValue($value, $info)
	{
		$r = new Result();

		$type = isset($info['TYPE']) ? $info['TYPE']:'';

		if ($type === DataType::TYPE_PRODUCT_PROPERTY)
		{
			$propertyType = isset($info['PROPERTY_TYPE']) ? $info['PROPERTY_TYPE'] : '';
			$userType = isset($info['USER_TYPE']) ? $info['USER_TYPE'] : '';

			$attrs = isset($info['ATTRIBUTES']) ? $info['ATTRIBUTES'] : array();
			$isMultiple = in_array(Attributes::REQUIRED, $attrs, true);

			$value = $isMultiple? $value: [$value];

			if($propertyType === 'S' && $userType === 'Date')
			{
				array_walk($value, function(&$item) use ($r)
				{
					$date = $this->internalizeDateProductPropertyValue($item['VALUE']);
					if($date->isSuccess())
					{
						$item['VALUE'] = $date->getData()[0];
					}
					else
					{
						$r->addErrors($date->getErrors());
					}
				});
			}
			elseif($propertyType === 'S' && $userType === 'DateTime')
			{
				array_walk($value, function(&$item) use ($r)
				{
					$date = $this->internalizeDateTimeProductPropertyValue($item['VALUE']);
					if($date->isSuccess())
					{
						$item['VALUE'] = $date->getData()[0];
					}
					else
					{
						$r->addErrors($date->getErrors());
					}
				});
			}
			elseif($propertyType === 'F' && empty($userType))
			{
				array_walk($value, function(&$item) use ($r)
				{
					$date = $this->internalizeFileValue($item['VALUE']);
					if(count($date)>0)
					{
						$item['VALUE'] = $date;
					}
					else
					{
						$r->addError(new Error('Wrong file date'));
					}
				});
			}
			//elseif($propertyType === 'S' && $userType === 'HTML'){}

			$value = $isMultiple? $value: $value[0];
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	public function internalizeArguments($name, $arguments)
	{
		if($name == 'getfieldsbyfilter'
			|| $name == 'download'
		){}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	public function externalizeFieldsGet($fields, $fieldsInfo=[])
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = (int)$fields['IBLOCK_ID'];
		$productType = (int)$fields['TYPE'];

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID'=>$iblockId]);
		$product = $this->getFieldsCatalogProductByFilter(['IBLOCK_ID'=>$iblockId, 'PRODUCT_TYPE'=>$productType]);

		if($product->isSuccess())
		{
			$fieldsInfo = array_merge(
				$this->getFieldsIBlockElement(),
				($propertyValues->isSuccess()? $propertyValues->getData():[]),
				$this->getFieldsCatalogProductCommonFields(),
				$product->getData()
			);
		}
		else
		{
			// if it was not possible to determine the view fields by product type,
			// we get the default fields, all fields of the catalog and fields of the Information Block

			$fieldsInfo = array_merge(
				$this->getFields(),
				($propertyValues->isSuccess()? $propertyValues->getData():[])
			);
		}

		return parent::externalizeFieldsGet($fields, $fieldsInfo);
	}

	public function externalizeListFields($list, $fieldsInfo=[])
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = (int)$list[0]['IBLOCK_ID'];

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID'=>$iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess()? $propertyValues->getData():[])
		);

		$r = parent::externalizeListFields($list, $fieldsInfo);
/*
		array_walk($r, function(&$item){
			foreach($item as $name=>$value)
			{
				if(preg_match('/^PROPERTY_\d+$/', $name))
				{
					$item['PROPERTIES'][$name] = $value;
					unset($item[$name]);
				}
			}
		});*/

		return $r;
	}

	public function externalizeResult($name, $fields)
	{
		if($name == 'getfieldsbyfilter'
			|| $name == 'download'
		){}
		else
		{
			parent::externalizeResult($name, $fields);
		}

		return $fields;
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if($name == 'getfieldsbyfilter')
		{
			if(isset($arguments['filter']))
			{
				$filter = $arguments['filter'];
				if(!empty($filter))
					$arguments['filter'] = $this->convertKeysToSnakeCaseFilter($filter);
			}
		}
		elseif($name == 'download')
		{
			if(isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if(!empty($fields))
				{
					$converter = new Converter(Converter::VALUES | Converter::TO_SNAKE | Converter::TO_SNAKE_DIGIT | Converter::TO_UPPER);
					$converterForKey = new Converter(Converter::KEYS | Converter::TO_SNAKE | Converter::TO_SNAKE_DIGIT | Converter::TO_UPPER);

					$result=[];
					foreach ($converter->process($fields) as $key=>$value)
					{
						$result[$converterForKey->process($key)] = $value;
					}
					$arguments['fields'] = $result;
				}
			}
		}
		else
		{
			parent::convertKeysToSnakeCaseArguments($name, $arguments);
		}

		return $arguments;
	}

	public function checkFieldsList($arguments)
	{
		$r = new Result();

		$error=[];
		if(!in_array('ID', $arguments['select']))
			$error[] = 	'id';
		if(!in_array('IBLOCK_ID', $arguments['select']))
			$error[] = 	'iblockId';

		if(count($error)>0)
			$r->addError(new Error('Required select fields: '.implode(', ', $error)));

		if(!isset($arguments['filter']['IBLOCK_ID']))
			$r->addError(new Error('Required filter fields: iblockId'));

		return $r;
	}

	public function checkArguments($name, $arguments)
	{
		if($name == 'download')
		{
			$fields = $arguments['fields'];
			return $this->checkFieldsDownload($fields);
		}
		else
		{
			return parent::checkArguments($name, $arguments);
		}
	}

	protected function checkFieldsDownload($fields)
	{
		$r = new Result();

		$emptyFields = [];

		if(!isset($fields['FIELD_NAME']))
			$emptyFields[] = 'fieldName';

		if(!isset($fields['FILE_ID']))
			$emptyFields[] = 'fileId';

		if(!isset($fields['PRODUCT_ID']))
			$emptyFields[] = 'productId';

		if(count($emptyFields)>0)
		{
			$r->addError(new Error('Required fields: '.implode(', ', $emptyFields)));
		}

		return $r;
	}

	protected function getActionUriToDownload()
	{
		return '/rest/catalog.product.download';
	}

	protected function externalizeFileValue($name, $value, $fields)
	{
		$productId = $fields['ID'];

		$data = [
			'fields'=>[
				'fieldName' => Converter::toJson()
					->process($name),
				'fileId' => $value,
				'productId' => $productId
			]
		];

		$uri = new \Bitrix\Main\Web\Uri($this->getActionUriToDownload());

		return [
			'ID'=>$value,
			'URL'=>new \Bitrix\Main\Engine\Response\DataType\ContentUri(
				$uri->addParams($data)
					->__toString()
			)
		];
	}

	protected function externalizeExtendedTypeValue($name, $value, $fields, $fieldsInfo)
	{
		$r = new Result();

		$info = isset($fieldsInfo[$name]) ? $fieldsInfo[$name]:[];
		$type = isset($info['TYPE']) ? $info['TYPE']:'';

		if($type === DataType::TYPE_PRODUCT_PROPERTY)
		{
			$attrs = isset($info['ATTRIBUTES']) ? $info['ATTRIBUTES'] : array();
			$isMultiple = in_array(Attributes::MULTIPLE, $attrs, true);

			$propertyType = isset($info['PROPERTY_TYPE']) ? $info['PROPERTY_TYPE'] : '';
			$userType = isset($info['USER_TYPE']) ? $info['USER_TYPE'] : '';

			$value = $isMultiple? $value: [$value];

			if($propertyType === 'S' && $userType === 'Date')
			{
				array_walk($value, function(&$item)use($r)
				{
					$date = $this->externalizeDateValue($item['VALUE']);
					if($date->isSuccess())
					{
						$item['VALUE'] = $date->getData()[0];
					}
					else
					{
						$r->addErrors($date->getErrors());
					}
				});
			}
			elseif($propertyType === 'S' && $userType === 'DateTime')
			{
				array_walk($value, function(&$item) use($r)
				{
					$date = $this->externalizeDateTimeValue($item['VALUE']);
					if($date->isSuccess())
					{
						$item['VALUE'] = $date->getData()[0];
					}
					else
					{
						$r->addErrors($date->getErrors());
					}
				});
			}
			elseif($propertyType === 'F' && empty($userType))
			{
				array_walk($value, function(&$item) use ($fields, $name)
				{
					$item['VALUE'] = $this->externalizeFileValue($name, $item['VALUE'], ['PRODUCT_ID'=>$fields['ID']]);
				});
			}

			$value = $isMultiple? $value: $value[0];
		}

		if($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}
}