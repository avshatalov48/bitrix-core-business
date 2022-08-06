<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\Image\MorePhotoImage;
use Bitrix\Main\Engine\Response;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\UI\Viewer\ItemAttributes;

class ImageInput
{
	public const FILE_ID_SALT = 'catalog.image.input.file.id';

	/** @var BaseIblockElementEntity $entity */
	private $entity;

	private $autoSavingEnabled = true;
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

	public function disableAutoSaving(): self
	{
		$this->autoSavingEnabled = false;

		return $this;
	}

	public function isEmpty(): bool
	{
		return count($this->getValues()) === 0;
	}

	public function getFormattedField(): array
	{
		$fileValues = $this->getValues();
		$signedFileValues = $this->getSignedValues();

		return [
			'id' => 'bx_file_'.$this->getInputId(),
			'values' => $signedFileValues,
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
			'bitrix:catalog.image.input',
			'',
			[
				'FILE_SETTINGS' => $this->getImageParams(),
				'FILE_VALUES' => $this->getValues(),
				'FILE_SIGNED_VALUES' => $this->getSignedValues(),
				'LOADER_PREVIEW' => $this->getPreview(),
				'ENABLE_AUTO_SAVING' => $this->autoSavingEnabled,
				'PRODUCT_ENTITY' => $this->entity,
				'INPUT_ID' => 'bx_file_'.$this->getInputId(),
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
			'maxCount' => !$this->isMorePhotoEnabled() ? 1 : null,
			'upload' => true,
			'medialib' => false,
			'fileDialog' => true,
			'cloud' => true,
		];
	}

	private function getInputName(): string
	{
		$defaultName = $this->isMorePhotoEnabled() ? 'PROPERTY_'.$this->getMorePhotoPropertyId().'_n#IND#' : 'DETAIL_PICTURE';
		return $this->inputName ?? $defaultName;
	}

	private function getInputId(): string
	{
		if (!$this->inputId)
		{
			$id = uniqid($this->getInputName().'_', false);
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
		if ($this->isMorePhotoEnabled())
		{
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
		}
		else
		{
			$item = $photoCollection->getFrontImage();
			if ($item)
			{
				$this->values[$item::CODE] = $item->getId();
			}
		}

		return $this->values;
	}

	private function getSignedValues(): array
	{
		$signedValues = [];

		foreach ($this->getValues() as $name => $fileId)
		{
			if ($fileId !== null && is_numeric($fileId))
			{
				static $signer = null;
				if ($signer === null)
				{
					$signer = new Signer;
				}

				$signedValues[$name] = $signer->sign((string)$fileId, self::FILE_ID_SALT);
			}
		}

		return $signedValues;
	}

	private function isMorePhotoEnabled(): bool
	{
		return (int)$this->getMorePhotoPropertyId() > 0;
	}

	private function getMorePhotoPropertyId(): ?int
	{
		if ($this->morePhotoPropertyId === null)
		{
			$this->morePhotoPropertyId = 0;
			$propertyRaw = \Bitrix\Iblock\PropertyTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=IBLOCK_ID' => $this->getIblockId(),
					'=ACTIVE' => 'Y',
					'=CODE' => MorePhotoImage::CODE,
				],
				'limit' => 1,
				'cache' => [
					'ttl' => 86400,
				],
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
			'bitrix:catalog.image.input',
			'',
			[
				'FILE_SETTINGS' => $settings,
				'FILE_VALUES' => $values,
				'FILE_SIGNED_VALUES' => $this->getSignedValues(),
				'LOADER_PREVIEW' => (string)($options['preview'] ?? ''),
				'DISABLED' => (bool)($options['disabled'] ?? false),
				'ENABLE_AUTO_SAVING' => $this->autoSavingEnabled,
				'PRODUCT_ENTITY' => $this->entity,
				'INPUT_ID' => 'bx_file_'.$this->getInputId(),
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