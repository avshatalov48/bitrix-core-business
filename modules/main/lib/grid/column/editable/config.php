<?php

namespace Bitrix\Main\Grid\Column\Editable;

use Bitrix\Main\Grid\Editor\Types;

/**
 * @see `components/bitrix/main.ui.grid/templates/.default/src/js/inline-editor.js` for details about config struct.
 */
class Config
{
	private string $name;
	private string $type;
	private ?string $placeholder;
	private ?bool $disabled;

	/**
	 * @see \Bitrix\Main\Grid\Editor\Types
	 *
	 * @param string $type constant from `Types` class
	 */
	public function __construct(string $name, ?string $type = null)
	{
		$this->name = $name;
		$this->type = $type ?? Types::TEXT;
	}

	/**
	 * Field type.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Name field.
	 *
	 * @param string $value
	 *
	 * @return self
	 */
	public function setName(string $value): self
	{
		$this->name = $value;

		return $this;
	}

	/**
	 * Name field.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Field placeholder.
	 *
	 * @param string $value
	 *
	 * @return self
	 */
	public function setPlaceholder(string $value): self
	{
		$this->placeholder = $value;

		return $this;
	}

	/**
	 * Field is disabled.
	 *
	 * @param bool $value
	 *
	 * @return self
	 */
	public function setDisabled(bool $value): self
	{
		$this->disabled = $value;

		return $this;
	}

	/**
	 * Config for JS class `BX.Grid.InlineEditor`
	 *
	 * @return array
	 * @psalm-return array<string, mixed>
	 */
	public function toArray(): array
	{
		$result = [
			'NAME' => $this->name,
			'TYPE' => $this->type,
		];

		if (isset($this->placeholder))
		{
			$result['PLACEHOLDER'] = $this->placeholder;
		}

		if (isset($this->disabled))
		{
			$result['DISABLED'] = $this->disabled;
		}

		return $result;
	}
}
