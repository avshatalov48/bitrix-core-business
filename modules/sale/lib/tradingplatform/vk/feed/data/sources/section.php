<?php

namespace Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Sources;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Sale\TradingPlatform\Vk;

/**
 * Class Section
 * Complex iterator for processing albums from several iblocks
 *
 * @package Bitrix\Sale\TradingPlatform\Vk\Feed\Data\Sources
 */
class Section extends DataSource implements \Iterator
{
	protected $sections;
	protected $resSections;
	protected $currentKey = 0;
	protected $currentRecord = array();

	protected $vk;
	protected $exportId;

	protected $sectionsToExport;
	protected $sectionsAliases;

	protected $startPosition = 0;
	
	/**
	 * Section constructor.
	 * @param $exportId
	 * @param $startPosition
	 */
	public function __construct($exportId, $startPosition)
	{
		$this->vk = Vk\Vk::getInstance();

		if (!$this->vk->isActive())
			throw new SystemException("Vk is not active!" . __METHOD__);

		if (!isset($exportId) || strlen($exportId) <= 0)
			throw new ArgumentNullException("EXPORT_ID");

		if (!Loader::includeModule('iblock'))
			throw new SystemException("Can't include module \"IBlock\"! " . __METHOD__);

		$this->exportId = $exportId;

//		get list of sections to export IDs
		$sectionsList = new Vk\SectionsList($this->exportId);
		$preparedSections = $sectionsList->getSectionsToAlbumsExport();
		$this->sectionsToExport = $preparedSections["SECTIONS"];
		$this->sectionsAliases = $preparedSections["ALIASES"];
		unset($preparedSections);

//		CHECK is empty list
		if (empty($this->sectionsToExport))
		{
			$logger = new Vk\Logger($this->exportId);
			$logger->addError('EMPTY_SECTIONS_LIST');
		}

		$this->setStartPosition($startPosition);
	}

	
	/**
	 * Create object for get values. Later we well Fetch them
	 *
	 * @return array|bool|\CDBResult|\CIBlockResult
	 */
	private function createSectionsDbObject()
	{
		return \CIBlockSection::GetList(
			array("ID" => "ASC"),
			array("ID" => $this->sectionsToExport, "ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", "CHECK_PERMISSIONS" => "N"),
			true,
			array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PICTURE", "DETAIL_PICTURE", "LEFT_MARGIN", "RIGHT_MARGIN", "DESCRIPTION"),
			false
		);
	}

	
	
	/**
	 * More simply setStartPosition for albums
	 *
	 * @param string $startPosition - number of start position
	 */
	protected function setStartPosition($startPosition)
	{
		if ($startPosition && $startPosition > 0)
		{
			$this->startPosition = intval($startPosition);
//			we can use two condition in filter on ID: in array and >=startPostion.
//			therefore prepare array of sections to export like a >=
			foreach ($this->sectionsToExport as $key => $section)
			{
				if ($key < $startPosition)
					unset($this->sectionsToExport[$key]);
			}
		}
	}

	/**
	 * Owerwrite ITERATOR method
	 * @return array
	 */
	public function current()
	{
		return $this->currentRecord;
	}

	/**
	 * Owerwrite ITERATOR method
	 * @return int
	 */
	public function key()
	{
		return $this->currentKey;
	}

	/**
	 * Owerwrite ITERATOR method
	 */
	public function next()
	{
		$this->currentKey++;
		$this->currentRecord = $this->nextItem();
	}

	/**
	 * Fetch once next item from bd object
	 *
	 * @return null
	 */
	private function nextItem()
	{
		$currItem = NULL;
//		only if album exist and  not empty
		if ($obCurrItem = $this->resSections->GetNextElement(true, false))
		{
			$currItem = $obCurrItem->GetFields();
			if ($currItem["ELEMENT_CNT"] > 0)
//				put album alias from map. Better do it here and not getting map (only for it value) in converter
				$currItem["TO_ALBUM_ALIAS"] = isset($this->sectionsAliases[$currItem["ID"]]) ?
					$this->sectionsAliases[$currItem["ID"]] : false;
		}

		return $currItem;
	}

	/**
	 * Owerwrite ITERATOR method
	 */
	public function rewind()
	{
		$this->currentKey = 0;
		$this->resSections = $this->createSectionsDbObject();
		$this->currentRecord = $this->nextItem();
	}

	/**
	 * Owerwrite ITERATOR method
	 * @return bool
	 */
	public function valid()
	{
		return is_array($this->currentRecord);
	}
}