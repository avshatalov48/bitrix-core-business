<?php

namespace Bitrix\Main\UI\Viewer;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\UI\Viewer\Transformation\Transformation;
use Bitrix\Main\UI\Viewer\Transformation\TransformerManager;
use Bitrix\Main\Web\Json;

class ItemAttributes
{
	private const FAKE_FILEDATA = [
		'ID' => -1,
		'CONTENT_TYPE' => 'application/octet-stream',
	];

	/**
	 * @var
	 */
	protected $fileData;
	/**
	 * @var array
	 */
	protected $attributes = [];
	/**
	 * @var array
	 */
	protected $actions = [];
	/**
	 * @var
	 */
	protected $sourceUri;
	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var array
	 */
	protected static $renderClassByContentType = [];

	/**
	 * ItemAttributes constructor.
	 *
	 * @param $fileData
	 * @param $sourceUri
	 * @param array $options
	 */
	private function __construct($fileData, $sourceUri, array $options = [])
	{
		$this->fileData = $fileData;
		$this->sourceUri = $sourceUri;
		$this->options = $options;

		$this->setDefaultAttributes();
	}

	protected function setDefaultAttributes()
	{
		$this
			->setAttribute('data-viewer')
			->setViewerType(static::getViewerTypeByFile($this->fileData))
			->setAttribute('data-src', $this->sourceUri)
		;
	}

	/**
	 * @param $fileId
	 * @param $sourceUri
	 *
	 * @return static
	 * @throws ArgumentException
	 */
	public static function buildByFileId($fileId, $sourceUri)
	{
		$fileData = \CFile::getByID($fileId)->fetch();
		if (!$fileData)
		{
			throw new ArgumentException('Invalid fileId', 'fileId');
		}

		return new static($fileData, $sourceUri);
	}

	/**
	 * @param array $fileData
	 * @param $sourceUri
	 *
	 * @return static
	 * @throws ArgumentException
	 */
	public static function buildByFileData(array $fileData, $sourceUri)
	{
		if (empty($fileData['ID']))
		{
			throw new ArgumentException('Invalid file data', 'fileData');
		}

		return new static($fileData, $sourceUri);
	}

	public static function tryBuildByFileData(array $fileData, $sourceUri)
	{
		try
		{
			return static::buildByFileData($fileData, $sourceUri);
		}
		catch (ArgumentException $exception)
		{
			if ($exception->getParameter() == 'fileData')
			{
				return static::buildAsUnknownType($sourceUri);
			}

			throw $exception;
		}
	}

	/**
	 * @param $sourceUri
	 *
	 * @return static
	 */
	public static function buildAsUnknownType($sourceUri)
	{
		return new static(self::FAKE_FILEDATA, $sourceUri);
	}

	protected static function isFakeFileData(array $fileData): bool
	{
		return
			($fileData['ID'] === self::FAKE_FILEDATA['ID'])
			&& ($fileData['CONTENT_TYPE'] === self::FAKE_FILEDATA['CONTENT_TYPE'])
		;
	}

	public static function tryBuildByFileId($fileId, $sourceUri)
	{
		try
		{
			return static::buildByFileId($fileId, $sourceUri);
		}
		catch (ArgumentException $exception)
		{
			if ($exception->getParameter() == 'fileId')
			{
				return static::buildAsUnknownType($sourceUri);
			}

			throw $exception;
		}
	}

	/**
	 * @param $title
	 *
	 * @return $this
	 */
	public function setTitle($title)
	{
		return $this->setAttribute('data-title', htmlspecialcharsbx($title));
	}

	public function setTypeClass(string $class)
	{
		return $this->setAttribute('data-viewer-type-class', htmlspecialcharsbx($class));
	}

	public function setViewerType(string $type): self
	{
		return $this->setAttribute('data-viewer-type', $type);
	}

	public function getTypeClass()
	{
		return $this->getAttribute('data-viewer-type-class');
	}

	/**
	 * @param $id
	 *
	 * @return $this
	 */
	public function setGroupBy($id)
	{
		return $this->setAttribute('data-viewer-group-by', htmlspecialcharsbx($id));
	}

	/**
	 * @return $this
	 */
	public function unsetGroupBy()
	{
		return $this->unsetAttribute('data-viewer-group-by');
	}

	/**
	 * @return string|null
	 */
	public function getGroupBy()
	{
		return $this->getAttribute('data-viewer-group-by');
	}

