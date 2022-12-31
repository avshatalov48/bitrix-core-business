<?php

namespace Bitrix\Location\Entity;

use Bitrix\Location\Entity\Source\Config;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Main\Context;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\StreamConverter;

/**
 * Class Source
 * @package Bitrix\Location\Entity
 * @internal
 */
abstract class Source
{
	/** @var string */
	protected $code;

	/** @var string|null */
	protected $name;

	/** @var bool */
	protected $isDefault = false;

	/** @var Config|null */
	protected $config;

	/** @var array|null  */
	protected $autocompleteReplacements = null;

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 * @return Source
	 */
	public function setCode(string $code): Source
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string|null $name
	 * @return Source
	 */
	public function setName(string $name): Source
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDefault(): bool
	{
		return $this->isDefault;
	}

	public function isAvailable(): bool
	{
		return true;
	}

	/**
	 * @return Config|null
	 */
	public function getConfig(): ?Config
	{
		return $this->config;
	}

	/**
	 * @param Config|null $config
	 * @return Source
	 */
	public function setConfig(?Config $config): Source
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * Returns replacements for the source autocomplete search
	 *
	 * @param string $languageId
	 * @return array
	 */
	public function getAutocompleteReplacements(string $languageId): array
	{
		if($this->autocompleteReplacements === null)
		{
			$this->autocompleteReplacements = [];

			$path = Context::getCurrent()->getServer()->getDocumentRoot()
				. '/bitrix/modules/location/lang/'
				. $languageId
				. '/lib/source/'
				. strtolower($this->code)
				. '/autocompletereplacements.php';

			if (File::isFileExists($path))
			{
				$this->autocompleteReplacements = StreamConverter::include($path, $languageId);
			}
		}

		return $this->autocompleteReplacements;
	}

	/**
	 * Returns source repository
	 *
	 * @return IRepository
	 */
	abstract public function makeRepository(): IRepository;

	/**
	 * Is used for the transferring params to JS Source
	 *
	 * @return array
	 */
	abstract public function getJSParams(): array;

	/**
	 * @param string $bitrixLang
	 * @return string
	 */
	abstract public function convertLang(string $bitrixLang): string;
}
