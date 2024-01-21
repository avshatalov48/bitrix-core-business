<?php

namespace Bitrix\Iblock\Grid\Access;

use CIBlockElementRights;
use CIBlockRights;
use CIBlockSectionRights;

class IblockRightsChecker
{
	private int $iblockId;
	private array $elementsRights = [];
	private array $sectionsRights = [];

	public function __construct(int $iblockId)
	{
		$this->iblockId = $iblockId;
	}

	final protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	/**
	 * Preloading rights for elements and sections (necessary for performance).
	 *
	 * The best way to override the `setRawRows` grid method:
	 * ```php
		public function setRawRows(iterable $rawValue): void
		{
			parent::setRawRows($rawValue);

			// preload rights
			if (!empty($rawValue))
			{
				$elementIds = [];
				$sectionIds = [];

				foreach ($this->getRawRows() as $row)
				{
					$id = (int)($row['ID'] ?? 0);
					if ($id <= 0)
					{
						continue;
					}

					$type = $row['ROW_TYPE'] ?? RowType::ELEMENT;
					if ($type === RowType::SECTION)
					{
						$sectionIds[] = $id;
					}
					else
					{
						$elementIds[] = $id;
					}
				}

				// before create getter for rights checker
				$this->getIblockRightsChecker()->preloadRights($elementIds, $sectionIds);
			}
		}
	 * ```
	 *
	 * @param array $elementIds
	 * @param array $sectionIds
	 *
	 * @return void
	 */
	public function preloadRights(array $elementIds, array $sectionIds): void
	{
		if (!empty($elementIds))
		{
			$rows = CIBlockElementRights::UserHasRightTo(
				$this->getIblockId(),
				$elementIds,
				'',
				CIBlockElementRights::RETURN_OPERATIONS
			);
			foreach ($rows as $elementId => $rights)
			{
				$this->elementsRights[$elementId] = $rights;
			}
		}

		if (!empty($elementIds))
		{
			$rows = CIBlockSectionRights::UserHasRightTo(
				$this->getIblockId(),
				$sectionIds,
				'',
				CIBlockSectionRights::RETURN_OPERATIONS
			);
			foreach ($rows as $sectionId => $rights)
			{
				$this->sectionsRights[$sectionId] = $rights;
			}
		}
	}

	protected function checkElementRight(int $elementId, string $right): bool
	{
		$cachedRights = $this->elementsRights[$elementId] ?? null;
		if (isset($cachedRights))
		{
			return isset($cachedRights[$right]);
		}

		return CIBlockElementRights::UserHasRightTo($this->getIblockId(), $elementId, $right);
	}

	protected function checkSectionRight(int $sectionId, string $right): bool
	{
		$cachedRights = $this->sectionsRights[$sectionId] ?? null;
		if ($cachedRights !== null)
		{
			return isset($cachedRights[$right]);
		}

		return CIBlockSectionRights::UserHasRightTo($this->getIblockId(), $sectionId, $right);
	}

	#region public api

	public function canAddElement(int $elementId): bool
	{
		return $this->checkElementRight($elementId, 'section_element_bind');
	}

	public function canEditElement(int $elementId): bool
	{
		return $this->checkElementRight($elementId, 'element_edit');
	}

	public function canEditElements(): bool
	{
		return CIBlockRights::UserHasRightTo($this->getIblockId(), $this->getIblockId(), 'element_edit');
	}

	public function canEditSection(int $sectionId): bool
	{
		return $this->checkSectionRight($sectionId, 'section_edit');
	}

	public function canDeleteElements(): bool
	{
		return CIBlockRights::UserHasRightTo($this->getIblockId(), $this->getIblockId(), 'element_delete');
	}

	public function canDeleteElement(int $elementId): bool
	{
		return $this->checkElementRight($elementId, 'element_delete');
	}

	public function canDeleteSection(int $sectionId): bool
	{
		return $this->checkSectionRight($sectionId, 'section_delete');
	}

	public function canBindElementToSection(int $sectionId): bool
	{
		return $this->checkSectionRight($sectionId, 'section_element_bind');
	}

	public function canBindSectionToSection(int $sectionId): bool
	{
		return $this->checkSectionRight($sectionId, 'section_section_bind');
	}

	#endregion public api
}