	/**
	 * @param array $action
	 *
	 * @return $this
	 */
	public function addAction(array $action)
	{
		$this->actions[] = $action;

		return $this;
	}

	public function clearActions(): self
	{
		$this->actions = [];

		return $this;
	}

	/**
	 * @return array
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * @param string $extension
	 * @return ItemAttributes
	 */
	public function setExtension($extension)
	{
		return $this->setAttribute('data-viewer-extension', $extension);
	}

	/**
	 * @return string|null
	 */
	public function getExtension()
	{
		return $this->getAttribute('data-viewer-extension');
	}

	/**
	 * @return mixed|null
	 */
	public function getViewerType()
	{
		if (!$this->issetAttribute('data-viewer-type'))
		{
			$this->setViewerType(static::getViewerTypeByFile($this->fileData));
		}

		return $this->getAttribute('data-viewer-type');
	}

	/**
	 * @param $name
	 * @param $value
	 *
	 * @return $this
	 */
	public function setAttribute($name, $value = null)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * @param $name
	 *
	 * @return $this
	 */
	public function unsetAttribute($name)
	{
		unset($this->attributes[$name]);

		return $this;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function issetAttribute($name)
	{
		return isset($this->attributes[$name]);
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function getAttribute($name)
	{
		if (isset($this->attributes[$name]))
		{
			return $this->attributes[$name];
		}

		return null;
	}

	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @param array $fileArray
	 *
	 * @return mixed|string
	 * @throws \ReflectionException
	 */
	protected static function getViewerTypeByFile(array $fileArray)
	{
		$contentType = $fileArray['CONTENT_TYPE'];
		$originalName = $fileArray['ORIGINAL_NAME'] ?? null;

		if (isset(static::$renderClassByContentType[$contentType]))
		{
			$renderClass = static::$renderClassByContentType[$contentType];
			if ($renderClass::getSizeRestriction() === null)
			{
				return $renderClass::getJsType();
			}
		}

		$previewManager = new PreviewManager();
		$renderClass = $previewManager->getRenderClassByFile([
			'contentType' => $contentType,
			'originalName' => $originalName,
			'size' => $fileArray['FILE_SIZE'] ?? null,
		]);

		if ($renderClass === Renderer\Stub::class)
		{
			$transformerManager = new TransformerManager();
			if ($transformerManager->isAvailable())
			{
				/** @var Transformation $transformationClass */
				$transformation = $transformerManager->buildTransformationByFile($fileArray);
				if ($transformation)
				{
					$contentType = $transformation->getOutputContentType();
					$renderClass = $previewManager->getRenderClassByFile([
						'contentType' => $contentType,
						'originalName' => $originalName,
					]);
				}
			}
		}

		if ($renderClass !== Renderer\RestrictedBySize::class)
		{
			static::$renderClassByContentType[$fileArray['CONTENT_TYPE']] = $renderClass;
		}

		return $renderClass::getJsType();
	}

	/**
	 * @return string
	 */
	public function toString()
	{
		return (string)$this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$string = '';
		foreach ($this->attributes as $key => $value)
		{
			if (is_int($key))
			{
				$string .= "{$value} ";
			}
			else
			{
				$value = htmlspecialcharsbx($value);
				$string .= "{$key}=\"{$value}\" ";
			}
		}

		if ($this->actions)
		{
			$string .= "data-actions='" . htmlspecialcharsbx(Json::encode($this->actions)) . "'";
		}

		return $string;
	}

	/**
	 * Convert structure to array which we can use in js (node.dataset).
	 * @return array
	 */
	public function toDataSet()
	{
		$likeDataSet = [];
		foreach ($this->attributes as $key => $value)
		{
			if (is_int($key))
			{
				$likeDataSet[$this->convertKeyToDataSet($value)] = null;
			}
			else
			{
				$likeDataSet[$this->convertKeyToDataSet($key)] = $value;
			}
		}

		if ($this->actions)
		{
			$likeDataSet[$this->convertKeyToDataSet('data-actions')] = Json::encode($this->actions);
		}

		return $likeDataSet;
	}

	protected function convertKeyToDataSet($key)
	{
		$key = str_replace('data-', '', $key);
		$key = str_replace('-', ' ', mb_strtolower($key));

		return lcfirst(str_replace(' ', '', ucwords($key)));
	}
}
