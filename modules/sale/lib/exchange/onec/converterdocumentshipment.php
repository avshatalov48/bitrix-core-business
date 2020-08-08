<?php
namespace Bitrix\Sale\Exchange\OneC;


use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Exchange\EntityType;
use Bitrix\Sale\Exchange\ImportBase;
use Bitrix\Sale\Exchange\ImportOneCBase;
use Bitrix\Sale\Exchange\ISettings;
use Bitrix\Sale\Exchange\ISettingsExport;
use Bitrix\Sale\Exchange\ISettingsImport;
use Bitrix\Sale\Shipment;


/**
 * Class ConverterDocumentShipment
 * @package Bitrix\Sale\Exchange\OneC
 * @deprecated
 */
class ConverterDocumentShipment extends Converter
{
	/**
	 * @return array
	 */
	protected function getFieldsInfo()
	{
		return ShipmentDocument::getFieldsInfo();
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

		$availableFields = Shipment::getAvailableFields();

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
				case 'DELIVERY_DOC_DATE':
					if(isset($params['1C_DATE']))
						$fields[$k] = $params['1C_DATE'];
					break;
				case 'DELIVERY_DOC_NUM':
					if(isset($params['REK_VALUES']['1C_DELIVERY_NUM']))
						$fields[$k] = $params['REK_VALUES']['1C_DELIVERY_NUM'];
					break;
				case 'DEDUCTED':
					$deducted='';
					$cancel='';

					if(isset($params['REK_VALUES']['DEDUCTED']))
						$deducted = $params['REK_VALUES']['DEDUCTED'];
					if(isset($params['REK_VALUES']['CANCEL']))
						$cancel = $params['REK_VALUES']['CANCEL'];

					if($deducted == 'Y')
						$fields[$k] = 'Y';
					elseif($cancel == 'Y')
						$fields[$k] = 'N';
					break;
				case 'ALLOW_DELIVERY':
					$value='';
					if(isset($params['REK_VALUES']['DEDUCTED']))
						$value = $params['REK_VALUES']['DEDUCTED'];

					if($value == 'Y')
						$fields[$k] = 'Y';
					break;
				case 'TRACKING_NUMBER':
					if(isset($params['REK_VALUES']['1C_TRACKING_NUMBER']))
						$fields[$k] = $params['REK_VALUES']['1C_TRACKING_NUMBER'];
					break;
				case 'BASE_PRICE_DELIVERY':
					$fields["BASE_PRICE_DELIVERY"] = $this->getBasePriceDelivery($params['ITEMS']);
					break;
				case 'DELIVERY_ID':
					$deliverySystemId = 0;
					if(isset($params['REK_VALUES']['DELIVERY_SYSTEM_ID']))
					{
						$deliverySystemId = $params['REK_VALUES']['DELIVERY_SYSTEM_ID'];
					}

					if($deliverySystemId<=0)
					{
						if(isset($params['REK_VALUES']['DELIVERY_SYSTEM_ID_DEFAULT']))
						{
							$deliverySystemId = $params['REK_VALUES']['DELIVERY_SYSTEM_ID_DEFAULT'];
						}
					}

					/** @var ImportSettings $settings */
					$settings = $this->getSettings();

					if($deliverySystemId<=0)
					{
						$deliverySystemId = $settings->shipmentServiceFor($this->getEntityTypeId());
					}

					if($deliverySystemId<=0)
					{
						$deliverySystemId = $settings->shipmentServiceDefaultFor($this->getEntityTypeId());
					}

					$fields[$k] = $deliverySystemId;
					break;
			}
		}

		$result['TRAITS'] = isset($fields)? $fields:array();
		$result['ITEMS'] = isset($params['ITEMS'])? $this->modifyItemIdByItemName($params['ITEMS']):array();
		$result['TAXES'] = isset($params['TAXES'])? $params['TAXES']:array();

		return $result;
	}

	/**
	 * @param Shipment|null $shipment
	 * @param array $fields
	 * @throws ArgumentException
	 */
	static public function sanitizeFields($shipment=null, array &$fields, ISettings $settings)
	{
		if(!empty($shipment) && !($shipment instanceof Shipment))
			throw new ArgumentException("Entity must be instanceof Shipment");

		foreach($fields as $k=>$v)
		{
			switch($k)
			{
				case 'BASE_PRICE_DELIVERY':
					if((!empty($shipment)? $shipment->getPrice():'') != $v)
					{
						/** @var ISettingsImport $settings */
						$fields['CURRENCY'] = $settings->getCurrency();
						$fields['CUSTOM_PRICE_DELIVERY'] = "Y";
					}
					else
					{
						unset($fields['BASE_PRICE_DELIVERY']);
					}
					break;
				case 'DELIVERY_ID':
					if(!empty($shipment))
					{
						unset($fields['DELIVERY_ID']);
					}
					break;
			}
		}
		unset($fields['ID']);
	}

	public function externalize(array $fields)
	{
		$result = array();

		$traits = $fields['TRAITS'];
		$items = $fields['ITEMS'];
		$stories = isset($fields['STORIES']) ? $fields['STORIES']: array();
		$taxes = $fields['TAXES'];
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
					$value = ($traits['ID_1C']<>'' ? $traits['ID_1C']:$traits['ID']);
					break;
				case 'NUMBER':
					$value = $traits['ID'];
					break;
				case 'DATE':
					$value = $traits['DATE_INSERT'];
					break;
				case 'OPERATION':
					$value = DocumentBase::resolveDocumentTypeName($this->getDocmentTypeId());
					break;
				case 'ROLE':
					$value = DocumentBase::getLangByCodeField('SELLER');
					break;
				case 'CURRENCY':
					$replaceCurrency = $settings->getReplaceCurrency();
					$value = mb_substr($replaceCurrency <> ''? $replaceCurrency : $traits[$k], 0, 3);
					break;
				case 'CURRENCY_RATE':
					$value = self::CURRENCY_RATE_DEFAULT;;
					break;
				case 'AMOUNT':
					$price = 0;
					foreach ($items as $item)
					{
						if($item['PRODUCT_XML_ID'] !== ImportOneCBase::DELIVERY_SERVICE_XMLID)
							$price = $price + $item['PRICE'] * $item['QUANTITY'];
					}

					$value = $price + $traits['PRICE_DELIVERY'];

					break;
				case 'VERSION':
					$value = $traits['VERSION'];
					break;
				case 'NUMBER_BASE':// ?????
					$value = $traits['ORDER_ID'];
					break;
				case 'TAXES':
					if(count($taxes)>0)
						$value = $this->externalizeTaxes($taxes, $v);
					break;
				case 'STORIES':
					if(count($stories)>0)
						$value = $this->externalizeStories($stories, $v);
					break;
				case 'TIME':
					$value = $traits['DATE_INSERT'];
					break;
				case 'COMMENT':
					$value = $traits['COMMENTS'];
					break;
				case 'ITEMS':
					if(count($items)>0)
						$value = $this->externalizeItems($items, $v);
					break;
				case 'REK_VALUES':
					$value=array();
					foreach($v['FIELDS'] as $name=>$fieldInfo)
					{
						$valueRV='';
						switch($name)
						{
							case 'PRICE_DELIVERY':
								$valueRV = ($traits['PRICE_DELIVERY'] <> ''? $traits['PRICE_DELIVERY']:"0.0000");
								break;
							case 'DATE_ALLOW_DELIVERY':
							case 'DELIVERY_LOCATION':
							case 'DATE_DEDUCTED':
							case 'REASON_UNDO_DEDUCTED':
							case 'RESERVED':
							case 'REASON_MARKED':
							case 'TRACKING_NUMBER':
								$valueRV = $traits[$name];
								break;
							case 'CANCEL':
								$valueRV = $traits['CANCELED'];
								break;
							case 'DELIVERY_SYSTEM_ID':
								$valueRV = $traits['DELIVERY_ID'];
								break;
							case 'DELIVERY_STATUS':
								$valueRV = $traits['STATUS_ID'];
								break;
							case 'DELIVERY_DEDUCTED':
								$valueRV = $traits['DEDUCTED'];
								break;
							case 'DELIVERY':
								$valueRV = $traits['DELIVERY_NAME'];
								break;
							case 'DELIVERY_DATE_CANCEL':
								$valueRV = $traits['DATE_CANCELED'];
								break;
							case 'CANCEL_REASON':
								$valueRV = $traits['REASON_CANCELED'];
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
			if(!in_array($k, array('TAXES', 'STORES', 'ITEMS', 'REK_VALUES')))
			{
				$this->externalizeField($value, $v);
			}

			$result[$k] = $value;
		}
		$result = $this->modifyTrim($result);
		return $result;
	}

	public function externalizeItems(array $taxes, array $info)
	{
		/** @var ConverterDocumentOrder $converter */
		$converter = ConverterFactory::create(EntityType::ORDER);
		return $converter->externalizeItems($taxes, $info);
	}

	public function externalizeStories(array $stories, array $info)
	{
		/** @var ConverterDocumentOrder $converter */
		$converter = ConverterFactory::create(EntityType::ORDER);
		return $converter->externalizeStories($stories, $info);
	}

	public function externalizeTaxes(array $items, array $info)
	{
		/** @var ConverterDocumentOrder $converter */
		$converter = ConverterFactory::create(EntityType::ORDER);
		return $converter->externalizeTaxes($items, $info);
	}

	protected function getBasePriceDelivery($list=[])
	{
		if(is_array($list) && count($list)>0)
		{
			foreach($list as $item)
			{
				foreach($item as $fields)
				{
					if($fields['TYPE'] == ImportBase::ITEM_SERVICE)
					{
						//if((!empty($shipment)? $shipment->getPrice():'') != $item["PRICE"])
						//{
						//$fields["CUSTOM_PRICE_DELIVERY"] = "Y";
						return $fields["PRICE"];
						//$fields["CURRENCY"] = $settings->getCurrency();
						//}
						//break 2;
					}
				}
			}
		}
		return 0;
	}
}