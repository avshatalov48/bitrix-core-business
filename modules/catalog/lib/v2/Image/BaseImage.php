<?php

namespace Bitrix\Catalog\v2\Image;

use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Fields\TypeCasters\MapTypeCaster;

/**
 * Class BaseImage
 *
 * @package Bitrix\Catalog\v2\Image
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
abstract class BaseImage extends BaseEntity
{
	public function __construct(ImageRepositoryContract $imageRepository)
	{
		parent::__construct($imageRepository);
	}

	public function getSource(): string
	{
		return (string)$this->getField('SRC');
	}

	public function setFileStructure(array $fileFields): BaseImage
	{
		$this->setField('FILE_STRUCTURE', $fileFields);

		return $this;
	}

	public function getFileStructure(): ?array
	{
		return $this->getField('FILE_STRUCTURE');
	}

	public function getPropertyValueId(): int
	{
		return (int)$this->getField('PROPERTY_VALUE_ID');
	}

	protected function getFieldsMap(): array
	{
		return [
			'ID' => MapTypeCaster::NULLABLE_INT,
			'PROPERTY_VALUE_ID' => MapTypeCaster::INT,
			'SRC' => MapTypeCaster::STRING,
			'WIDTH' => MapTypeCaster::INT,
			'HEIGHT' => MapTypeCaster::INT,
			'WEIGHT' => MapTypeCaster::INT,
			'FILE_NAME' => MapTypeCaster::STRING,
			'FILE_STRUCTURE' => static function ($value) {
				return is_array($value) ? $value : \CIBlock::makeFileArray(null, true);
			},
		];
	}
}