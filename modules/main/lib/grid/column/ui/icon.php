<?php

namespace Bitrix\Main\Grid\Column\UI;

/**
 * Column icon.
 *
 * @see \Bitrix\Main\Grid\Column\UI\ColumnFields method `setIcon`
 */
final class Icon
{
	private string $url;
	private ?string $title;

	/**
	 * @param string $url
	 * @param string|null $title
	 */
	public function __construct(string $url, ?string $title = null)
	{
		$this->url = $url;
		$this->title = $title;
	}

	/**
	 * URL image.
	 *
	 * @param string $value
	 *
	 * @return self
	 */
	public function setUrl(string $value): self
	{
		$this->url = $value;

		return $this;
	}

	/**
	 * URL image.
	 *
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * Tooltip title.
	 *
	 * @param string|null $value
	 *
	 * @return self
	 */
	public function setTitle(?string $value): self
	{
		$this->title = $value;

		return $this;
	}

	/**
	 * Tooltip title.
	 *
	 * @return string|null
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}
}
