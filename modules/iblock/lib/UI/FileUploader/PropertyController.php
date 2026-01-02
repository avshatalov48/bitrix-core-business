<?php

namespace Bitrix\Iblock\UI\FileUploader;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\UI\FileUploader\FileOwnershipCollection;
use Bitrix\UI\FileUploader\Configuration;
use Bitrix\UI\FileUploader\UploaderController;

class PropertyController extends UploaderController
{
	private ?array $property = null;

	public function __construct(array $options = [])
	{
		$options['propertyId'] = (int)($options['propertyId'] ?? 0);
		$options['controlId'] = (string)($options['controlId'] ?? '');

		if ($options['propertyId'])
		{
			$this->property = $this->loadPropertyById($options['propertyId']);
		}

		parent::__construct($options);
	}

	private function loadPropertyById(int $propertyId): ?array
	{
		return PropertyTable::getRow([
			'select' => ['ID', 'FILE_TYPE'],
			'filter' => [
				'=ID' => $propertyId,
				'=ACTIVE' => 'Y',
				'=PROPERTY_TYPE' => PropertyTable::TYPE_FILE,
			],
		]);
	}

	public function isAvailable(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			return false;
		}

		if (!$this->property)
		{
			return false;
		}

		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
	}

	public function getConfiguration(): Configuration
	{
		$configuration = new Configuration();

		$acceptedFileTypes = [];

		if (!empty($this->property['FILE_TYPE']))
		{
			$acceptedFileTypes = $this->prepareAcceptedFileTypes($this->property['FILE_TYPE']);
		}

		if (!empty($acceptedFileTypes))
		{
			$configuration->setAcceptedFileTypes($acceptedFileTypes);
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
		if (Loader::includeModule('catalog'))
		{
			return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
		}

		return false;
	}

	public function verifyFileOwner(FileOwnershipCollection $files): void
	{
		$controlId = $this->getOption('controlId');
		if (!$controlId)
		{
			return;
		}

		foreach ($files as $file)
		{
			if (
				!empty(
					\Bitrix\Main\UI\FileInputUtility::instance()->checkFiles(
						$controlId,
						[$file->getId()],
					)
				)
			)
			{
				$file->markAsOwn();
			}
		}
	}

	public function canView(): bool
	{
		if (Loader::includeModule('catalog'))
		{
			return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
		}

		return false;
	}

	public function canRemove(): bool
	{
		if (Loader::includeModule('catalog'))
		{
			return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ);
		}

		return false;
	}
}
