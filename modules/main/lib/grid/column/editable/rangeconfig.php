<?php

namespace Bitrix\Main\Grid\Column\Editable;

use Bitrix\Main\Grid\Editor\Types;

class RangeConfig extends Config
{
	private ?float $min;
	private ?float $max;
	private ?float $step;

	/**
	 * @param string $name
	 * @param float|null $min
	 * @param float|null $max
	 * @param float|null $step
	 */
	public function __construct(string $name, ?float $min = null, ?float $max = null, ?float $step = null)
	{
		parent::__construct($name, Types::RANGE);

		$this->min = $min;
		$this->max = $max;
		$this->step = $step;
	}

	/**
	 * Minimal value.
	 *
	 * @param float $value
	 *
	 * @return self
	 */
	public function setMin(float $value): self
	{
		$this->min = $value;

		return $this;
	}

	/**
	 * Maximum value.
	 *
	 * @param float $value
	 *
	 * @return self
	 */
	public function setMax(float $value): self
	{
		$this->max = $value;

		return $this;
	}

	/**
	 * Step value.
	 *
	 * @param float $value
	 *
	 * @return self
	 */
	public function setStep(float $value): self
	{
		$this->step = $value;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		$result = parent::toArray();

		if (isset($this->min))
		{
			$result['DATA']['MIN'] = $this->min;
		}

		if (isset($this->max))
		{
			$result['DATA']['MAX'] = $this->max;
		}

		if (isset($this->step))
		{
			$result['DATA']['STEP'] = $this->step;
		}

		return $result;
	}
}
