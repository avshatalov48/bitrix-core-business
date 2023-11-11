<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\Url\AdminPage\BaseBuilder;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Web\Uri;

Loader::requireModule('iblock');

final class ProductNameFieldAssembler extends FieldAssembler
{
	private ?BaseBuilder $urlBuilder;

	public function __construct(array $columnIds, BaseBuilder $urlBuilder = null)
	{
		parent::__construct($columnIds);

		$this->urlBuilder = $urlBuilder;
	}

	protected function prepareRow(array $row): array
	{
		$rowType = $row['data']['ROW_TYPE'] ?? null;
		if ($rowType !== RowType::ELEMENT)
		{
			return $row;
		}

		$elementId = (int)($row['data']['ID'] ?? 0);
		$elementName = (string)($row['data']['NAME'] ?? '');
		if ($elementId > 0 && !empty($elementName))
		{
			$name = HtmlFilter::encode($elementName);

			if (isset($this->urlBuilder))
			{
				$link = new Uri(
					$this->urlBuilder->getElementDetailUrl($elementId)
				);

				$columnValue =
					'<a href="' . HtmlFilter::encode($link->toAbsolute()) . '">'
					. $name
					. '</a>'
				;
			}
			else
			{
				$columnValue =
					'<div>'
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
