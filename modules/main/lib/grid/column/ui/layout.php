<?php

namespace Bitrix\Main\Grid\Column\UI;

use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Html\Attributes;

/**
 * Layout of the column and its cells.
 */
final class Layout
{
	private Column $column;
	private bool $hasLeftAlignedCounter = false;

	/**
	 * @param Column $column
	 */
	public function __construct(Column $column)
	{
		$this->column = $column;
	}

	/**
	 * Sets has counter with left align.
	 *
	 * @param bool $value
	 *
	 * @return self
	 */
	public function setHasLeftAlignedCounter(bool $value): self
	{
		$this->hasLeftAlignedCounter = $value;

		return $this;
	}

	/**
	 * Has counter with left align?
	 *
	 * @return bool
	 */
	public function isHasLeftAlignedCounter(): bool
	{
		return $this->hasLeftAlignedCounter;
	}

	/**
	 * Columns cells attributes.
	 *
	 * @return Attributes
	 */
	public function getCellAttributes(): Attributes
	{
		$result = new Attributes();

		$value = $this->column->getTitle();
		if (!empty($value))
		{
			$result->set('title', $value);
		}

		$value = $this->column->getCssColorClassName();
		if (!empty($value))
		{
			$result->set('class', $value);
		}

		$value = $this->getColumnStyle();
		if (!empty($value))
		{
			$result->set('style', $value);
		}

		$result->setData('name', $this->column->getId());
		$result->setData('sort-by', $this->column->getSort());
		$result->setData('sort-url', $this->column->getSortUrl());
		$result->setData('sort-order', $this->column->getNextSortOrder());

		$editable = $this->column->getEditable();
		if (isset($editable))
		{
			$result->setData('edit', $editable->toArray());
		}
		else
		{
			$result->setData('edit', false);
		}

		return $result;
	}

	/**
	 * Column style attribute value.
	 *
	 * @return string
	 */
	private function getColumnStyle(): string
	{
		$result = '';

		$width = $this->column->getWidth();
		if (isset($width))
		{
			$result .= " width:{$width}px;";
		}

		$colorValue = $this->column->getCssColorValue();
		if (isset($colorValue))
		{
			$result .= " background-color:{$colorValue};";
		}

		return trim($result);
	}

	/**
	 * Column container attributes.
	 *
	 * @return Attributes
	 */
	public function getContainerAttributes(): Attributes
	{
		$result = new Attributes();

		$value = $this->getContainerStyle();
		if (!empty($value))
		{
			$result->set('style', $value);
		}

		return $result;
	}

	/**
	 * Column container style attribute value.
	 *
	 * @return string
	 */
	private function getContainerStyle(): string
	{
		$result = '';

		$width = $this->column->getWidth();
		if (isset($width))
		{
			$result .= "width:{$width}px;";
		}

		return $result;
	}
}
