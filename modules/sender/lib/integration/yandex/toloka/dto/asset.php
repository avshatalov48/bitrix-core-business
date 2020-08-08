<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class Asset implements TolokaTransferObject
{
	/**
	 * @var String[]
	 */
	private $scriptUrls = [];

	/**
	 * Asset constructor.
	 *
	 * @param String[] $scriptUrls
	 */
	public function __construct()
	{
		$this->scriptUrls[] = '$TOLOKA_ASSETS/js/toloka-handlebars-templates.js';
	}

	/**
	 * @return String[]
	 */
	public function getScriptUrls(): array
	{
		return $this->scriptUrls;
	}


	/**
	 * @param String[] $scriptUrls
	 *
	 * @return Asset
	 */
	public function setScriptUrls(array $scriptUrls): Asset
	{
		$this->scriptUrls = $scriptUrls;

		return $this;
	}


	/**
	 * @param string $scriptUrl
	 *
	 * @return Asset
	 */
	public function addScriptUrl(string $scriptUrl): Asset
	{
		$this->scriptUrls[] = $scriptUrl;

		return $this;
	}


	public function toArray():array
	{
		return [
			'script_urls'   => $this->scriptUrls,
		];
	}
}