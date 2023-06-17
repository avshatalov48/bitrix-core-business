<?php

namespace Bitrix\Im\V2\Common;

/**
 * The implements of the ArrayAccess interface to provide class fields access.
 * @template-implements \ArrayAccess
 */
trait FieldAccessImplementation
{
	public function offsetExists($offset): bool
	{
		return isset(static::mirrorDataEntityFields()[$offset]);
	}

	public function offsetGet($offset)
	{
		if ($field = static::mirrorDataEntityFields()[$offset])
		{
			if (isset($field['alias']))
			{
				$field = static::mirrorDataEntityFields()[$field['alias']];
			}
			if (
				($getter = $field['get'])
				&& is_string($getter)
				&& is_callable([$this, $getter])
			)
			{
				return $this->$getter();
			}

			return $this->{$field['field']};
		}

		return null;
	}

	public function offsetSet($offset, $value): void
	{
		if ($field = static::mirrorDataEntityFields()[$offset])
		{
			if (isset($field['alias']))
			{
				$field = static::mirrorDataEntityFields()[$field['alias']];
			}
			if (!isset($field['primary']))
			{
				if (
					($setter = $field['set'])
					&& is_string($setter)
					&& is_callable([$this, $setter])
				)
				{
					$this->$setter($value);
				}
				else
				{
					$this->{$field['field']} = $value;
				}
			}
		}
	}

	public function offsetUnset($offset): void
	{
		if ($field = static::mirrorDataEntityFields()[$offset])
		{
			if (isset($field['alias']))
			{
				$field = static::mirrorDataEntityFields()[$field['alias']];
			}
			if (!isset($field['primary']))
			{
				$this->{$field['field']} = null;
			}
		}
	}
}