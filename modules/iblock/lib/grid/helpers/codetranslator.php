<?php

namespace Bitrix\Iblock\Grid\Helpers;

use Bitrix\Main\SystemException;
use CIBlock;

trait CodeTranslator
{
	/**
	 * @var array
	 * @psalm-var array<string, mixed>
	 */
	private array $iblockFields;

	/**
	 * Iblock
	 *
	 * @return int
	 */
	abstract protected function getIblockId(): int;

	/**
	 * @return array
	 * @throws \Bitrix\Main\SystemException if iblock not found.
	 */
	private function getIblockFields(): array
	{
		if (!isset($this->iblockFields))
		{
			$result = CIBlock::GetArrayByID($this->getIblockId());
			if (!is_array($result))
			{
				throw new SystemException('Iblock not found');
			}

			$this->iblockFields = $result;
		}

		return $this->iblockFields;
	}

	/**
	 * Transliteration settings of element.
	 *
	 * @return array|null
	 */
	protected function getElementTranslitSettings(): ?array
	{
		$iblock = $this->getIblockFields();

		$elementTranslit = $iblock['FIELDS']['CODE']['DEFAULT_VALUE'] ?? null;
		if (empty($elementTranslit))
		{
			return null;
		}

		$useElementTranslit =
			isset($elementTranslit['TRANSLITERATION'], $elementTranslit['USE_GOOGLE'])
			&& $elementTranslit['TRANSLITERATION'] === 'Y'
			&& $elementTranslit['USE_GOOGLE'] !== 'Y'
		;
		if ($useElementTranslit)
		{
			return [
				'max_len' => $elementTranslit['TRANS_LEN'],
				'change_case' => $elementTranslit['TRANS_CASE'],
				'replace_space' => $elementTranslit['TRANS_SPACE'],
				'replace_other' => $elementTranslit['TRANS_OTHER'],
				'delete_repeat_replace' => ($elementTranslit['TRANS_EAT'] === 'Y')
			];
		}

		return null;
	}

	/**
	 * Transliteration settings of section.
	 *
	 * @return array|null
	 */
	protected function getSectionTranslitSettings(): ?array
	{
		$iblock = $this->getIblockFields();

		$sectionTranslit = $iblock['FIELDS']['SECTION_CODE']['DEFAULT_VALUE'] ?? null;
		if (empty($sectionTranslit))
		{
			return null;
		}

		$useSectionTranslit =
			isset($sectionTranslit['TRANSLITERATION'], $sectionTranslit['USE_GOOGLE'])
			&& $sectionTranslit['TRANSLITERATION'] === 'Y'
			&& $sectionTranslit['USE_GOOGLE'] !== 'Y'
		;
		if ($useSectionTranslit)
		{
			return [
				'max_len' => $sectionTranslit['TRANS_LEN'],
				'change_case' => $sectionTranslit['TRANS_CASE'],
				'replace_space' => $sectionTranslit['TRANS_SPACE'],
				'replace_other' => $sectionTranslit['TRANS_OTHER'],
				'delete_repeat_replace' => ($sectionTranslit['TRANS_EAT'] === 'Y')
			];
		}

		return null;
	}
}
