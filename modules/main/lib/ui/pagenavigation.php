<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */
namespace Bitrix\Main\UI;

use Bitrix\Main\Web;

/**
 * Class PageNavigation
 *
 * This class helps to calculate limits for DB queries and other data sources
 * to organize page navigation through results.
 *
 * Examples of supported URLs:
 * /page.php?nav-cars=page-5&nav-books=page-2&other=params
 * /page.php?nav-cars=page-5-size-20&nav-books=page-2
 * /page.php?nav-cars=page-all&nav-books=page-2
 * /page/nav-cars/page-2/size-20/something/
 * /page/nav-cars/page-all/something/?other=params
 * /page/nav-cars/page-5/nav-books/page-2/size-10
 */
class PageNavigation
{
	protected $id;
	protected $pageSizes = array();
	protected $pageSize = 20;
	protected $recordCount;
	protected $currentPage;
	protected $allowAll = false;
	protected $allRecords = false;

	/**
	 * @param string $id Navigation identity like "nav-cars".
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}

	/**
	 * Initializes the navigation from URI.
	 */
	public function initFromUri()
	{
		$navParams = array();

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();

		if(($value = $request->getQuery($this->id)) !== null)
		{
			//parameters are in the QUERY_STRING
			$params = explode("-", $value);
			for($i = 0, $n = count($params); $i < $n; $i += 2)
			{
				$navParams[$params[$i]] = $params[$i+1];
			}
		}
		else
		{
			//probably parametrs are in the SEF URI
			$matches = array();
			if(preg_match("'/".preg_quote($this->id, "'")."/page-([\\d]+|all)+(/size-([\\d]+))?'", $request->getRequestUri(), $matches))
			{
				$navParams["page"] = $matches[1];
				if(isset($matches[3]))
				{
					$navParams["size"] = $matches[3];
				}
			}
		}

		if(isset($navParams["size"]))
		{
			//set page size from user request
			if(in_array($navParams["size"], $this->pageSizes))
			{
				$this->setPageSize((int)$navParams["size"]);
			}
		}

		if(isset($navParams["page"]))
		{
			if($navParams["page"] == "all" && $this->allowAll == true)
			{
				//show all records in one page
				$this->allRecords = true;
			}
			else
			{
				//set current page within boundaries
				$currentPage = (int)$navParams["page"];
				if($currentPage >= 1)
				{
					if($this->recordCount !== null)
					{
						$maxPage = $this->getPageCount();
						if($currentPage > $maxPage)
						{
							$currentPage = $maxPage;
						}
					}
					$this->setCurrentPage($currentPage);
				}
			}
		}
	}

	/**
	 * Returns number of pages or 0 if recordCount is not set.
	 * @return int
	 */
	public function getPageCount()
	{
		if($this->allRecords)
		{
			return 1;
		}
		$maxPages = floor($this->recordCount/$this->pageSize);
		if(($this->recordCount % $this->pageSize) > 0)
		{
			$maxPages++;
		}
		return $maxPages;
	}

	/**
	 * @param int $n Page size.
	 * @return $this
	 */
	public function setPageSize($n)
	{
		$this->pageSize = (int)$n;
		return $this;
	}

	/**
	 * @param int $n The current page number.
	 * @return $this
	 */
	public function setCurrentPage($n)
	{
		$this->currentPage = (int)$n;
		return $this;
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
		return 1;
	}

	/**
	 * @param bool $mode Allows to show all records, yes or no.
	 * @return $this
	 */
	public function allowAllRecords($mode)
	{
		$this->allowAll = (bool)$mode;
		return $this;
	}

	/**
	 * @param int $n Number of records (to calculate number of pages).
	 * @return $this
	 */
	public function setRecordCount($n)
	{
		$this->recordCount = (int)$n;
		return $this;
	}

	/**
	 * Returns number of records.
	 * @return int|null
	 */
	public function getRecordCount()
	{
		return $this->recordCount;
	}

	/**
	 * This controls which sizes are available via user interface.
	 * @param array $sizes Array of integers.
	 * @return $this
	 */
	public function setPageSizes(array $sizes)
	{
		$this->pageSizes = $sizes;
		return $this;
	}

	/**
	 * Returns allowed page sizes.
	 * @return array
	 */
	public function getPageSizes()
	{
		return $this->pageSizes;
	}

	/**
	 * Returns "formal" page size.
	 * @return int
	 */
	public function getPageSize()
	{
		return $this->pageSize;
	}

	/**
	 * Returns navigation ID.
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
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
		return ($this->getCurrentPage() - 1) * $this->pageSize;
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
		return $this->pageSize;
	}

	/**
	 * Returns true if all the records are shown in one page.
	 * @return bool
	 */
	public function allRecordsShown()
	{
		return $this->allRecords;
	}

	/**
	 * Returns true if showing all records is allowed.
	 * @return bool
	 */
	public function allRecordsAllowed()
	{
		return $this->allowAll;
	}

	/**
	 * Returns an URI with navigation parameters compatible with initFromUri().
	 * @param Web\Uri $uri
	 * @param bool $sef SEF mode.
	 * @param string $page Page number.
	 * @param string $size Page size.
	 * @return Web\Uri
	 */
	public function addParams(Web\Uri $uri, $sef, $page, $size = null)
	{
		if($sef == true)
		{
			$this->clearParams($uri, $sef);

			$path = $uri->getPath();
			$pos = strrpos($path, "/");
			$path = substr($path, 0, $pos+1).$this->id."/page-".$page."/".($size !== null? "size-".$size."/" : '').substr($path, $pos+1);
			$uri->setPath($path);
		}
		else
		{
			$uri->addParams(array($this->id => "page-".$page.($size !== null? "-size-".$size : '')));
		}
		return $uri;
	}

	/**
	 * Clears an URI from navigation parameters and returns it.
	 * @param Web\Uri $uri
	 * @param bool $sef SEF mode.
	 * @return Web\Uri
	 */
	public function clearParams(Web\Uri $uri, $sef)
	{
		if($sef == true)
		{
			$path = $uri->getPath();
			$path = preg_replace("'/".preg_quote($this->id, "'")."/page-([\\d]+|all)+(/size-([\\d]+))?'", "", $path);
			$uri->setPath($path);
		}
		else
		{
			$uri->deleteParams(array($this->id));
		}
		return $uri;
	}
}
