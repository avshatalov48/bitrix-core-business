<?php

namespace Bitrix\UI\AccessRights\V2\Control;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightModel;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Configurator\RightItemConfigurator;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Control;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem\Type;

class Toggler implements Control, RightItemConfigurator
{
	protected const MIN_VALUE = '0';
	protected const MAX_VALUE = '1';

	protected ?string $defaultValue = null;
	protected ?string $nothingSelectedValue = null;
	protected ?string $emptyValue = null;

	public function __construct(
		protected readonly string $allowedValue,
		protected readonly string $deniedValue,
	)
	{
	}

	public function getType(): Type
	{
		return Type::Toggler;
	}

	public function configureRightItem(RightItem $rightItem): void
	{
		$rightItem
			->setDefaultValue($this->rightValueToUI($this->defaultValue))
			->setNothingSelectedValue($this->rightValueToUI($this->nothingSelectedValue))
			->setEmptyValue($this->rightValueToUI($this->emptyValue))
			->setMaxValue(static::MAX_VALUE)
			->setMinValue(static::MIN_VALUE);
	}

	public function convertUIValueToModelValue(string|int $value): string
	{
		if ($value === static::MAX_VALUE)
		{
			return $this->allowedValue;
		}

		return $this->deniedValue;
	}

	public function convertModelValueToUIValue(RightModel $model): string
	{
		return $this->rightValueToUI($model->getValue());
	}

	private function rightValueToUI(mixed $rightValue): string
	{
		if ($rightValue === $this->allowedValue)
		{
			return static::MAX_VALUE;
		}

		return static::MIN_VALUE;
	}

	public function default(?string $value): static
	{
		$this->defaultValue = $value;

		return $this;
	}

	public function nothingSelected(?string $value): static
	{
		$this->nothingSelectedValue = $value;

		return $this;
	}

	public function empty(?string $value): static
	{
		$this->emptyValue = $value;

		return $this;
	}
}
