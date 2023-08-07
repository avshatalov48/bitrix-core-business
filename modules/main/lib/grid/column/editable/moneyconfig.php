<?php

namespace Bitrix\Main\Grid\Column\Editable;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Loader;

/**
 * Configuration for `input+dropdown` fields (usually it's money).
 */
class MoneyConfig extends Config
{
	/**
	 * @var array
	 * @psalm-var array<string|int, mixed>
	 */
	private array $currencyList;
	private ?bool $isHtml;

	/**
	 * @param array|null $currencyList in format `[value => name]`
	 */
	public function __construct(string $name, ?array $currencyList = null)
	{
		parent::__construct($name, Types::MONEY);

		if (isset($currencyList))
		{
			$this->currencyList = $currencyList;
		}
		elseif (Loader::includeModule('currency'))
		{
			$this->currencyList = CurrencyManager::getSymbolList();
			$this->setHtml(true);
		}
		else
		{
			$this->currencyList = [];
		}
	}

	/**
	 * Control content is HTML.
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
	 * Currency list as dropdown.
	 *
	 * @return array[]
	 */
	private function getCurrenyListAsDropdown(): array
	{
		$result = [];

		foreach ($this->currencyList as $value => $name)
		{
			$result[] = [
				'VALUE' => $value,
				'NAME' => $name,
			];
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		$result = parent::toArray();

		$result['CURRENCY_LIST'] = $this->getCurrenyListAsDropdown();

		if (isset($this->isHtml))
		{
			$result['HTML_ENTITY'] = $this->isHtml;
		}

		return $result;
	}
}
