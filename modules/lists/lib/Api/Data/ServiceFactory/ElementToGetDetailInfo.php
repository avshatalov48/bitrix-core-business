<?php

namespace Bitrix\Lists\Api\Data\ServiceFactory;

use Bitrix\Lists\Api\Request\ServiceFactory\GetElementDetailInfoRequest;
use Bitrix\Main\ArgumentOutOfRangeException;

final class ElementToGetDetailInfo
{
	private int $iBlockId;
	private int $elementId;
	private int $sectionId;

	private array $additionalSelectFields = [];
	private bool $isNeedCheckPermissions;

	private function __construct(
		int $iBlockId,
		int $elementId,
		int $sectionId
	){
		$this->iBlockId = $iBlockId;
		$this->elementId = $elementId;
		$this->sectionId = $sectionId;
	}

	/**
	 * @throws ArgumentOutOfRangeException
	 */
	public static function createFromRequest(GetElementDetailInfoRequest $request): self
	{
		$iBlockId = self::validateId($request->iBlockId);
		if ($iBlockId === null || $iBlockId === 0)
		{
			throw new ArgumentOutOfRangeException('iBlockId', 1, null);
		}

		$elementId = self::validateId($request->elementId);
		if ($elementId === null)
		{
			throw new ArgumentOutOfRangeException('elementId', 0, null);
		}

		$sectionId = self::validateId($request->sectionId);
		if ($sectionId === null)
		{
			throw new ArgumentOutOfRangeException('sectionId', 0, null);
		}

		$self = new self($iBlockId, $elementId, $sectionId);

		if ($request->additionalSelectFields)
		{
			$self->setAdditionalSelectFields($request->additionalSelectFields);
		}

		$self->isNeedCheckPermissions = $request->needCheckPermission;

		return $self;
	}

	private static function validateId(int $id): ?int
	{
		if ($id >= 0)
		{
			return $id;
		}

		return null;
	}

	private function setAdditionalSelectFields(array $selectFields): void
	{
		$allowedFields = ['FIELDS', 'PROPS', 'IBLOCK_ID', 'IBLOCK_SECTION_ID'];

		$fields = [];
		foreach ($selectFields as $fieldId)
		{
			if (in_array($fieldId, $allowedFields, true))
			{
				$fields[] = $fieldId;
			}
		}

		$this->additionalSelectFields = $fields;
	}

	/**
	 * @return int
	 */
	public function getIBlockId(): int
	{
		return $this->iBlockId;
	}

	/**
	 * @return int
	 */
	public function getElementId(): int
	{
		return $this->elementId;
	}

	/**
	 * @return int
	 */
	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	/**
	 * @return array
	 */
	public function getAdditionalSelectFields(): array
	{
		return $this->additionalSelectFields;
	}

	/**
	 * @return bool
	 */
	public function isNeedCheckPermissions(): bool
	{
		return $this->isNeedCheckPermissions;
	}
}
