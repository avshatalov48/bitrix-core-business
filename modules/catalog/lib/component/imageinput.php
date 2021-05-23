<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Image\MorePhotoImage;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\UI\Viewer\ItemAttributes;

class ImageInput
{
	/** @var BaseIblockElementEntity $entity */
	private $entity;

	private $morePhotoPropertyId;
	private $inputName;
	private $inputId;
	private $values;

	public function __construct(BaseIblockElementEntity $entity = null)
	{
		$this->entity = $entity;
	}

	public function setInputName($name)
	{
		$this->inputName = $name;
	}

	public function isEmpty(): bool
	{
		return count($this->getValues()) === 0;
	}

	public function getFormattedField(): array
	{
		$fileValues = $this->getValues();

		return [
			'id' => 'bx_file_'.$this->getInputId(),
			'values' => $fileValues,
			'isEmpty' => empty($fileValues),
			'preview' => $this->getPreview(),
			'input' => $this->getHtml($this->getImageParams(), $fileValues, [
				'disabled' => $this->entity === null,
			]),
			'emptyInput' => $this->getHtml($this->getImageParams(), [], [
				'disabled' => true,
			]),
		];
	}

	public function getComponentResponse(): ?Response\Component
	{
		return new Response\Component(
			'bitrix:ui.image.input',
			'',
			[
				'FILE_SETTINGS' => $this->getImageParams(),
				'FILE_VALUES' => $this->getValues(),
				'LOADER_PREVIEW' => $this->getPreview(),
			]
		);
	}

	private function getImageParams(): array
	{
		return [
			'name' => $this->getInputName(),
			'id' => $this->getInputId(),
			'description' => 'Y',
			'allowUpload' => 'I',
			'allowUploadExt' => null,
			'maxCount' => null,
			'upload' => true,
			'medialib' => false,
			'fileDialog' => true,
			'cloud' => true,
		];
	}

	private function getInputName(): string
	{
		return $this->inputName ?? 'PROPERTY_'.$this->getMorePhotoPropertyId().'_n#IND#';
	}

	private function getInputId(): string
	{
		if (!$this->inputId)
		{
			$id = $this->getInputName().'_'.random_int(1, 1000000);
			$this->inputId = strtolower(preg_replace('/[^a-z0-9]/i', '_', $id));
		}

		return $this->inputId;
	}

	private function getEntityId(): int
	{
		return $this->entity ? (int)$this->entity->getId() : 0;
	}

	private function getIblockId(): int
	{
		return $this->entity ? (int)$this->entity->getIblockId() : 0;
	}

	private function getValues(): array
	{
		if (isset($this->values))
		{
			return $this->values;
		}

		if ($this->getEntityId() <= 0)
		{
			return [];
		}

		$this->values = [];
		$photoCollection = $this->entity->getFrontImageCollection();
		foreach ($photoCollection as $item)
		{
			if ($item instanceof MorePhotoImage)
			{
				$propName = str_replace('n#IND#', $item->getPropertyValueId(), $this->getInputName());
				$this->values[$propName] = $item->getId();
			}
			else
			{
				$this->values[$item::CODE] = $item->getId();
			}
		}

		return $this->values;
	}

	private function getMorePhotoPropertyId(): ?int
	{
		if (empty($this->morePhotoPropertyId))
		{
			$propertyRaw = \Bitrix\Iblock\PropertyTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=IBLOCK_ID' => $this->getIblockId(),
					'=ACTIVE' => 'Y',
					'=CODE' => 'MORE_PHOTO',
				],
				'limit' => 1,
			]);

			if ($morePhotoProperty = $propertyRaw->fetch())
			{
				$this->morePhotoPropertyId = $morePhotoProperty['ID'];
			}
		}

		return $this->morePhotoPropertyId;
	}

	private function getHtml(array $settings = [], array $values = [], array $options = []): string
	{
		ob_start();

		$GLOBALS['APPLICATION']->includeComponent(
			'bitrix:ui.image.input',
			'',
			[
				'FILE_SETTINGS' => $settings,
				'FILE_VALUES' => $values,
				'LOADER_PREVIEW' => (string)($options['preview'] ?? ''),
				'DISABLED' => (bool)($options['disabled'] ?? false),
			]
		);

		return ob_get_clean();
	}

	private function getPreview(): string
	{
		$imageData = [];

		if ($this->getEntityId() > 0)
		{
			$photoCollection = $this->entity->getFrontImageCollection();
			foreach ($photoCollection as $item)
			{
				if (!$item->getFileStructure())
				{
					continue;
				}

				$attributes = ItemAttributes::tryBuildByFileData($item->getFileStructure(), $item->getSource());
				$attributes->setAttribute('data-viewer-type', 'image');
				$attributes->setGroupBy("group-{$this->entity->getId()}");
				$imageData[] = [
					'SOURCE' => $item->getSource(),
					'ATTRIBUTES' => $attributes,
				];
			}
		}

		$fileCount = min(count($imageData), 3);

		switch ($fileCount)
		{
			case 3:
				$multipleClass = ' ui-image-input-img-block-multiple';
				break;

			case 2:
				$multipleClass = ' ui-image-input-img-block-double';
				break;

			case 0:
				$multipleClass = ' ui-image-input-img-block-empty';
				break;

			case 1:
			default:
				$multipleClass = '';
				break;
		}

		$imageString = '';
		foreach ($imageData as $key => $image)
		{
			$displayClass = '';
			if ($key !== 0)
			{
				$displayClass = 'main-ui-hide';
			}
			$imageString .= "
				<img class='ui-image-input-img {$displayClass}'	{$image['ATTRIBUTES']} src='{$image['SOURCE']}'>
			";
		}

		return "
			<div class='ui-image-input-img-block{$multipleClass}'>
				<div class='ui-image-input-img-block-inner'>
					<div class='ui-image-input-img-item'>
						{$imageString}
					</div>
				</div>
			</div>
		";
	}
}