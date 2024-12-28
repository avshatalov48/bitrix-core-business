<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command\ValueObject;

use Bitrix\Main\Validation\Rule\ElementsType;
use Bitrix\Socialnetwork\Collab\Control\Command\Attribute\ValidatableElements;
use Bitrix\Socialnetwork\Collab\Control\Option\AbstractOption;
use Bitrix\Socialnetwork\Collab\Control\Option\OptionFactory;
use Bitrix\Socialnetwork\Control\Command\ValueObject\CreateObjectInterface;
use Bitrix\Socialnetwork\Control\Command\ValueObject\CreateWithDefaultValueInterface;
use Bitrix\Socialnetwork\ValueObjectInterface;

class CollabOptions implements ValueObjectInterface, CreateWithDefaultValueInterface, CreateObjectInterface
{
	#[ElementsType(className: AbstractOption::class)]
	#[ValidatableElements]
	protected array $options = [];

	public static function create(mixed $data): static
	{
		$value = new static();

		$data = array_merge(OptionFactory::DEFAULT_OPTIONS, $data);
		foreach ($data as $optionName => $optionValue)
		{
			$option = AbstractOption::create([$optionName => $optionValue]);
			$value->addOption($option);
		}

		return $value;
	}

	public static function createWithDefaultValue(): static
	{
		$value = new static();

		foreach (OptionFactory::DEFAULT_OPTIONS as $optionName => $optionValue)
		{
			$option = AbstractOption::create([$optionName => $optionValue]);
			$value->addOption($option);
		}

		return $value;
	}

	public function __construct(AbstractOption ...$options)
	{
		$this->options = $options;
	}

	/**
	 * @return AbstractOption[]
	 */
	public function getValue(): array
	{
		return $this->options;
	}

	public function addOption(AbstractOption $option): void
	{
		$this->options[] = $option;
	}
}
