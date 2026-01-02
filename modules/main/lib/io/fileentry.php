<?php

namespace Bitrix\Main\IO;

abstract class FileEntry extends FileSystemEntry
{
	public function __construct($path, $siteId = null)
	{
		parent::__construct($path, $siteId);
	}

	public function getExtension()
	{
		return Path::getExtension($this->path);
	}

	abstract public function getContents();

	abstract public function putContents($data);

	abstract public function getSize();

	abstract public function isWritable();

	abstract public function isReadable();

	abstract public function readFile();

	/**
	 * @return mixed
	 * @deprecated Use getSize() instead
	 */
	public function getFileSize()
	{
		return $this->getSize();
	}

	public function isDirectory()
	{
		return false;
	}

	public function isFile()
	{
		return true;
	}

	public function isLink()
	{
		return false;
	}
}
