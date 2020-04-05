<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Sources;

use Bitrix\Catalog\Ebay\ExportOfferCreator;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\TradingPlatform\Vk;

/**
 * Class Product
 * Complex iterator for processing products from several iblocks
 *
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Sources
 */
class Product extends DataSource implements \Iterator
{
	protected $feeds = array();
	protected $currentFeed;
	protected $startPosition = array();

	protected $vk;
	protected $exportId;

	/**
	 * Product constructor.
	 * @param $exportId	- int value of export ID
	 * @param $startPosition - can be null. ID of first element to process
	 */
	public function __construct($exportId, $startPosition)
	{
		$this->vk = Vk\Vk::getInstance();
		
		if (!$this->vk->isActive())
			throw new SystemException("Vk is not active!" . __METHOD__);

//		check and set EXPORT ID
		if (!isset($exportId) || strlen($exportId) <= 0)
			throw new ArgumentNullException("EXPORT_ID");
		$this->exportId = $exportId;

//		check and set START POSITION
		$this->setStartPosition($startPosition);
		
		if (!Loader::includeModule('catalog'))
			throw new SystemException("Can't include module \"Catalog\"! " . __METHOD__);

//		get items only from sections, that was checked to export. And get them iblocksIds
		$sectionsList = new Vk\SectionsList($this->exportId);
		$sectionsToExport = $sectionsList->getSectionsToProductExport();
		$logger = new Vk\Logger($this->exportId);
		if(!empty($sectionsToExport))
		{
			$logger->addLog('Sections to export', $sectionsToExport);
		}
		$iblockIds = $sectionsList->getMappedIblocks();
		
//		if not products to export - ERROR
		if (empty($sectionsToExport))
		{
			$logger->addError('EMPTY_SECTIONS_LIST');
		}

//		create FEEDS
		foreach ($iblockIds as $iblockId)
		{
			$exportOfferParams = array(
				"IBLOCK_ID" => $iblockId,
				"PRODUCT_GROUPS" => $sectionsToExport[$iblockId],
				"INCLUDE_SUBSECTION" => false // we have all sections in PRODUCT_GROUPS, subsections is not needed
			);
//			set start position, if exist. Set current feed as start
			if (isset($this->startPosition[$iblockId]))
			{
				$exportOfferParams["START_POSITION"] = $this->startPosition[$iblockId];
				$this->startFeed = count($this->feeds);
			}
			
			$feed = ExportOfferCreator::getOfferObject($exportOfferParams);
			if($this->vk->getAvailableFlag($this->exportId))
				$feed->setOnlyAvailableFlag(true);
			
			$this->feeds[] = $feed;
			unset($feed);
		}
	}


	protected function setStartPosition($startPosition)
	{
		if (strlen($startPosition) > 0)
		{
//			todo: maybe can use cache from sectionslist
//			find IblockId for this product ID
			if (Loader::includeModule("catalog") && Loader::includeModule("iblock"))
			{
				$resIblockId = \CIBlockElement::GetList(array(), array("ID" => $startPosition), false, false, array("IBLOCK_ID"));
				if ($iblockId = $resIblockId->Fetch())
					$this->startPosition[$iblockId["IBLOCK_ID"]] = $startPosition;
			}
		}
	}
	
	/**
	 * Owerwrite ITERATOR method
	 * @return mixed
	 */
	public function current()
	{
		$current = $this->feeds[$this->currentFeed]->current();
		return $current;
	}

	/**
	 * Owerwrite ITERATOR method
	 * @return string
	 */
	public function key()
	{
		return $this->currentFeed . "_" . $this->feeds[$this->currentFeed]->key();
	}

	/**
	 * Owerwrite ITERATOR method
	 */
	public function next()
	{
		$this->feeds[$this->currentFeed]->next();
		
		// step to the next product feed
		if (!$this->valid() && $this->currentFeed < count($this->feeds) - 1)
		{
			$this->currentFeed++;
			$this->next();
		}
	}

	/**
	 * Owerwrite ITERATOR method
	 */
	public function rewind()
	{
		$this->currentFeed = $this->startFeed;
		
		foreach ($this->feeds as $feed)
			$feed->rewind();
	}

	/**
	 * Owerwrite ITERATOR method
	 * @return mixed
	 */
	public function valid()
	{
		return $this->feeds[$this->currentFeed]->valid();
	}
}