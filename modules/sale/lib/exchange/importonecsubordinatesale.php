<?php
namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\SubordinateSale\ConverterFactory;
use Bitrix\Sale\Exchange\OneC\SubordinateSale\DocumentFactory;

final class ImportOneCSubordinateSale extends ImportOneCPackage
{
	protected function convert(array $documents)
	{
		$documentOrder = $this->getDocumentByTypeId(EntityType::ORDER, $documents);

		if($documentOrder instanceof OneC\OrderDocument)
		{
			$fieldsOrder = $documentOrder->getFieldValues();
			$itemsOrder = $this->getProductsItems($fieldsOrder);

			if(is_array($fieldsOrder['SUBORDINATES']))
			{
				foreach ($fieldsOrder['SUBORDINATES'] as $subordinateDocumentFields)
				{
					$typeId = $this->resolveSubordinateDocumentTypeId($subordinateDocumentFields);

					if($typeId == EntityType::SHIPMENT)
					{
						$subordinateDocumentItems = array();
						$itemsSubordinate = $this->getProductsItems($subordinateDocumentFields);

						foreach ($itemsSubordinate as $itemSubordinate)
						{
							$xmlId = key($itemSubordinate);

							if($xmlId == self::DELIVERY_SERVICE_XMLID)
							{
								$itemSubordinate[$xmlId]['TYPE'] = ImportBase::ITEM_SERVICE;
								$subordinateDocumentItems[] = $itemSubordinate;
							}
							else
							{
								$item = $this->getItemByParam($xmlId, $itemsOrder);

								if($item !== null)
								{
									$item[$xmlId]['QUANTITY'] = $itemSubordinate[$xmlId]['QUANTITY'];
									$subordinateDocumentItems[] = $item;
								}
							}
						}

						unset($subordinateDocumentFields['ITEMS']);
						unset($subordinateDocumentFields['ITEMS_FIELDS']);

						if(count($subordinateDocumentItems)>0)
						{
							$subordinateDocumentFields['ITEMS'] = $subordinateDocumentItems;
						}
					}

					$document = OneC\DocumentImportFactory::create($typeId);
					$document->setFields($subordinateDocumentFields);
					$documents[] = $document;
				}
				$documentOrder->setField('SUBORDINATES', '');
			}
		}
		return parent::convert($documents);
	}

	/**
	 * @param array $fields
	 * @return int
	 */
	protected function resolveSubordinateDocumentTypeId(array $fields)
	{
		$typeId = EntityType::UNDEFINED;

		if(isset($fields['OPERATION']))
		{
			$typeId = EntityType::resolveID($fields['OPERATION']);
		}
		return $typeId;
	}

	/**
	 * @param $xmlId
	 * @param array $items
	 * @param array|null $params
	 * @return mixed|null
	 */
	protected function getItemByParam($key, array $items, array $params=null)
	{
		foreach ($items as $item)
		{
			if(array_key_exists($key, $item))
			{
				return $item;
			}
		}
		return null;
	}

	/**
	 * @param $typeId
	 * @return IConverter
	 */
	protected function converterFactoryCreate($typeId)
	{
		return ConverterFactory::create($typeId);
	}

	/**
	 * @param $typeId
	 * @return DocumentBase
	 */
	protected function documentFactoryCreate($typeId)
	{
		return DocumentFactory::create($typeId);
	}
}