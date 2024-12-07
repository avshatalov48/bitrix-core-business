<?php

namespace Bitrix\Main\Grid\Row\Assembler\Field;

use Bitrix\Main\Context;
use Bitrix\Main\Context\Culture;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Grid\Settings;

/**
 * Assembler of number fields.
 *
 * Example of creation for an integer:
 * ```php
 * $assembler = new NumberFieldAssembler(false, ['ID', 'COUNT']);
 * ```
 *
 * Example of creation for an float:
 * ```php
 * $assembler = new NumberFieldAssembler(true, ['WEIGHT', 'AMOUNT']);
 * ```
 *
 * Example of creation for culture of current context:
 * ```php
 * $assembler = NumberFieldAssembler::createForContext(true, ['COUNT', 'AMOUNT']);
 * ```
 *
 * @see \Bitrix\Main\Context
 * @see \Bitrix\Main\Context\Culture
 */
class NumberFieldAssembler extends FieldAssembler
{
	private bool $isFloat;
	private ?Culture $culture;

	/**
	 * @param bool $isFloat
	 * @param array $columnIds
	 * @param Settings|null $settings
	 * @param Culture|null $culture
	 */
	public function __construct(bool $isFloat, array $columnIds, ?Settings $settings = null, ?Culture $culture = null)
	{
		$this->isFloat = $isFloat;
		$this->culture = $culture;

		parent::__construct($columnIds, $settings);
	}

	/**
	 * Create assembler for culture of context.
	 *
	 * @param bool $isFloat
	 * @param array $columnIds
	 * @param Settings|null $settings
	 * @param Context|null $context if is null - use is current context.
	 *
	 * @return self
	 */
	public static function createForContext(bool $isFloat, array $columnIds, ?Settings $settings = null, Context $context = null): self
	{
		$context ??= Context::getCurrent();

		return new static(
			$isFloat,
			$columnIds,
			$settings,
			$context?->getCulture()
		);
	}

	/**
	 * @inheritDoc
	 *
	 * @param mixed $value
	 *
	 * @return string|int|float|null returns formatted string if sets culture. If the value is not a number, then 0 is returned.
	 */
	protected function prepareColumn($value): string|int|float|null
	{
		if (is_null($value))
		{
			return null;
		}
		elseif (!is_numeric($value))
		{
			return $this->isFloat ? 0.0 : 0;
		}

		if ($this->isFloat)
		{
			$value = (float)$value;
		}
		else
		{
			$value = (int)$value;
		}

		if (isset($this->culture))
		{
			return number_format(
				$value,
				$this->culture->getNumberDecimals(),
				$this->culture->getNumberDecimalSeparator(),
				$this->culture->getNumberThousandsSeparator(),
			);
		}

		return $value;
	}
}
