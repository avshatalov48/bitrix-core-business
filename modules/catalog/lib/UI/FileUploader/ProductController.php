<?php

namespace Bitrix\Catalog\UI\FileUploader;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\v2\Image\MorePhotoImage;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\UI\FileUploader\FileOwnershipCollection;
use Bitrix\UI\FileUploader\Configuration;
use Bitrix\UI\FileUploader\UploaderController;

class ProductController extends UploaderController
{
	private ?array $property;

	public function __construct(array $options = [])
	{
		$options['productId'] ??= 0;
		$options['productId'] = is_numeric($options['productId']) ? (int)$options['productId'] : 0;

		$options['iblockId'] ??= 0;
		$options['iblockId'] = (int)$options['iblockId'];

		if (empty($options['iblockId']) && !empty($options['productId']))
		{
			$options['iblockId'] = (int)\CIBlockElement::GetIBlockByID($options['productId']);
		}

		if (empty($options['iblockId']))
		{
			throw new ArgumentException('Parameter "iblockId" must be defined in options.');
		}

		$iblockInfo = ServiceContainer::getIblockInfo($options['iblockId']);
		if (!$iblockInfo)
		{
			throw new ArgumentException("Iblock {{$options['iblockId']}} is not supported.");
		}

		$options['fieldName'] ??= MorePhotoImage::CODE;
		$options['fieldName'] = (string)$options['fieldName'];

		if ($options['fieldName'] === '')
		{
			throw new ArgumentException('Parameter "fieldName" must be defined in options.');
		}

		$this->property = $this->loadProperty($options['iblockId'], $options['fieldName']);

		parent::__construct($options);
	}

	private function loadProperty(int $iblockId, string $fieldName): ?array
	{
		return PropertyTable::getRow([
			'select' => ['ID', 'FILE_TYPE'],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=ACTIVE' => 'Y',
				'=PROPERTY_TYPE' => PropertyTable::TYPE_FILE,
				'=CODE' => $fieldName,
			],
		]);
	}

	public function isAvailable(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}

	public function getConfiguration(): Configuration
	{
		$configuration = new Configuration();

		$acceptedFileTypes = [];

		if (!empty($this->property['FILE_TYPE']))
		{
			$propertyFileTypes = $this->prepareAcceptedFileTypes($this->property['FILE_TYPE']);
			if (!empty($propertyFileTypes))
			{
				$acceptedFileTypes = array_intersect(Configuration::getImageExtensions(), $propertyFileTypes);
			}
		}

		if (!empty($acceptedFileTypes))
		{
			$configuration->setAcceptedFileTypes($acceptedFileTypes);
		}
		else
		{
			$configuration->acceptOnlyImages();
		}

		return $configuration;
	}

	private function prepareAcceptedFileTypes(string $fileTypes): array
	{
		$imageExtensions = explode(',', $fileTypes);

		return array_map(static fn ($extension) => '.' . trim($extension), $imageExtensions);
	}

	public function canUpload(): bool
	{
		return CurrentUser::get()->canDoOperation(ActionDictionary::ACTION_STORE_VIEW);
	}

	public function verifyFileOwner(FileOwnershipCollection $files): void
	{
	}

	public function canView(): bool
	{
		return false;
	}

	public function canRemove(): bool
	{
		return false;
	}
}
