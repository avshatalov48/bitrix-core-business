<?php
namespace Bitrix\Main\IO;

abstract class FileEntry
	extends FileSystemEntry
{
	public function __construct($path, $siteId = null)
	{
		parent::__construct($path, $siteId);
	}

	public function getExtension()
	{
		return Path::getExtension($this->path);
	}

	public abstract function getContents();
	public abstract function putContents($data);
	public abstract function getSize();
	public abstract function isWritable();
	public abstract function isReadable();
	public abstract function readFile();

	/**
	 * @deprecated Use getSize() instead
	 * @return mixed
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
