<?php

namespace Bitrix\Main\Grid\Column\UI;

/**
 * Column hint.
 *
 * @see \Bitrix\Main\Grid\Column\UI\ColumnFields method `setHint`
 */
final class Hint
{
	private string $text;
	private bool $isHtml;
	private bool $isInteractivity;

	/**
	 * @param string $text
	 * @param bool $isHtml
	 * @param bool $isInteractivity
	 */
	public function __construct(string $text, bool $isHtml = false, bool $isInteractivity = false)
	{
		$this->text = $text;
		$this->isHtml = $isHtml;
		$this->isInteractivity = $isInteractivity;
	}

	/**
	 * Hint text.
	 *
	 * @param string $value
	 *
	 * @return self
	 */
	public function setText(string $value): self
	{
		$this->text = $value;

		return $this;
	}

	/**
	 * Hint text.
	 *
	 * @return string
	 */
	public function getText(): string
	{
		return $this->text;
	}

	/**
	 * Sets text is html.
	 *
	 * @param bool $value
	 *
	 * @return self
	 */
	public function setHtml(bool $value): self
	{
		$this->isHtml = $value;

		return $this;
	}

	/**
	 * Text is html?
	 *
	 * @return bool
	 */
	public function isHtml(): bool
	{
		return $this->isHtml;
	}

	/**
	 * Interactivity mode.
	 *
	 * @see `ui.hint` extensions for details.
	 *
	 * @param bool $value
	 *
	 * @return self
	 */
	public function setInteractivity(bool $value): self
	{
		$this->isInteractivity = $value;

		return $this;
	}

	/**
	 * Is interactivity mode?
	 *
	 * @return bool
	 */
	public function isInteractivity(): bool
	{
		return $this->isInteractivity;
	}
}
