<?php

namespace Bitrix\Sale\Exchange;


use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Exchange\Entity\EntityImport;
use Bitrix\Sale\Exchange\Entity\OrderImport;
use Bitrix\Sale\Result;

trait LoggerTrait
{
	/** @var  $rawData null */
	protected $rawData;

	/**
	 * @param null $rawData
	 */
	public function setRawData($rawData)
	{
		$this->rawData = $rawData;
	}

	/**
	 * @return null
	 */
	public function getRawData()
	{
		return $this->rawData;
	}

	/**
	 * @return string
	 */
	abstract public function getDirectionType();

	/**
	 * @param ImportBase[] $items
	 * @return Result
	 */
	protected function loggerEntities(array $items)
	{
		$result = new Result();

		foreach ($items as $item)
		{
			if($item->hasLogging())
			{
				$logger = $item->getLogger();

				$logger->setField('ENTITY_ID', $item->getId());
				$logger->setField('ENTITY_TYPE_ID', $item->getOwnerTypeId());
				$logger->setField('XML_ID', $item->getExternalId());
				$logger->setField('DIRECTION', $this->getDirectionType());

				$logger->save();
			}
		}
		return $result;
	}

	/**
	 * @param ImportBase[] $items
	 * @param OrderImport $orderItem
	 * @return Result
	 */
	protected function loggerEntitiesPackage(array $items, OrderImport $orderItem)
	{
		$xmlStreem = $this->getRawData();

		foreach ($items as $item)
		{
			if($item->hasLogging())
			{
				$logger = $item->getLogger();

				if($item instanceof OrderImport)
				{
					$logger->setField('MESSAGE', $xmlStreem);
					$logger->setField('PARENT_ID', $orderItem->getId());
					$logger->setField('MARKED', $item->isMarked()?'Y':'N');
					$logger->setField('ENTITY_DATE_UPDATE', $item->getField('TRAITS')['DATE_UPDATE']);
				}
				else
				{
					if($item instanceof EntityImport)
					{
						$logger->setField('PARENT_ID', $item->getParentEntity()->getId());
						$logger->setField('OWNER_ENTITY_ID', $orderItem->getId());
						$logger->setField('MARKED', $item->isMarked()?'Y':'N');
					}
					else
					{
						$logger->setField('PARENT_ID', $orderItem->getId());
					}
				}
			}
		}
		return $this->loggerEntities($items);
	}
}