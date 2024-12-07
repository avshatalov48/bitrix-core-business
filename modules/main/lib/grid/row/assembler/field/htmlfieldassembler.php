<?php

namespace Bitrix\Main\Grid\Row\Assembler\Field;

use Bitrix\Main\Grid\Row\FieldAssembler;
use Stringable;

/**
 * Assembler of HTML fields.
 */
class HtmlFieldAssembler extends FieldAssembler
{
	/**
	 * Returns the value "as is".
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
			return (string)$value;
		}

		return '';
	}
}
