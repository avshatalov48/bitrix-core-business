<?php

namespace Bitrix\Main\Web\WebPacker\Output;

use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\WebPacker;

/**
 * Class File
 *
 * @package Bitrix\Main\Web\WebPacker\Output
 */
class File extends Base
{
	protected $id;
	protected $moduleId;
	protected $dir;
	protected $name;
	protected $content;

	/**
	 * Remove by ID.
	 *
	 * @param int $id ID.
	 * @return void
	 */
	public static function removeById($id)
	{
		\CFile::Delete($id);
	}

	/**
	 * Output.
	 *
	 * @param WebPacker\Builder $builder Module.
	 * @return Result
	 */
	public function output(WebPacker\Builder $builder)
	{
		if (!$this->moduleId)
		{
			throw new SystemException('Module ID is empty.');
		}
		if (!$this->name)
		{
			throw new SystemException('File name is empty.');
		}
		if (!$this->dir)
		{
			throw new SystemException('File directory is empty.');
		}

		$content = $builder->stringify();
		$id = $this->saveFile($content);

		return (new Result())->setId($id)->setContent($content);
	}

	/**
	 * Set content.
	 *
	 * @param string $content Content.
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Set file ID.
	 *
	 * @param int $id File ID.
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Set module ID.
	 *
	 * @param string $moduleId Bitrix module ID.
	 * @return $this
	 */
	public function setModuleId($moduleId)
	{
		$this->moduleId = $moduleId;
		return $this;
	}

	/**
	 * Set directory.
	 *
	 * @param string $dir Directory.
	 * @return $this
	 */
	public function setDir($dir)
	{
		$this->dir = $dir;
		return $this;
	}

	/**
	 * Set name.
	 *
	 * @param string $name Name.
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Remove.
	 *
	 * @return void
	 */
	public function remove()
	{
		if (!$this->id)
		{
			return;
		}

		static::removeById($this->id);
	}

	/**
	 * Get uri.
	 *
	 * @return string
	 */
	public function getUri()
	{
		$uri = '';
		if (!$this->id)
		{
			return $uri;
		}

		$file = \CFile::getByID($this->id)->fetch();
		if (!$file)
		{
			return $uri;
		}

		$uri = $file['~src'];
		if ($uri)
		{
			return $uri;
		}

		$uploadDir = Option::get("main", "upload_dir", "upload");
		return WebPacker\Builder::getDefaultSiteUri() .
			'/' . $uploadDir .
			'/' . $file['SUBDIR'] .
			'/' . $file['FILE_NAME'];
	}

	protected function saveFile($content)
	{
		$this->remove();

		$fileArray = [
			'MODULE_ID' => $this->moduleId,
			'name' => $this->name,
			'content' => $content
		];

		$this->id = \CFile::SaveFile(
			$fileArray,
			$this->moduleId,
			false,
			false,
			$this->dir
		);

		return $this->id;
	}
}