<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class ViewSpec implements TolokaTransferObject
{
	/**
	 * @var string
	 */
	private $markup;

	/**
	 * @var string
	 */
	private $script;

	/**
	 * @var string
	 */
	private $styles;
	/**
	 * @var Asset
	 */
	private $assets;

	/**
	 * @var ViewSpecSettings
	 */
	private $settings;

	public function toArray():array
	{
		return [
			'markup'   => $this->markup,
			'script'   => $this->script,
			'styles'   => $this->styles,
			'settings' => $this->settings->toArray(),
			'assets'   => $this->assets->toArray(),
		];
	}

	/**
	 * @return string
	 */
	public function getMarkup(): string
	{
		return $this->markup;
	}

	/**
	 * @param string $markup
	 *
	 * @return ViewSpec
	 */
	public function setMarkup(string $markup): ViewSpec
	{
		$this->markup = $markup;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getScript(): string
	{
		return $this->script;
	}

	/**
	 * @param string $script
	 *
	 * @return ViewSpec
	 */
	public function setScript(string $script): ViewSpec
	{
		$this->script = $script;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getStyles(): string
	{
		return $this->styles;
	}

	/**
	 * @param string $styles
	 *
	 * @return ViewSpec
	 */
	public function setStyles(string $styles): ViewSpec
	{
		$this->styles = $styles;

		return $this;
	}

	/**
	 * @return ViewSpecSettings
	 */
	public function getSettings(): ViewSpecSettings
	{
		return $this->settings;
	}

	/**
	 * @param ViewSpecSettings $settings
	 *
	 * @return ViewSpec
	 */
	public function setSettings(ViewSpecSettings $settings): ViewSpec
	{
		$this->settings = $settings;

		return $this;
	}

	/**
	 * @return Asset
	 */
	public function getAssets(): Asset
	{
		return $this->assets;
	}

	/**
	 * @param Asset $assets
	 *
	 * @return ViewSpec
	 */
	public function setAssets(Asset $assets): ViewSpec
	{
		$this->assets = $assets;

		return $this;
	}
}