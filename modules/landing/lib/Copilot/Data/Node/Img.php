<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Node;

use Bitrix\Landing;
use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Copilot\Data\Block\Operator;
use Bitrix\Landing\Copilot\Data\Type\NodeType;
use Bitrix\Landing\Copilot\Data\Type\ImgValueDto;
use Bitrix\Landing\Rights;
use Bitrix\Main\Web\Uri;

class Img extends Node
{
	protected const TYPE = NodeType::Img;

	/**
	 * Array like position => value
	 * @var ImgValueDto[]
	 */
	private array $values = [];

	/**
	 * ['width' => int, 'height' => int, 'aspectRatio' => string]
	 * @var array
	 */
	private array $sizeData = [];
	private bool $editInStyle = false;
	private ?array $genderData = null;

	public function __construct(string $code, array $data)
	{
		parent::__construct($code, $data);
		$this->setData($data);
	}

	public function setData(array $data): void
	{
		if (isset($data['editInStyle']))
		{
			$this->editInStyle = (bool)$data['editInStyle'];
		}

		if (isset($data['sizeData']))
		{
			foreach ($data['sizeData'] as $position => $sizeData)
			{
				$this->sizeData[$position] = [
					'width' => $sizeData['width'] ?? null,
					'height' => $sizeData['height'] ?? null,
					'aspectRatio' => $sizeData['aspectRatio'] ?? null,
				];
			}
		}

		if (isset($data['genderData']))
		{
			$this->genderData = $data['genderData'];
		}

		// get data from typed values array
		if (isset($data['values']))
		{
			$this->values = [];
			foreach ($data['values'] as $position => $value)
			{
				$this->values[$position] = new ImgValueDto();
				if (isset($value['defaultSrc']))
				{
					$this->values[$position]->defaultSrc = $value['defaultSrc'];
				}
				if (isset($value['defaultSrc2x']))
				{
					$this->values[$position]->defaultSrc2x = $value['defaultSrc2x'];
				}
				if (isset($value['src']))
				{
					$this->values[$position]->src = $value['src'];
					$this->values[$position]->src2x = $value['src2x'] ?? $value['src'];
					if (isset($value['id']))
					{
						$this->values[$position]->id = $value['id'];
						$this->values[$position]->id2x = $value['id2x'] ?? $value['id'];
					}
				}
			}

			return;
		}

		// ... or from partial data
		$dataSrc['src'] = $data['src'] ?? $data['defaultSrc'];
		$dataSrc['src2x'] = $data['src2x'] ?? $data['defaultSrc2x'];

		$maxLength = max(
			is_array($dataSrc['src']) ? count($dataSrc['src']) : 0,
			is_array($dataSrc['src2x']) ? count($dataSrc['src2x']) : 0
		);

		for ($position = 0; $position < $maxLength; $position++)
		{
			if (!isset($this->values[$position]))
			{
				$this->values[$position] = new ImgValueDto();
			}

			if (isset($dataSrc['src'][$position]))
			{
				$src = $dataSrc['src'][$position];
				$this->values[$position]->defaultSrc = $src;
				$this->values[$position]->src = $src;
			}

			if (isset($dataSrc['src2x'][$position]))
			{
				$src2x = $dataSrc['src2x'][$position];
				$this->values[$position]->defaultSrc2x = $src2x;
				$this->values[$position]->src2x = $src2x;
			}
		}
	}

	/**
	 * Set value for one position
	 * @param array $value
	 * @param int $position
	 * @return Img
	 */
	public function setValue(array $value, int $position): self
	{
		if (isset($this->values[$position]))
		{
			$defaultSrc = $this->values[$position]->defaultSrc;
			$defaultSrc2x = $this->values[$position]->defaultSrc2x;
		}

		$newValue = new ImgValueDto();
		if (isset($value['src']))
		{
			$newValue->defaultSrc = $defaultSrc ?? null;
			$newValue->defaultSrc2x = $defaultSrc2x ?? null;
			$newValue->src = $value['src'];
			$newValue->src2x = $value['src2x'] ?? $value['src'];
			if (isset($value['id']))
			{
				$newValue->id = $value['id'];
				$newValue->id2x = $value['id2x'] ?? $value['id'];
			}
		}
		$this->values[$position] = $newValue;

		return $this;
	}

