<?php

namespace Bitrix\Main\Grid\Row\Assembler\Field;

use Bitrix\Main\Grid\Row\FieldAssembler;
use Stringable;

/**
 * Assembler of string fields.
 */
class StringFieldAssembler extends FieldAssembler
{
	/**
	 * @inheritDoc
	 *
	 * @param mixed $value
	 *
	 * @return string|null
	 */
	protected function prepareColumn($value): ?string
	{
		if (is_null($value))
		{
			return null;
		}

		if (
			is_scalar($value)
			|| $value instanceof Stringable
		)
		{
			return htmlspecialcharsbx((string)$value);
		}

		return '';
	}
}
