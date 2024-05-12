<?php

namespace Bitrix\Lists\Api\Data\ServiceFactory;

use Bitrix\Lists\Api\Data\Data;
use Bitrix\Lists\Api\Request\ServiceFactory\UpdateElementRequest;
use Bitrix\Main\ArgumentOutOfRangeException;

final class ElementToUpdate extends Data
{
	private int $elementId;
	private int $iBlockId;
	private int $sectionId;
	private array $values;
	private int $modifiedBy;

	private function __construct(
		int $elementId,
		int $iBlockId,
		int $sectionId,
		array $values,
		int $modifiedBy,
	)
	{
		$this->elementId = $elementId;
		$this->iBlockId = $iBlockId;
		$this->sectionId = $sectionId;
		$this->values = $values;
		$this->modifiedBy = $modifiedBy;
	}

	/**
	 * @param UpdateElementRequest $request
	 * @return self
	 * @throws ArgumentOutOfRangeException
	 */
	public static function createFromRequest($request): self
	{
		$elementId = self::validateId($request->elementId);
		if ($elementId === null || $elementId === 0)
		{
			throw new ArgumentOutOfRangeException('elementId', 1, null);
		}

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

		$modifiedBy = self::validateId($request->modifiedByUserId);
		if ($modifiedBy === null || $modifiedBy === 0)
		{
			throw new ArgumentOutOfRangeException('modifiedBy', 1, null);
		}

		$values = $request->values;
		$values['ID'] = $elementId;
		$values['IBLOCK_ID'] = $iBlockId;
		$values['IBLOCK_SECTION_ID'] = $sectionId;

		return new self($elementId, $iBlockId, $sectionId, $values, $modifiedBy);
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
	public function getIBlockId(): int
	{
		return $this->iBlockId;
	}

	/**
	 * @return int
	 */
	public function getModifiedBy(): int
	{
		return $this->modifiedBy;
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
}
