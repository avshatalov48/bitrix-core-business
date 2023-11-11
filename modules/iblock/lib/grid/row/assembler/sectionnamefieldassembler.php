<?php

namespace Bitrix\Iblock\Grid\Row\Assembler;

use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\Url\AdminPage\BaseBuilder;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Uri;

final class SectionNameFieldAssembler extends FieldAssembler
{
	private ?BaseBuilder $urlBuilder;

	public function __construct(array $columnIds, ?BaseBuilder $urlBuilder = null)
	{
		parent::__construct($columnIds);

		$this->urlBuilder = $urlBuilder;
		$this->preloadResources();
	}

	/**
	 * Preload resources.
	 *
	 * It is always called, even if empty list of products.
	 * It is necessary for correct display in case of filtering.
	 *
	 * Example, an empty list with a filter, the filter was reset - the products appeared, but the resources were not loaded.
	 *
	 * @return void
	 */
	private function preloadResources(): void
	{
		Extension::load([
			'ui.icons.disk',
		]);
	}

	protected function prepareRow(array $row): array
	{
		$rowType = $row['data']['ROW_TYPE'] ?? null;
		if ($rowType !== RowType::SECTION)
		{
			return $row;
		}

		$sectionId = (int)($row['data']['ID'] ?? 0);
		$sectionName = (string)($row['data']['NAME'] ?? '');
		if ($sectionId > 0 && !empty($sectionName))
		{
			$prefix = '<span class="ui-icon ui-icon-xs ui-icon-file-folder"><i></i></span>';
			$name = '<span class="element-field-grid-section-cell-name">' . htmlspecialcharsbx($sectionName) . '</span>';

			if (isset($this->urlBuilder))
			{
				$link = new Uri($this->urlBuilder->getSectionListUrl($sectionId));

				$columnValue =
					'<a class="element-field-grid-section-cell" href="' . htmlspecialcharsbx($link->toAbsolute()) . '">'
					. $prefix
					. $name
					. '</a>'
				;
			}
			else
			{
				$columnValue =
					'<div class="element-field-grid-section-cell">'
					. $prefix
					. $name
					. '</div>'
				;
			}

			$row['columns'] ??= [];
			foreach ($this->getColumnIds() as $columnId)
			{
				$row['columns'][$columnId] = $columnValue;
			}
		}

		return $row;
	}
}