	public function setParentBlock(Data\Block $block): self
	{
		parent::setParentBlock($block);

		// todo: need match is wrapper here?
		// if ($this->block->getBgType() === 'image')
		// {
		// 	if (in_array(substr($this->getCode(), 1), $block->getWrapperClasses()))
		// 	{
		// 		$this->isWrapper = true;
		// 	}
		// }

		return $this;
	}

	/**
	 * Get array of data to set in Node
	 *
	 * @param int|null $position
	 *
	 * @return array
	 */
	public function getValues(?int $position = null): array
	{
		$values = [];
		foreach ($this->values as $pos => $value)
		{
			$current = [];
			if (isset($value->defaultSrc))
			{
				$current['defaultSrc'] = $value->defaultSrc;
			}
			if (isset($value->defaultSrc2x))
			{
				$current['defaultSrc2x'] = $value->defaultSrc2x;
			}
			if (isset($value->src))
			{
				$current['src'] = $value->src;
				$current['src2x'] = $value->src2x ?? $value->src;

				if (isset($value->id))
				{
					$current['id'] = $value->id;
					$current['id2x'] = $value->id2x ?? $value->id;
				}
			}

			if (!empty($current))
			{
				$current['type'] = 'image';
				$values[$pos] = $current;
			}
		}

		if (isset($position))
		{
			$values = array_filter(
				$values,
				static function ($key) use ($position){
					return $key === $position;
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		return $values;
	}

	public function getSizeData(): ?array
	{
		return $this->sizeData;
	}

	public function setSrc(array $src): self
	{
		foreach ($src as $position => $srcItem)
		{
			if (!isset($this->values[$position]))
			{
				$this->values[$position] = new ImgValueDto();
				$this->values[$position]->id = null;
				$this->values[$position]->id2x = null;
				$this->values[$position]->defaultSrc = null;
				$this->values[$position]->defaultSrc2x = null;
			}

			if (is_string($srcItem))
			{
				$this->values[$position]->src = $srcItem;
				$this->values[$position]->src2x = $srcItem;
			}

			if (is_array($srcItem))
			{
				$this->values[$position]->src = $srcItem['src'] ?? null;
				$this->values[$position]->src2x = $srcItem['src2x'] ?? $this->values[$position]->src;
			}
		}

		return $this;
	}

	public function setDefaultSrc(array $defaultSrc): self
	{
		foreach ($defaultSrc as $position => $defaultSrcItem)
		{
			if (!isset($this->values[$position]))
			{
				$this->values[$position] = new ImgValueDto();
				$this->values[$position]->id = null;
				$this->values[$position]->id2x = null;
				$this->values[$position]->src = null;
				$this->values[$position]->src2x = null;
			}

			if (is_string($defaultSrcItem))
			{
				$this->values[$position]->defaultSrc = $defaultSrcItem;
				$this->values[$position]->defaultSrc2x = $defaultSrcItem;
			}

			if (is_array($defaultSrcItem))
			{
				$this->values[$position]->defaultSrc = $defaultSrcItem['src'] ?? null;
				$this->values[$position]->defaultSrc2x =
					$defaultSrcItem['src2x'] ?? $this->values[$position]->defaultSrc;
			}
		}

		return $this;
	}

	public function isEditInStyle(): bool
	{
		return $this->editInStyle;
	}

	public function setGenderData(array $genderData): self
	{
		// todo: need node type checking!
		$this->genderData = $genderData;

		return $this;
	}

	// todo: use trait for repeated methods
	public function getGenderData(): ?array
	{
		return $this->genderData;
	}

	public function setImageFromPath(string $path, int $position): static
	{
		$fileArray = self::makeFileArrayByPath($path);
		$sizeData = $this->getSizeData()[$position];
		if (!isset($fileArray, $sizeData))
		{
			return $this;
		}

		$defaultSrc = $this->values[$position]->defaultSrc;
		$defaultSrc2x = $this->values[$position]->defaultSrc2x;

		$this->values[$position] = new ImgValueDto();
		$this->values[$position]->defaultSrc = $defaultSrc;
		$this->values[$position]->defaultSrc2x = $defaultSrc2x;

		foreach ([1, 2] as $x)
		{
			$targetSize = self::matchResizeSize(
				$fileArray['width'],
				$fileArray['height'],
				(int)$sizeData['width'] * $x,
				$sizeData['aspectRatio'],
			);

			$params = $targetSize;
			$params['resize_type'] = BX_RESIZE_IMAGE_EXACT;
			$newFile = Landing\Manager::savePicture(
				$fileArray,
				false,
				$params
			);

			if ($newFile)
			{
				if ($x === 1)
				{
					$this->values[$position]->id = (int)$newFile['ID'];
					$this->values[$position]->src = $newFile['SRC'];
				}
				elseif ($x === 2)
				{
					$this->values[$position]->id2x = (int)$newFile['ID'];
					$this->values[$position]->src2x = $newFile['SRC'];
				}
			}
		}

		return $this;
	}

	/**
	 * Try to makeFileArray by file path. Different behaviour for cloud and local storage
	 * @param string $path
	 * @return array|null
	 */
	private static function makeFileArrayByPath(string $path): ?array
	{
		$uri = new Uri($path);
		$query = $uri->getQuery();
		mb_parse_str($query, $params);
		if (
			!isset($params['id'])
			|| (int)$params['id'] <= 0
		)
		{
			return null;
		}

		$fileId = (int)$params['id'];
		$file = \CFile::GetByID($fileId)->Fetch();
		$fileArray = \CFIle::makeFileArray($fileId);

		if (isset($fileArray['tmp_name']))
		{
			if (
				(int)$file['WIDTH'] > 0
				&& (int)$file['HEIGHT'] > 0
			)
			{
				$fileArray['width'] = (int)$file['WIDTH'];
				$fileArray['height'] = (int)$file['HEIGHT'];
			}

			return $fileArray;
		}

		return null;
	}

	/**
	 * @param int $sourceWidth - size or source img
	 * @param int $sourceHeight - size or source img
	 * @param int $targetWidth - need just one size of target img
	 * @param string $aspectName - f.e. 16by9. See Operator::getAllowedAspectRatios()
	 * @return array
	 */
	protected static function matchResizeSize(
		int $sourceWidth,
		int $sourceHeight,
		int $targetWidth,
		string $aspectName
	): array
	{
		$aspects = Operator::getAllowedAspectRatios();
		$aspect = $aspects[$aspectName] ?? 1;

		$targetHeight = $targetWidth / $aspect;

		if (
			$targetWidth > $sourceWidth
			|| $targetHeight > $sourceHeight
		)
		{
			if (($targetHeight * $aspect) <= $sourceWidth)
			{
				$targetWidth = $sourceHeight * $aspect;
				$targetHeight = $sourceHeight;
			}
			else
			{
				$targetWidth = $sourceWidth;
				$targetHeight = $sourceWidth / $aspect;
			}
		}

		return [
			'width' => $targetWidth,
			'height' => $targetHeight,
		];
	}

	public function toArray(): array
	{
		$data = parent::toArray();
		$data['editInStyle'] = $this->isEditInStyle();
		$data['values'] = $this->getValues();
		$data['sizeData'] = $this->getSizeData();
		$data['genderData'] = $this->getGenderData();

		return $data;
	}

	public function toLanding(?int $position = null): bool
	{
		if (!$this->canApplyToLanding())
		{
			return false;
		}

		$block = $this->getBlockInstance();
		if (!$block)
		{
			return false;
		}

		// todo: need rights?
		$rightsBefore = Rights::isOn();
		Rights::setGlobalOff();

		$dataToSave = [];
		foreach ($this->getValues($position) as $pos => $value)
		{
			if (isset($value['id']))
			{
				Landing\File::addToBlock($block->getId(), $value['id']);
			}
			if (isset($value['id2x']))
			{
				Landing\File::addToBlock($block->getId(), $value['id2x']);
			}

			$dataToSave[$pos] = $value;
		}

		$saved = false;
		$updated = $block->updateNodes([
			$this->getCode() => $dataToSave,
		]);
		if ($updated)
		{
			$saved = $block->save();
		}

		if ($rightsBefore)
		{
			Rights::setGlobalOn();
		}

		return $updated && $saved;
	}
}