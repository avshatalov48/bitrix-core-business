<?php

namespace Bitrix\Main\Html;

use Bitrix\Main\Text\HtmlFilter;
use CUtil;

/**
 * HTML attributes.
 */
final class Attributes
{
	/**
	 * @var array
	 * @psalm-var array<string, mixed>
	 */
	private array $attributes = [];

	/**
	 * Set attribute.
	 *
	 * For `data-*` attributes see method `setData`
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return self
	 */
	public function set(string $name, $value): self
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Set data attribute.
	 *
	 * For other attributes see method `set`
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return self
	 */
	public function setData(string $name, $value): self
	{
		$this->set('data-' . $name, $value);

		return $this;
	}

	/**
	 * Get attribute.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get(string $name)
	{
		return $this->attributes[$name] ?? null;
	}

	/**
	 * Remove attribute.
	 *
	 * @param string $name
	 *
	 * @return mixed returns removed attribute value.
	 */
	public function remove(string $name)
	{
		try
		{
			return $this->get($name);
		}
		finally
		{
			unset($this->attributes[$name]);
		}
	}

	/**
	 * Generates escaped HTML string with attributes.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$result = '';

		if (!empty($this->attributes))
		{
			$result = ' ';
			foreach ($this->attributes as $name => $value)
			{
				if (!isset($value))
				{
					continue;
				}

				$escapedName = HtmlFilter::encode($name);

				$isDataAttribute = stripos($name, 'data-') === 0;
				if ($isDataAttribute)
				{
					if (is_array($value))
					{
						$escapedValue = '(' . HtmlFilter::encode(CUtil::PhpToJSObject($value)) . ')';
					}
					elseif (is_bool($value))
					{
						$escapedValue = $value ? 'true' : 'false';
					}
					else
					{
						$escapedValue = HtmlFilter::encode((string)$value);
					}
				}
				else
				{
					$escapedValue = HtmlFilter::encode((string)$value);
				}

				$result .= $escapedName . '="' . $escapedValue . '"';
			}

			$result .= ' ';
		}

		return $result;
	}
}
