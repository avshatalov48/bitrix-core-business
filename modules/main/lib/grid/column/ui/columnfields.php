<?php

namespace Bitrix\Main\Grid\Column\UI;

use Bitrix\Main\ArgumentException;

/**
 * Column fields used in the UI, in particular the `main.ui.grid` component.
 */
trait ColumnFields
{
	/**
	 * The sorting direction that is used when sorting by column for the first time.
	 *
	 * @var string
	 */
	protected string $firstOrder = 'asc';
	/**
	 * Is column name displayed?
	 *
	 * @var bool
	 */
	protected bool $showname = true;
	/**
	 * Alignment of text in column cells.
	 *
	 * @var string|null
	 */
	protected ?string $align = null;
	/**
	 * Tooltip title.
	 *
	 * @var string|null
	 */
	protected ?string $title = null;
	/**
	 * Column width (px).
	 *
	 * @var int|null
	 */
	protected ?int $width = null;
	/**
	 * Is it possible to change the column size?
	 *
	 * @var bool
	 */
	protected bool $resizeable = true;
	/**
	 * Cancels the row selection when clicking on a column cell (relevant for interactive content).
	 *
	 * @var bool
	 */
	protected bool $preventDefault = true;
	/**
	 * Anchors the column on the left, when scrolling horizontally.
	 *
	 * @var bool
	 */
	protected bool $sticked = false;
	/**
	 * Column shift.
	 *
	 * @var bool
	 */
	protected bool $shift = false;
	/**
	 * Column section id.
	 *
	 * @var string|null
	 */
	protected ?string $sectionId = null;
	/**
	 * Sort index in the list of all columns.
	 *
	 * @var int
	 */
	protected int $sortIndex = 0;
	/**
	 * URL sets sorting.
	 *
	 * @var string|null
	 */
	protected ?string $sortUrl = null;
	/**
	 * Current sort state (asc or desc).
	 *
	 * @var string|null
	 */
	protected ?string $sortState = null;
	/**
	 * Is column displayed?
	 *
	 * @var bool
	 */
	protected bool $shown;
	/**
	 * Icon.
	 *
	 * @var Icon|null
	 */
	protected ?Icon $icon = null;
	/**
	 * Tooltip hint.
	 *
	 * @var Hint|null
	 */
	protected ?Hint $hint = null;
	/**
	 * Columns and cells HTML layout.
	 *
	 * @var Layout
	 */
	protected Layout $layout;

	// css
	protected ?string $cssClassName = null;
	protected ?string $cssColorValue = null;
	protected ?string $cssColorClassName = null;

	public function getFirstOrder(): string
	{
		return $this->firstOrder;
	}

	/**
	 * @param string $firstOrder `asc` or `desc` value
	 *
	 * @return self
	 */
	public function setFirstOrder(string $firstOrder): self
	{
		$this->firstOrder = $firstOrder === 'asc' ? 'asc' : 'desc';

		return $this;
	}

	public function isShowname(): bool
	{
		return $this->showname;
	}

	public function setShowname(bool $showname): self
	{
		$this->showname = $showname;

		return $this;
	}

	public function getCssClassName(): ?string
	{
		return $this->cssClassName;
	}

	public function setCssClassName(?string $cssClassName): self
	{
		$this->cssClassName = $cssClassName;

		return $this;
	}

	public function getAlign(): string
	{
		return $this->align ?? 'left';
	}

	public function setAlign(?string $align): self
	{
		$this->align = $align;

		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setTitle(?string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getWidth(): ?int
	{
		return $this->width;
	}

	/**
	 * Sets column width.
	 *
	 * @param int|null $width pixels
	 *
	 * @return self
	 */
	public function setWidth(?int $width): self
	{
		$this->width = $width;

		return $this;
	}

	public function getCssColorClassName(): ?string
	{
		return $this->cssColorClassName;
	}

	/**
	 * Set color class name.
	 *
	 * If need to set specific color value, see `setCssColorValue` method
	 *
	 * @see \Bitrix\Main\Grid\Column\Color for details about available values.
	 *
	 * @param string $color class name of style
	 *
	 * @return self
	 */
	public function setCssColorClassName(string $color): self
	{
		$this->cssColorClassName = $color;

		return $this;
	}

	public static function isValidCssColorValue(string $value): bool
	{
		return
			strpos($value, '#') === 0
			|| strpos($value, 'rgb') === 0
			|| strpos($value, 'hsl') === 0
		;
	}

	/**
	 * Set color css value.
	 *
	 * @param string $value in css format: `#222, rgb(1,2,3), hsl(2,3,4), ...`
	 *
	 * @return self
	 */
	public function setCssColorValue(string $value): self
	{
		if (!self::isValidCssColorValue($value))
		{
			throw new ArgumentException('Invalid css color value');
		}

		$this->cssColorValue = $value;

		return $this;
	}

	public function getCssColorValue(): ?string
	{
		return $this->cssColorValue;
	}

	public function isResizeable(): bool
	{
		return $this->resizeable !== false;
	}

	public function setResizeable(bool $resizeable): self
	{
		$this->resizeable = $resizeable;

		return $this;
	}

	public function isPreventDefault(): bool
	{
		return $this->preventDefault ?? true;
	}

	public function setPreventDefault(bool $preventDefault): self
	{
		$this->preventDefault = $preventDefault;

		return $this;
	}

	public function isShift(): bool
	{
		return $this->shift;
	}

	public function setShift(bool $shift): self
	{
		$this->shift = $shift;

		return $this;
	}

	public function isSticked(): bool
	{
		return $this->sticked;
	}

	public function setSticked(bool $sticked): self
	{
		$this->sticked = $sticked;

		return $this;
	}

	public function setSection(?string $value): self
	{
		$this->sectionId = $value;

		return $this;
	}

	public function getSection(): ?string
	{
		return $this->sectionId;
	}

	public function setSortIndex(int $value): self
	{
		$this->sortIndex = $value;

		return $this;
	}

	public function getSortIndex(): int
	{
		return $this->sortIndex;
	}

	/**
	 * @param string $value `asc` or `desc`
	 *
	 * @return self
	 */
	public function setSortState(string $value): self
	{
		$this->sortState = $value === 'asc' ? 'asc' : 'desc';

		return $this;
	}

	public function getSortState(): ?string
	{
		return $this->sortState;
	}

	public function setSortUrl(string $value): self
	{
		$this->sortUrl = $value;

		return $this;
	}

	public function getSortUrl(): ?string
	{
		return $this->sortUrl;
	}

	public function getSortOrder(): string
	{
		return $this->getSortState() === 'desc' ? 'desc' : 'asc';
	}

	public function getNextSortOrder(): string
	{
		$sortState = $this->getSortState();
		if (isset($sortState))
		{
			return $sortState === 'asc' ? 'desc' : 'asc';
		}

		return $this->getFirstOrder();
	}

	public function setShown(bool $value): self
	{
		$this->shown = $value;

		return $this;
	}

	abstract public function isDefault(): bool;

	public function isShown(): bool
	{
		return $this->shown ?? $this->isDefault();
	}

	public function getLayout(): Layout
	{
		$this->layout ??= new Layout($this);

		return $this->layout;
	}

	public function setIcon(?Icon $value): self
	{
		$this->icon = $value;

		return $this;
	}

	public function getIcon(): ?Icon
	{
		return $this->icon;
	}

	public function setHint(?Hint $value): self
	{
		$this->hint = $value;

		return $this;
	}

	public function getHint(): ?Hint
	{
		return $this->hint;
	}
}
