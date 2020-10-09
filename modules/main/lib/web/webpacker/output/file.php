<?php

namespace Bitrix\Main\Web\WebPacker\Output;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\WebPacker;
use Bitrix\Main\Web\MimeType;

/**
 * Class File
 *
 * @package Bitrix\Main\Web\WebPacker\Output
 */
class File extends Base
{
	protected $id;
	protected $moduleId;
	protected $uploadDir;
	protected $dir;
	protected $name;
	protected $type;
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

		$content = $builder->stringify();
		$id = $this->saveFile($content);

		$result = (new Result())->setId($id)->setContent($content);
		if (!$id)
		{
			$result->addError(new Error('Empty file ID.'));
		}

		return $result;
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
	 * Get file ID.
	 *
	 * @return int|null
	 */
	public function getId()
	{
		return $this->id;
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
	 * Set upload directory.
	 *
	 * @param string $uploadDir Upload directory.
	 * @return $this
	 */
	public function setUploadDir($uploadDir)
	{
		$this->uploadDir = $uploadDir;
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
	 * Set type.
	 *
	 * @param string $type Content type.
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;
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

		$uploadDir = $this->uploadDir ?: Option::get("main", "upload_dir", "upload");
		return WebPacker\Builder::getDefaultSiteUri() .
			'/' . $uploadDir .
			'/' . $file['SUBDIR'] .
			'/' . $file['FILE_NAME'];
	}

	protected function saveFile($content)
	{
		$this->remove();

		$type = $this->type;
		if (!$type && $this->name)
		{
			$type = static::getMimeTypeByFileName($this->name);
		}

		$fileArray = [
			'MODULE_ID' => $this->moduleId,
			'name' => $this->name,
			'content' => $content
		];

		if ($type)
		{
			$fileArray['type'] = $type;
		}

		$this->id = \CFile::SaveFile(
			$fileArray,
			$this->moduleId,
			false,
			false,
			$this->dir ?: ''
		);

		return $this->id;
	}

	protected static function getMimeTypeByFileName($fileName)
	{
		$extension = mb_strtolower(getFileExtension($fileName));
		$list = MimeType::getMimeTypeList();
		if (isset($list[$extension]))
		{
			return $list[$extension];
		}

		return 'text/plain';
	}
}