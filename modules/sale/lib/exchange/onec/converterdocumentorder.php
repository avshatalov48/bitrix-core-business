<?php
namespace Bitrix\Sale\Exchange\OneC;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\ImportBase;
use Bitrix\Sale\Exchange\ImportOneCBase;
use Bitrix\Sale\Exchange\ISettingsExport;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Sale\Order;

/**
 * Class ConverterDocumentOrder
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentOrder extends Converter
{
	/**
	 * @return array
	 */
	protected function getFieldsInfo()
	{
		return OrderDocument::getFieldsInfo();
	}

	/**
	 * @return int
	 */
	public function getOwnerEntityTypeId()
	{
		return DocumentType::ORDER;
	}

	/**
	 * @param $documentImport
	 * @return array
	 * @throws ArgumentException
	 */
	public function resolveParams($documentImport)
	{
		if(!($documentImport instanceof DocumentBase))
			throw new ArgumentException("Document must be instanceof DocumentBase");

		$result = array();

		$params = $documentImport->getFieldValues();

		$availableFields = Order::getAvailableFields();

		foreach ($availableFields as $k)
		{
			switch($k)
			{
				case 'ID_1C':
				case 'VERSION_1C':
					if(isset($params[$k]))
						$fields[$k] = $params[$k];
					break;
				case 'COMMENTS':
					if(isset($params['COMMENT']))
						$fields[$k] = $params['COMMENT'];
					break;
				case 'CANCELED':
					$value='';
					if(isset($params['CANCELED']))
						$value = $params['CANCELED'];

					if($value == 'Y')
					{
						$fields[$k] = 'Y';
					}
					else

					{
						$v='';
						if(isset($params['REK_VALUES']['CANCEL']))
						{
							$v = $params['REK_VALUES']['CANCEL'];
						}

						if($v == 'Y')
						{
							$fields[$k] = 'Y';
						}
						else
						{
							$fields[$k] = 'N';
						}
					}
					break;
				case 'DATE_INSERT':
					if(isset($params['1C_TIME']) && $params['1C_TIME'] instanceof DateTime)
						$fields[$k] = $params['1C_TIME'];
					break;
				case 'STATUS_ID':
					if(isset($params['REK_VALUES']['1C_STATUS_ID']))
					{
						$settings = $this->getSettings();
						if($settings->changeStatusFor(EntityType::ORDER) == 'Y')
							$fields[$k] = $params['REK_VALUES']['1C_STATUS_ID'];
					}
					break;
				case '1C_PAYED_DATE':
				case '1C_DELIVERY_DATE':
					if(isset($params['REK_VALUES'][$k]))
						$fields[$k] = $params['REK_VALUES'][$k];
					break;
			}
		}

		$result['TRAITS'] = isset($fields)? $fields:array();
		$result['ITEMS'] = isset($params['ITEMS'])? $params['ITEMS']:array();
		$result['TAXES'] = isset($params['TAXES'])? $params['TAXES']:array();

		return $result;
	}

	/**
	 * @param null $order
	 * @param array $fields
	 * @throws ArgumentException
	 */
	public function sanitizeFields($order=null, array &$fields)
	{
		if(!empty($order) && !($order instanceof Order))
			throw new ArgumentException("Entity must be instanceof Order");

		if(empty($order))
		{
			$fields['DATE_STATUS'] = new DateTime() ;
			$fields['DATE_UPDATE'] = new DateTime();
		}
		else
		{
			if(isset($fields['DATE_INSERT']))
				unset($fields['DATE_INSERT']);
		}

		if(isset($fields['ID']))
			unset($fields['ID']);

		if(isset($fields['1C_PAYED_DATE']))
			unset($fields['1C_PAYED_DATE']);

		if(isset($fields['1C_DELIVERY_DATE']))
			unset($fields['1C_DELIVERY_DATE']);
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	public function externalize(array $fields)
	{
		$result = array();

		$traits = $fields['TRAITS'];
		$items = $fields['ITEMS'];
		$taxes = $fields['TAXES'];
		$stories = isset($fields['STORIES']) ? array_unique($fields['STORIES'], SORT_NUMERIC): array();
		$businessValue = $fields['BUSINESS_VALUE'];

		$availableFields = $this->getFieldsInfo();

		/** @var ISettingsExport $settings */
		$settings = $this->getSettings();

		foreach ($availableFields as $k=>$v)
		{
			$value='';
			switch ($k)
			{
				case 'ID':
					$value = $traits['ID'];
					break;
				case 'NUMBER':
					/** TODO: only EntityType::ORDER */
					$value = $settings->prefixFor(EntityType::ORDER).$traits['ACCOUNT_NUMBER'];
					break;
				case 'ID_1C':
					$value = ($traits[$k]<>'' ? $traits[$k]:'');
					break;
				case 'DATE':
					$value = $traits['DATE_INSERT'];
					break;
				case 'OPERATION':
					$value = DocumentBase::resolveDocumentTypeName($this->getOwnerEntityTypeId());
					break;
				case 'ROLE':
					$value = DocumentBase::getLangByCodeField('SELLER');
					break;
				case 'CURRENCY':
					$replaceCurrency = $settings->getReplaceCurrency();
					$value = substr($replaceCurrency<>'' ? $replaceCurrency:$traits[$k], 0, 3);
					break;
				case 'CURRENCY_RATE':
					$value = self::CURRENCY_RATE_DEFAULT;
					break;
				case 'AMOUNT':
					$value = $traits['PRICE'];
					break;
				case 'VERSION':
					$value = $traits['VERSION'];
					break;
				case 'TIME':
					$value = $traits['DATE_INSERT'];
					break;
				case 'COMMENT':
					$value = $traits['COMMENTS'];
					break;
				case 'DISCOUNTS':
					$value = $this->externalizeDiscounts($traits, $v);
					break;
				case 'TAXES':
					if(count($taxes)>0)
						$value = $this->externalizeTaxes($taxes, $v);
					break;
				case 'STORIES':
					if(count($stories)>0)
						$value = $this->externalizeStories(current($stories), $v);
					break;
				case 'ITEMS':
					$value = $this->externalizeItems($items, $v);
					break;
				case 'REK_VALUES':
					$value=array();
					foreach($v['FIELDS'] as $name=>$fieldInfo)
					{
						$valueRV='';
						switch($name)
						{
							case 'DATE_PAID':
								$valueRV = $traits['DATE_PAYED'];
								break;
							case 'PAY_NUMBER':
								$valueRV = $traits['PAY_VOUCHER_NUM'];
								break;
							case 'DATE_ALLOW_DELIVERY_LAST':
								$valueRV = $traits['DATE_ALLOW_DELIVERY'];
								break;
							case 'DELIVERY_SERVICE':
							case 'DELIVERY_ID':
							case 'PAY_SYSTEM':
							case 'PAY_SYSTEM_ID':
							case 'USER_DESCRIPTION':
								$valueRV = $traits[$name];
								break;
							case 'ALLOW_DELIVERY':
								//??????
								break;
							case 'ORDER_PAID':
								$valueRV = $traits['PAYED'];
								break;
							case 'CANCEL':
								$valueRV = $traits['CANCELED'];
								break;
							case 'FINAL_STATUS':
								$valueRV = ($traits['STATUS_ID']=='F'? 'Y':'N');
								break;
							case 'ORDER_STATUS':
								$valueRV = "[".$traits['STATUS_ID']."] ".static::getStatusNameById($traits['STATUS_ID']);
								break;
							case 'ORDER_STATUS_ID':
								$valueRV = $traits['STATUS_ID'];
								break;
							case 'DATE_CANCEL':
								$valueRV = $traits['DATE_CANCELED'];
								break;
							case 'CANCEL_REASON':
								$valueRV = $traits['REASON_CANCELED'];
								break;
							case 'DATE_STATUS':
								$valueRV = $traits['DATE_STATUS'];
								break;
							case 'SITE_NAME':
								$valueRV = '['.$traits['LID'].'] '.static::getSiteNameByLid($traits['LID']);
								break;
							case 'REKV':
								$value = array_merge($value, $this->externalizeRekv($businessValue[$name], $fieldInfo));
								break;
						}
						if(!in_array($name, array('REKV')))
						{
							$value[] = $this->externalizeRekvValue($name, $valueRV, $fieldInfo);
						}
					}
					break;
			}
			if(!in_array($k, array('DISCOUNTS', 'TAXES', 'STORES', 'ITEMS', 'REK_VALUES')))
			{
				$this->externalizeField($value, $v);
			}

			$result[$k] = $value;
		}
		$result = $this->modifyTrim($result);

		return $result;
	}

	/**
	 * @param $items
	 * @param $info
	 * @param array $params
	 * @return array
	 */
	public function externalizeItems(array $items, array $info)
	{
		$result = array();
		foreach ($items as $rowId=>$item)
		{
			foreach($info['FIELDS'] as $name=>$fieldInfo)
			{
				$value='';
				switch ($name)
				{
					case 'ID':
						$value = $item['PRODUCT_XML_ID'];
						break;
					case 'CATALOG_ID':
						$value = $item['CATALOG_XML_ID'];
						break;
					case 'NAME':
						$value = $item['NAME'];
						break;
					case 'PRICE_PER_UNIT':
						$value = $item['PRICE'];
						break;
					case 'QUANTITY':
						$value = $item['QUANTITY'];
						break;
					case 'SUMM':
						$value = $item['PRICE']*$item['QUANTITY'];
						break;
					case 'KOEF':
						$value = self::KOEF_DEFAULT;
						break;
					case 'ITEM_UNIT':
						$code = (intval($item['MEASURE_CODE'])>0 ? $item['MEASURE_CODE']:self::MEASURE_CODE_DEFAULT);
						foreach($fieldInfo['FIELDS'] as $unitFieldName=>$unitFieldInfo)
						{
							$unitValue = '';
							switch ($unitFieldName)
							{
								case 'ITEM_UNIT_CODE':
									$unitValue = $code;
									break;
								case 'ITEM_UNIT_NAME':
									$unitValue = static::getCatalogMeasureByCode($code);
									break;
							}
							$this->externalizeField($unitValue, $unitFieldInfo);
							$value[$unitFieldName] = $unitValue;
						}
						break;
					case 'DISCOUNTS':
						$summ = doubleval($item['DISCOUNT_PRICE']);
						if($summ > 0)
						{
							foreach($fieldInfo['FIELDS'] as $discountFieldName=>$discountFieldInfo)
							{
								$discountValue = '';
								switch ($discountFieldName)
								{
									case 'NAME':
										$discountValue = DocumentBase::getLangByCodeField('ITEM_DISCOUNT');
										break;
									case 'SUMM':
										$discountValue = $item['DISCOUNT_PRICE'];
										break;
									case 'IN_PRICE':
										$discountValue = 'Y';
										break;
								}
								$this->externalizeField($discountValue, $discountFieldInfo);
								$value[$discountFieldName] = $discountValue;
							}
						}
						break;
					case 'REK_VALUES':
						foreach($fieldInfo['FIELDS'] as $rekFieldName=>$rekFieldInfo)
						{
							$propertyValue = '';
							switch ($rekFieldName)
							{
								case 'TYPE_NOMENKLATURA':
									foreach ($rekFieldInfo['FIELDS'] as $nameProp=>$infoProp)
									{
										$valueProp='';
										switch ($nameProp)
										{
											case 'NAME':
												$valueProp = DocumentBase::getLangByCodeField('TYPE_NOMENKLATURA');
												break;
											case 'VALUE':
												$valueProp = DocumentBase::getLangByCodeField($item['PRODUCT_XML_ID']==ImportOneCBase::DELIVERY_SERVICE_XMLID ? ImportBase::ITEM_SERVICE:ImportBase::ITEM_ITEM);
												break;
										}
										$this->externalizeField($valueProp, $infoProp);
										$propertyValue[$nameProp] = $valueProp;
									}
									$value[] = $propertyValue;
									break;
								case 'TYPE_OF_NOMENKLATURA':
									foreach ($rekFieldInfo['FIELDS'] as $nameProp=>$infoProp)
									{
										$valueProp='';
										switch ($nameProp)
										{
											case 'NAME':
												$valueProp = DocumentBase::getLangByCodeField('TYPE_OF_NOMENKLATURA');
												break;
											case 'VALUE':
												$valueProp = DocumentBase::getLangByCodeField($item['PRODUCT_XML_ID']==ImportOneCBase::DELIVERY_SERVICE_XMLID ? ImportBase::ITEM_SERVICE:ImportBase::ITEM_ITEM);
												break;
										}
										$this->externalizeField($valueProp, $infoProp);
										$propertyValue[$nameProp] = $valueProp;
									}
									$value[] = $propertyValue;
									break;
								case 'BASKET_NUMBER':
									foreach ($rekFieldInfo['FIELDS'] as $nameProp=>$infoProp)
									{
										$valueProp='';
										switch ($nameProp)
										{
											case 'NAME':
												$valueProp = DocumentBase::getLangByCodeField('BASKET_NUMBER');
												break;
											case 'VALUE':
												$valueProp = \CSaleExport::getNumberBasketPosition($item['ID']);
												break;
										}
										$this->externalizeField($valueProp, $infoProp);
										$propertyValue[$nameProp] = $valueProp;
									}
									$value[] = $propertyValue;
									break;
								case 'PROPERTY_VALUE_BASKET':
									$attributes = isset($item['ATTRIBUTES'])? $item['ATTRIBUTES']:array();
									if(count($attributes)>0)
									{
										foreach ($attributes as $rowIdAttr=>$attribute)
										{
											foreach ($rekFieldInfo['FIELDS'] as $nameProp=>$infoProp)
											{
												$valueProp='';
												switch ($nameProp)
												{
													case 'NAME':
														$valueProp = DocumentBase::getLangByCodeField('PROPERTY_VALUE_BASKET').'#'.($attribute['CODE']<>'' ? $attribute['CODE']:$attribute['NAME']);
														break;
													case 'VALUE':
														$valueProp = $attribute['VALUE'];
														break;
												}
												$this->externalizeField($valueProp, $infoProp);
												$value[$rowIdAttr][$nameProp] = $valueProp;
											}
										}
									}
									break;
							}
						}
						break;
					case 'TAX_RATES':
						$rate = doubleval($item['VAT_RATE']);
						if($rate > 0)
						{
							foreach($fieldInfo['FIELDS'] as $rateFieldName=>$rateFieldInfo)
							{
								$rateValue = '';
								switch ($rateFieldName)
								{
									case 'NAME':
										$rateValue = DocumentBase::getLangByCodeField('VAT');
										break;
									case 'RATE':
										$rateValue = $item['VAT_RATE']*100;
										break;
								}
								$this->externalizeField($rekValue, $rateFieldInfo);
								$value[$rateFieldName] = $rateValue;
							}
						}
						break;
					case 'TAXES':
						$rate = doubleval($item['VAT_RATE']);
						if($rate > 0)
						{
							foreach($fieldInfo['FIELDS'] as $taxFieldName=>$taxFieldInfo)
							{
								$taxValue = '';
								switch ($taxFieldName)
								{
									case 'NAME':
										$taxValue = DocumentBase::getLangByCodeField('VAT');
										break;
									case 'TAX_VALUE':
										$taxValue = (($item["PRICE"] / ($item["VAT_RATE"]+1)) * $item["VAT_RATE"]);
										break;
									case 'IN_PRICE':
										$taxValue = 'Y';
										break;
								}
								$this->externalizeField($taxValue, $taxFieldInfo);
								$value[$taxFieldName] = $taxValue;
							}
						}
						break;
				}

				if(!is_array($value))
					$this->externalizeField($value, $fieldInfo);
				$result[$rowId][$name] = $value;
			}
		}
		return $result;
	}

	/**
	 * @param $stories
	 * @param $info
	 * @param array $params
	 * @return array
	 */
	public function externalizeStories(array $stories, array $info)
	{
		$result = array();
		$converterProfile = new ConverterDocumentProfile();

		foreach ($stories as $store)
		{
			$store = static::getStoreById($store['ID']);

			$resultStores=array();
			foreach($info['FIELDS'] as $name=>$fieldInfo)
			{
				$value='';
				switch ($name)
				{
					case 'ID':
						$value = $store['XML_ID'];
						break;
					case 'NAME':
						$value = $store['TITLE'];
						break;
					case 'ADDRESS':
						if(isset($store['ADDRESS']))
							$value = $converterProfile->externalizeArrayFields(array('STREET'=>$store['ADDRESS']), $fieldInfo);
						break;
					case 'CONTACTS':
						if(isset($store['PHONE']))
							$value = $converterProfile->externalizeArrayFields(array('WORK_PHONE_NEW'=>$store['PHONE']), $fieldInfo);
						break;
				}
				if(!is_array($value))
					$this->externalizeField($value, $fieldInfo);
				$resultStores[$name] = $value;
			}
			$result[] = $resultStores;
		}
		return $result;
	}

	/**
	 * @param $taxes
	 * @param array $info
	 * @return array
	 */
	public function externalizeTaxes(array $taxes, array $info)
	{
		$result = array();
		foreach ($taxes as $rowId=>$tax)
		{
			foreach($info['FIELDS'] as $name=>$fieldInfo)
			{
				$value='';
				switch($name)
				{
					case 'NAME':
						$value = $tax['TAX_NAME'];
						break;
					case 'IN_PRICE':
						$value = $tax['IS_IN_PRICE'];
						break;
					case 'SUMM':
						$value = $tax['VALUE_MONEY'];
						break;
				}
				$this->externalizeField($value, $fieldInfo);
				$result[$rowId][$name] = $value;
			}
		}
		return $result;
	}

	/**
	 * @param array $discounts
	 * @param array $info
	 * @return array
	 */
	public function externalizeDiscounts(array $discount, array $info)
	{
		$result = array();

		if(doubleval($discount['DISCOUNT_VALUE'])>0)
		{
			foreach($info['FIELDS'] as $name=>$fieldInfo)
			{
				$value='';
				switch($name)
				{
					case 'NAME':
						$value = DocumentBase::getLangByCodeField('ORDER_DISCOUNT');
						break;
					case 'IN_PRICE':
						$value = 'N';
						break;
					case 'AMOUNT':
						$value = $discount['DISCOUNT_VALUE'];
						break;
				}
				$this->externalizeField($value, $fieldInfo);
				$result[$name] = $value;
			}
		}
		return $result;
	}

	/**
	 * @param $code
	 * @return mixed
	 */
	static private function getCatalogMeasureByCode($code)
	{
		static $measure;

		if($measure[$code] === null)
		{
			if(Loader::includeModule("catalog"))
			{
				$r = \CCatalogMeasure::getList(
					array(),
					array(),
					false,
					false,
					array(
						"CODE",
						"MEASURE_TITLE"
					)
				);
				while($res = $r->Fetch())
				{
					$measure[$res["CODE"]] = $res["MEASURE_TITLE"];
				}
			}
			if($measure === null)
				$measure[self::MEASURE_CODE_DEFAULT] = \CSaleExport::getTagName("SALE_EXPORT_SHTUKA");
		}
		return $measure[$code];
	}

	/**
	 * @param $id
	 * @return array
	 */
	static private function getStoreById($id)
	{
		static $stories;
		if($stories[$id] === null)
		{
			if(Loader::includeModule('catalog'))
			{
				$res = \CCatalogStore::GetList(
					array("SORT" => "DESC", "ID" => "ASC"),
					array("ACTIVE" => "Y", "ISSUING_CENTER" => "Y"),
					false,
					false,
					array("ID", "SORT", "TITLE", "ADDRESS", "DESCRIPTION", "PHONE", "EMAIL", "XML_ID")
				);
				while ($store = $res->Fetch())
				{
					if(strlen($store["XML_ID"]) <= 0)
						$store["XML_ID"] = $store["ID"];

					$stories[$store["ID"]] = $store;
				}

				if(!is_array($stories))
				{
					$stories = array();
				}
			}
		}
		return (isset($stories[$id]) ? $stories[$id]:array());
	}

	/**
	 * @param $id
	 * @return string
	 */
	static private function getStatusNameById($id)
	{
		static $statuses;

		if($statuses === null)
		{
			$res = StatusLangTable::getList(array(
				'select' => array('*'),
				'filter' => array('=LID' => LANGUAGE_ID)
			));
			while($status = $res->fetch())
			{
				$statuses[$status['STATUS_ID']] = $status['NAME'];
			}

			if(!is_array($statuses))
			{
				$statuses = array();
			}
		}
		return (isset($statuses[$id])?$statuses[$id]:'');
	}
}
