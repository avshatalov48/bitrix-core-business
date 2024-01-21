<?php

namespace Bitrix\Lists\Api\Data\ServiceFactory;

use Bitrix\Lists\Api\Data\Data;
use Bitrix\Lists\Api\Request\ServiceFactory\AddElementRequest;
use Bitrix\Main\ArgumentOutOfRangeException;

final class ElementToAdd extends Data
{
	private int $iBlockId;
	private int $sectionId;
	private array $values;
	private int $createdBy;

	private function __construct(
		int $iBlockId,
		int $sectionId,
		array $values,
		int $createdBy,
	)
	{
		$this->iBlockId = $iBlockId;
		$this->sectionId = $sectionId;
		$this->values = $values;
		$this->createdBy = $createdBy;
	}

	/**
	 * @param AddElementRequest $request
	 * @return self
	 * @throws ArgumentOutOfRangeException
	 */
	public static function createFromRequest($request): self
	{
		$iBlockId = self::validateId($request->iBlockId);
		if ($iBlockId === null || $iBlockId === 0)
		{
			throw new ArgumentOutOfRangeException('iBlockId', 1, null);
		}

		$sectionId = self::validateId($request->sectionId);
		if ($sectionId === null)
		{
			throw new ArgumentOutOfRangeException('sectionId', 0, null);
		}

		$createdBy = self::validateId($request->createdByUserId);
		if ($createdBy === null || $createdBy === 0)
		{
			throw new ArgumentOutOfRangeException('createdBy', 1, null);
		}

		$values = self::validateValues($request->values, $iBlockId, $sectionId);

		return new self($iBlockId, $sectionId, $values, $createdBy);
	}

	/**
	 * @param array $values
	 * @param int $iBlockId
	 * @param int $sectionId
	 * @return array
	 */
	private static function validateValues(array $values, int $iBlockId, int $sectionId): array
	{
		unset($values['ID']);
		$values['IBLOCK_ID'] = $iBlockId;
		$values['IBLOCK_SECTION_ID'] = $sectionId;

		return $values;
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
	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	/**
	 * @return array
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * @return int
	 */
	public function getCreatedBy(): int
	{
		return $this->createdBy;
	}
}
