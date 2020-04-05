<?php
namespace Bitrix\Landing\Zip\Nginx;

use \Bitrix\Main\Text\Encoding;
use \Bitrix\Main\Config\Option;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Manager;

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
	 * File id.
	 * @var string
	 */
	protected $fileId;

	/**
	 * File size in entry.
	 * @var string
	 */
	protected $size;

	/**
	 * Entry constructor.
	 */
	protected function __construct()
	{
		$this->fileId = 0;
	}

	/**
	 * Creates Entry from file path.
	 * @param string $filePath File id from b_file.
	 * @return static
	 */
	public static function createFromFilePath($filePath)
	{
		$fileArray = \CFile::MakeFileArray($filePath);

		if ($fileArray)
		{
			return self::createFromFile([
				'ID' => 0,
				'ORIGINAL_NAME' => $fileArray['name'],
				'FILE_SIZE' => $fileArray['size'],
				'SRC' => substr(
					$fileArray['tmp_name'],
					strlen(Manager::getDocRoot())
				),
			]);
		}

		return null;
	}

	/**
	 * Creates Entry from file id (from b_file).
	 * @param int $fileId File id from b_file.
	 * @return static
	 */
	public static function createFromFileId($fileId)
	{
		$fileArray = File::getFileArray($fileId);

		if (
			!$fileArray ||
			empty($fileArray['SRC'])
		)
		{
			return null;
		}

		return self::createFromFile($fileArray);
	}

	/**
	 * Creates Entry from file array.
	 * @param array $fileArray File id from b_file.
	 * @return static
	 */
	protected static function createFromFile(array $fileArray)
	{
		$zipEntry = new static;
		$zipEntry->name = $fileArray['ORIGINAL_NAME'];
		$zipEntry->fileId = $fileArray['ID'];
		$zipEntry->size = $fileArray['FILE_SIZE'];

		$fromClouds = false;
		$filename = $fileArray['SRC'];

		if (isset($fileArray['HANDLER_ID']) && !empty($fileArray['HANDLER_ID']))
		{
			$fromClouds = true;
		}

		unset($fileArray);

		if ($fromClouds)
		{
			$filename = preg_replace('~^(http[s]?)(\://)~i', '\\1.' , $filename);
			$cloudUploadPath = Option::get(
				'main',
				'bx_cloud_upload',
				'/upload/bx_cloud_upload/'
			);
			$zipEntry->path = $cloudUploadPath . $filename;
			unset($cloudUploadPath);
		}
		else
		{
			$zipEntry->path = self::encodeUrn(
				Encoding::convertEncoding($filename, LANG_CHARSET, 'UTF-8')
			);
		}
		unset($filename);

		return $zipEntry;
	}

	/**
	 * Encodes uri: explodes uri by / and encodes in UTF-8 and rawurlencodes.
	 * @param string $uri Uri.
	 * @return string
	 */
	protected function encodeUrn($uri)
	{
		$result = '';
		$parts = preg_split(
			"#(://|:\\d+/|/|\\?|=|&)#", $uri, -1, PREG_SPLIT_DELIM_CAPTURE
		);

		foreach ($parts as $i => $part)
		{
			$part = Manager::getApplication()->convertCharset(
				$part,
				LANG_CHARSET,
				'UTF-8'
			);
			$result .= ($i % 2)
				? $part
				: rawurlencode($part);
		}
		unset($parts, $i, $part);

		return $result;
	}

	/**
	 * Returns representation zip entry as string.
	 * @return string
	 */
	public function __toString()
	{
		$name = Encoding::convertEncoding(
			$this->name,
			LANG_CHARSET,
			'UTF-8'
		);
		return "- {$this->size} {$this->path} /upload/{$this->fileId}/{$name}";
	}
}