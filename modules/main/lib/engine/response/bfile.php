<?php

namespace Bitrix\Main\Engine\Response;


use Bitrix\Main;

class BFile extends File
{
	protected $file;

	public function __construct(array $file, $name = null)
	{
		$this->file = $file;
		if ($name === null)
		{
			$name = $this->file['ORIGINAL_NAME'];
		}

		parent::__construct(null, $name, $this->file['CONTENT_TYPE']);
	}

	public static function createByFileData(array $file, $name = null)
	{
		return new self($file, $name);
	}

	public static function createByFileId($fileId, $name = null)
	{
		$file = \CFile::getFileArray($fileId);
		if (!$file)
		{
			throw new Main\ObjectNotFoundException("Could not find file ({$fileId})");
		}

		return new self($file, $name);
	}

	/**
	 * @return array
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return array|bool|null
	 */
	protected function prepareFile()
	{
		return $this->getFile();
	}

	protected function prepareOptions()
	{
		return [
			'force_download' => !$this->isShowInline(),
			'cache_time' => $this->getCacheTime(),
			'attachment_name' => $this->getName(),
			'content_type' => $this->getContentType(),
		];
	}
}