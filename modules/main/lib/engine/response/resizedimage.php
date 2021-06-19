<?php

namespace Bitrix\Main\Engine\Response;


use Bitrix\Main;

class ResizedImage extends BFile
{
	protected $showInline = true;
	protected $width;
	protected $height;
	protected $resizeType = BX_RESIZE_IMAGE_EXACT;
	protected $filters;

	public function __construct(array $file, $width, $height, $name = null)
	{
		parent::__construct($file, $name);

		$this->setWidth($width);
		$this->setHeight($height);
	}

	public static function createByImageId($imageId, $width, $height, $name = null)
	{
		$imageData = \CFile::getFileArray($imageId);
		if (!$imageData)
		{
			throw new Main\ObjectNotFoundException("Could not find file");
		}

		return new self($imageData, $width, $height, $name);
	}

	public static function createByImageData(array $imageData, $width, $height, $name = null)
	{
		return new self($imageData, $width, $height, $name);
	}

	/**
	 * @return mixed
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param mixed $width
	 *
	 * @return ResizedImage
	 */
	public function setWidth($width)
	{
		$this->width = $width;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param mixed $height
	 *
	 * @return ResizedImage
	 */
	public function setHeight($height)
	{
		$this->height = $height;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getResizeType()
	{
		return $this->resizeType;
	}

	/**
	 * @param int $resizeType
	 *
	 * @return ResizedImage
	 */
	public function setResizeType($resizeType)
	{
		$this->resizeType = $resizeType;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @param mixed $filters
	 *
	 * @return ResizedImage
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getImage()
	{
		return $this->file;
	}

	protected function prepareFile()
	{
		$file = parent::prepareFile();

		$tmpFile = \CFile::resizeImageGet(
			$file,
			['width' => $this->getWidth(), 'height' => $this->getHeight()],
			$this->getResizeType(),
			true,
			$this->getFilters(),
			true
		);

		$file['FILE_SIZE'] = $tmpFile['size'];
		$file['SRC'] = $tmpFile['src'];

		return $file;
	}
}