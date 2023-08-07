<?php

namespace Bitrix\Main\Grid\Column\Editable;

use Bitrix\Main\Grid\Editor\Types;

/**
 * Configuration for editor's custom fields (for example using components).
 */
class CustomConfig extends Config
{
	private ?string $html;

	/**
	 * @param string $name
	 * @param string|null $html
	 */
	public function __construct(string $name, ?string $html = null)
	{
		parent::__construct($name, Types::CUSTOM);

		$this->html = $html;
	}

	/**
	 * HTML code.
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	public function setHtml(string $value)
	{
		$this->html = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		$result = parent::toArray();

		if (isset($this->html))
		{
			$result['HTML'] = $this->html;
		}

		return $result;
	}
}
