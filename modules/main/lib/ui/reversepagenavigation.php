<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Main\UI;

class ReversePageNavigation extends PageNavigation
{
	/**
	 * @param string $id Navigation identity like "nav-cars".
	 * @param int $count Record count.
	 */
	public function __construct($id, $count)
	{
		parent::__construct($id);
		$this->setRecordCount($count);
	}

	/**
	 * Returns number of pages.
	 * @return int
	 */
	public function getPageCount()
	{
		if($this->allRecords)
		{
			return 1;
		}
		$maxPages = (int)floor($this->recordCount/$this->pageSize);
		if($this->recordCount > 0 && $maxPages == 0)
		{
			$maxPages = 1;
		}
		return $maxPages;
	}

	/**
	 * Returns the current page number.
	 * @return int
	 */
	public function getCurrentPage()
	{
		if($this->currentPage !== null)
		{
			return $this->currentPage;
		}
		return $this->getPageCount();
	}

	/**
	 * Returns offset of the first record of the current page.
	 * @return int
	 */
	public function getOffset()
	{
		if($this->allRecords)
		{
			return 0;
		}

		$offset = 0;
		$pageCount = $this->getPageCount();
		$currentPage = $this->getCurrentPage();

		if($currentPage <> $pageCount)
		{
			//counting the last page (wich is the first one on reverse paging)
			$offset += ($this->recordCount % $this->pageSize);
		}

		$offset += ($pageCount - $currentPage) * $this->pageSize;

		return $offset;
	}

	/**
	 * Returns the number of records in the current page.
	 * @return int
	 */
	public function getLimit()
	{
		if($this->allRecords)
		{
			return $this->getRecordCount();
		}
		if($this->getCurrentPage() == $this->getPageCount())
		{
			//the last page (displayed first)
			return $this->pageSize + ($this->recordCount % $this->pageSize);
		}
		else
		{
			return $this->pageSize;
		}
	}
}
