<?php
namespace Bitrix\Main\Engine\Response\Zip;

use \Bitrix\Main\Application;
use \Bitrix\Main\Text\Encoding;
use \Bitrix\Main\Config\Option;

class ArchiveEntry
{
	/**
	 * File name in entry.
	 * @var string
	 */
	protected $name;

	/**
	 * File path in entry.
	 * @var string
	 */
	protected $path;

	/**
	 * File size in entry.
	 * @var int
	 */
	protected $size;

	/**
	 * Crc32 for file
	 * @var string
	 */
	protected $crc32;

	/**
	 * Entry constructor.
	 */
	protected function __construct()
	{}

	/**
	 * Gets name of current file.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return ArchiveEntry
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Gets full path of current file.
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Gets size of current file.
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @return string
	 */
	public function getCrc32()
	{
		return $this->crc32;
	}

	/**
	 * Creates Entry from file path.
	 * @param string $filePath File id from b_file.
	 * @param string|null $name
	 * @return static
	 */
	public static function createFromFilePath($filePath, $name = null)
	{
		$fileArray = \CFile::MakeFileArray($filePath);

		if ($fileArray)
		{
			return self::createFromFile([
				'ID' => 0,
				'ORIGINAL_NAME' => $fileArray['name'],
				'FILE_SIZE' => $fileArray['size'],
				'SRC' => mb_substr($fileArray['tmp_name'], mb_strlen(self::getDocRoot())),
			], $name);
		}

		return null;
	}

	/**
	 * Creates Entry from file id (from b_file).
	 * @param int $fileId File id from b_file.
	 * @param string $moduleId Module id for strong restriction.
	 * @return static
	 */
	public static function createFromFileId($fileId, $moduleId = null)
	{
		$fileArray = \CFile::getFileArray($fileId);

		// check file exists
		if (!$fileArray || empty($fileArray['SRC']))
		{
			return null;
		}

		// check module restriction
		if (
			$moduleId !== null &&
			(
				!isset($fileArray['MODULE_ID']) ||
				$fileArray['MODULE_ID'] != $moduleId
			)
		)
		{
			return null;
		}

		return self::createFromFile($fileArray);
	}

	/**
	 * Creates Entry from file array.
	 * @param array $fileArray File id from b_file.
	 * @param string|null $name
	 * @return static
	 */
	protected static function createFromFile(array $fileArray, $name = null)
	{
		$zipEntry = new static;
		$zipEntry->setName($name?: $fileArray['ORIGINAL_NAME']);
		$zipEntry->size = (int)$fileArray['FILE_SIZE'];

		if (empty($fileArray['SRC']))
		{
			$fileArray['SRC'] = \CFile::getFileSrc($fileArray);
		}

		$fromClouds = false;
		$filename = $fileArray['SRC'];
		if (isset($fileArray['HANDLER_ID']) && !empty($fileArray['HANDLER_ID']))
		{
			$fromClouds = true;
		}

		if ($fromClouds)
		{
			$filename = preg_replace('~^(http[s]?)(\://)~i', '\\1.' , $filename);
			$cloudUploadPath = Option::get(
				'main',
				'bx_cloud_upload',
				'/upload/bx_cloud_upload/'
			);
			$zipEntry->path = $cloudUploadPath . $filename;
		}
		else
		{
			$zipEntry->path = self::encodeUrn(
				Encoding::convertEncoding($filename, LANG_CHARSET, 'UTF-8')
			);
		}

		return $zipEntry;
	}

	/**
	 * Get main instance of \CMain.
	 * @return \CMain
	 */
	protected static function getApplication()
	{
		return $GLOBALS['APPLICATION'];
	}

	/**
	 * Famous document root.
	 * @return string
	 */
	protected static function getDocRoot()
	{
		static $docRoot = null;

		if ($docRoot === null)
		{
			$context = Application::getInstance()->getContext();
			$server = $context->getServer();
			$docRoot = $server->getDocumentRoot();
		}

		return $docRoot;
	}

	/**
	 * Encodes uri: explodes uri by / and encodes in UTF-8 and rawurlencodes.
	 * @param string $uri Uri.
	 * @return string
	 */
	protected static function encodeUrn($uri)
	{
		$result = '';
		$parts = preg_split(
			"#(://|:\\d+/|/|\\?|=|&)#", $uri, -1, PREG_SPLIT_DELIM_CAPTURE
		);

		foreach ($parts as $i => $part)
		{
			$part = self::getApplication()->convertCharset(
				$part,
				LANG_CHARSET,
				'UTF-8'
			);
			$result .= ($i % 2)
				? $part
				: rawurlencode($part);
		}

		return $result;
	}

	/**
	 * Returns representation zip entry as string.
	 * @return string
	 */
	public function __toString()
	{
		$crc32 = $this->getCrc32()?: '-';
		$name = Encoding::convertEncoding(
			$this->getName(),
			LANG_CHARSET,
			'UTF-8'
		);

		return "{$crc32} {$this->getSize()} {$this->getPath()} {$name}";
	}
}