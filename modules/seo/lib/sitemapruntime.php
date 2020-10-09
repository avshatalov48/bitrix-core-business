<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */

namespace Bitrix\Seo;

use Bitrix\Main\Entity;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Path;

/**
 * Temporary data structure
 * Class SitemapRuntimeTable
 * @package Bitrix\Seo
 */
class SitemapRuntimeTable extends Entity\DataManager
{
	const ACTIVE = 'Y';
	const INACTIVE = 'N';
	
	const ITEM_TYPE_DIR = 'D';
	const ITEM_TYPE_FILE = 'F';
	const ITEM_TYPE_IBLOCK = 'I';
	const ITEM_TYPE_SECTION = 'S';
	const ITEM_TYPE_ELEMENT = 'E';
	const ITEM_TYPE_FORUM = 'G';
	const ITEM_TYPE_TOPIC = 'T';
	
	const PROCESSED = 'Y';
	const UNPROCESSED = 'N';
	
	public static function getFilePath()
	{
		return __FILE__;
	}
	
	public static function getTableName()
	{
		return 'b_seo_sitemap_runtime';
	}
	
	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'PID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'PROCESSED' => array(
				'data_type' => 'boolean',
				'values' => array(self::PROCESSED, self::UNPROCESSED),
			),
			'ITEM_PATH' => array(
				'data_type' => 'string',
			),
			'ITEM_ID' => array(
				'data_type' => 'integer',
			),
			'ITEM_TYPE' => array(
				'data_type' => 'enum',
				'values' => array(
					self::ITEM_TYPE_DIR,
					self::ITEM_TYPE_FILE,
					self::ITEM_TYPE_IBLOCK,
					self::ITEM_TYPE_SECTION,
					self::ITEM_TYPE_ELEMENT,
					self::ITEM_TYPE_FORUM,
					self::ITEM_TYPE_TOPIC,
				),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array(self::INACTIVE, self::ACTIVE),
			),
			'ACTIVE_ELEMENT' => array(
				'data_type' => 'boolean',
				'values' => array(self::INACTIVE, self::ACTIVE),
			),
		);
		
		return $fieldsMap;
	}
	
	
	public static function clearByPid($PID)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$query = $connection->query("
DELETE
FROM " . self::getTableName() . "
WHERE PID='" . intval($PID) . "'
");
	}
}


class SitemapRuntime
	extends SitemapFile
{
	const PROGRESS_WIDTH = 500;
	
	protected $PID = 0;
	private $originalFile = NULL;
	
	public function __construct($PID, $fileName, $arSettings)
	{
		$this->PID = $PID;
		
		if ($this->partFile == '')
		{
			$this->partFile = $fileName;
		}

//		normalize slashes
		$fileName = Path::normalize($fileName);
//		divide directory and path tp correctly add prefix
		$lastSlashPosition = mb_strrpos($fileName, "/");
		$fileDirectory = '';
		if ($lastSlashPosition !== false)
		{
			$fileDirectory = mb_substr($fileName, 0, $lastSlashPosition + 1);
			$fileName = mb_substr($fileName, $lastSlashPosition + 1);
		}
		
		parent::__construct($fileDirectory . $this->getPrefix() . $fileName, $arSettings);
	}
	
	/**
	 * Recreate file with same settings to new part
	 *
	 * @param string $fileName
	 */
	protected function reInit($fileName)
	{
		$this->__construct($this->PID, $fileName, $this->settings);
	}
	
	public function putSitemapContent(SitemapFile $sitemapFile)
	{
//		always write in new empty file - this is necessary
		if ($this->isExists())
			$this->delete();
		
		if ($sitemapFile->isExists())
		{
			$this->putContents($sitemapFile->getContents());
			$this->partChanged = true;
			$this->footerClosed = true;
		}
		else
		{
			$this->addHeader();
		}
	}
	
	
	public function setOriginalFile(SitemapFile $sitemapFile)
	{
		if (isset($sitemapFile))
			$this->originalFile = $sitemapFile;
	}
	
	/**
	 * Overwrite parent method to creating temp-files and correctly work with multipart
	 * Appends new IBlock entry to the existing finished sitemap
	 *
	 * @param string $url IBlock entry URL.
	 * @param string $modifiedDate IBlock entry modify timestamp.
	 *
	 * @return void
	 */
	public function appendIBlockEntry($url, $modifiedDate)
	{
//		if not set original file - to use as common sitemap file
		if(!$this->originalFile)
		{
			parent::appendIBlockEntry($url, $modifiedDate);
			return;
		}
		
		if ($this->originalFile->isExists())
		{
//			move sitemapfile to end, find name of last part
			while ($this->originalFile->isSplitNeeded())
			{
				$filename = $this->originalFile->split();
			}

//			if part was changed - create new runtime part file
			if (isset($filename) && $filename)
				$this->reInit($filename);
			
			$this->putSitemapContent($this->originalFile);
			$this->appendEntry(array(
				'XML_LOC' => $this->settings['PROTOCOL'] . '://' . \CBXPunycode::toASCII($this->settings['DOMAIN'], $e = NULL) . $url,
				'XML_LASTMOD' => date('c', $modifiedDate - \CTimeZone::getOffset()),
			));
		}
		else
		{
			$this->addHeader();
			$this->addIBlockEntry($url, $modifiedDate);
			$this->addFooter();
		}
	}
	
	/**
	 * Rename runtime file to original name. If runtime have part - rename them all
	 */
	public function finish()
	{
		foreach ($this->partList as $key => $partName)
		{
			$f = new File(Path::combine($this->getDirectoryName(), $partName));
			$f->rename(str_replace($this->getPrefix(), '', $f->getPath()));
			$this->partList[$key] = $f->getName();
		}
		
		if ($this->isCurrentPartNotEmpty())
		{
			if (!$this->footerClosed)
				$this->addFooter();
			$this->rename(str_replace($this->getPrefix(), '', $this->getPath()));
		}
	}
	
	protected function getPrefix()
	{
		return '~' . $this->PID;
	}
	
	public static function showProgress($text, $title, $v)
	{
		$v = $v >= 0 ? $v : 0;
		
		if ($v < 100)
		{
			$msg = new \CAdminMessage(array(
				"TYPE" => "PROGRESS",
				"HTML" => true,
				"MESSAGE" => $title,
				"DETAILS" => "#PROGRESS_BAR#<div style=\"width: " . self::PROGRESS_WIDTH . "px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-top: 20px;\">" . Converter::getHtmlConverter()->encode($text) . "</div>",
				"PROGRESS_TOTAL" => 100,
				"PROGRESS_VALUE" => $v,
				"PROGRESS_TEMPLATE" => '#PROGRESS_PERCENT#',
				"PROGRESS_WIDTH" => self::PROGRESS_WIDTH,
			));
		}
		else
		{
			$msg = new \CAdminMessage(array(
				"TYPE" => "OK",
				"MESSAGE" => $title,
				"DETAILS" => $text,
			));
		}
		
		return $msg->show();
	}
}