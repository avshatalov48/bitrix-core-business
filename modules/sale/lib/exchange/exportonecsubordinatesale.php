<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\Entity\SubordinateSale\EntityImportFactory;
use Bitrix\Sale\Exchange\OneC\DocumentBase;
use Bitrix\Sale\Exchange\OneC\PaymentDocument;
use Bitrix\Sale\Exchange\OneC\ShipmentDocument;
use Bitrix\Sale\Exchange\OneC\SubordinateSale\ConverterFactory;

final class ExportOneCSubordinateSale extends ExportOneCPackage
{
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
		return \Bitrix\Sale\Exchange\OneC\SubordinateSale\DocumentFactory::create($typeId);
	}

	/**
	 * @param DocumentBase[] $documents
	 */
	protected function convertDocumentFields(array $documents)
	{
		parent::convertDocumentFields($documents);

		$documentOrder = $this->getDocumentByTypeId(EntityType::ORDER, $documents);
		$fieldsOrder = $documentOrder->getFieldValues();
		foreach ($documents as $document)
		{
			if($document instanceof PaymentDocument || $document instanceof ShipmentDocument)
			{

				$fieldsOrder['SUBORDINATES'][] = $document->getFieldValues();
			}
		}
		$documentOrder->setFields($fieldsOrder);
	}

	/**
	 * @return string
	 */
	protected function getShemVersion()
	{
		return static::SHEM_VERSION_2_10;
	}

	/**
	 * @param array $list
	 * @return array
	 */
	protected function modifyDocumentsCollection(array $list)
	{
		return array($this->getDocumentByTypeId(EntityType::ORDER, $list));
	}

	/**
	 * @param $typeId
	 * @return ImportBase
	 */
	protected function entityFactoryCreate($typeId)
	{
		return EntityImportFactory::create($typeId);
	}
}