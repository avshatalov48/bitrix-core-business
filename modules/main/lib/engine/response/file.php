<?php

namespace Bitrix\Main\Engine\Response;


use Bitrix\Main;

class File extends Main\HttpResponse
{
	protected $path;
	/**
	 * @var null
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $contentType;
	protected $showInline = false;
	protected $cacheTime = 0;

	public function __construct($path, $name = null, $contentType = 'application/octet-stream')
	{
		parent::__construct();

		$this->setPath($path);
		$this->setContentType($contentType);

		if ($name === null)
		{
			$name = bx_basename($path);
		}

		$this->setName($name);
	}

	/**
	 * @return mixed
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param mixed $path
	 *
	 * @return File
	 */
	public function setPath($path)
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * @return null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param null $name
	 *
	 * @return File
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * @param string $contentType
	 *
	 * @return File
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isShowInline()
	{
		return $this->showInline;
	}

	/**
	 * @param bool $enabled
	 *
	 * @return File
	 */
	public function showInline($enabled)
	{
		$this->showInline = (bool)$enabled;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCacheTime()
	{
		return $this->cacheTime;
	}

	/**
	 * @param int $cacheTime
	 *
	 * @return File
	 */
	public function setCacheTime($cacheTime)
	{
		$this->cacheTime = (int)$cacheTime;

		return $this;
	}

	protected function prepareOptions()
	{
		return [
			'force_download' => !$this->isShowInline(),
			'cache_time' => $this->getCacheTime(),
			'attachment_name' => $this->getName(),
			'content_type' => $this->getContentType(),
			'fast_download'	=> false,
		];
	}

	protected function prepareFile()
	{
		$path = new \Bitrix\Main\IO\File($this->getPath());

		return \CFile::makeFileArray($path->getPhysicalPath());
	}

	public function send()
	{
		\CFile::viewByUser($this->prepareFile(), $this->prepareOptions());
	}
}