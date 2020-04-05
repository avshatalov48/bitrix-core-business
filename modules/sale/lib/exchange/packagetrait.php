<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Sale\Exchange\Entity\EntityImport;
use Bitrix\Sale\Exchange\OneC\DocumentType;

trait PackageTrait
{
	/**
	 * @param ImportBase $item
	 * @param array $fields
	 * @param null $order
	 */
	protected static function load(ImportBase $item, array $fields, $order=null)
	{
		if($item->getOwnerTypeId() <> static::getParentEntityTypeId())
		{
			if($item instanceof EntityImport)
			{
				$item->setParentEntity($order);
			}
		}

		$item->load($fields);
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	protected function getProductsItems(array $fields)
	{
		return (isset($fields['ITEMS']) && is_array($fields['ITEMS'])) ? $fields['ITEMS']:array();
	}

	/**
	 * @param $type_id
	 * @param OneC\DocumentBase[] $documents
	 * @return bool
	 */
	protected function hasDocumentByTypeId($type_id, array $documents)
	{
		$documentImport = $this->getDocumentByTypeId($type_id, $documents);

		return ($documentImport !== null);
	}

	/**
	 * @param $type_id
	 * @param OneC\DocumentBase[] $documents
	 * @return OneC\DocumentBase|null
	 */
	protected function getDocumentByTypeId($type_id, array $documents)
	{
		foreach($documents as $document)
		{
			if(DocumentType::isDefined($type_id))
			{
				if($document->getTypeId() == $type_id)
				{
					return $document;
				}
			}
		}

		return null;
	}

	/**
	 * @param $type_id
	 * @param ImportBase[] $items
	 * @return ImportBase|null
	 */
	protected function getEntityByTypeId($type_id, array $items)
	{
		foreach($items as $item)
		{
			if(EntityType::isDefined($type_id))
			{
				if($item->getOwnerTypeId() == $type_id)
				{
					return $item;
				}
			}
		}

		return null;
	}

	/**
	 * @param array $list
	 * @return bool
	 */
	protected function deliveryServiceExists(array $list)
	{
		$deliveryItem = $this->getDeliveryServiceItem($list);

		return !($deliveryItem === null);
	}
}