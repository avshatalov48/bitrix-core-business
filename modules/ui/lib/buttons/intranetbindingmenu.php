<?php

namespace Bitrix\UI\Buttons;

use Bitrix\Main\Loader;

class IntranetBindingMenu extends Button
{
	protected $componentParameters = [];
	protected $content;

	public static function isAvailable(): bool
	{
		return Loader::includeModule('intranet');
	}

	public static function createByComponentParameters(array $parameters): ?self
	{
		if(!static::isAvailable())
		{
			return null;
		}

		return new static([
			'componentParameters' => $parameters,
		]);
	}

	protected function init(array $params = [])
	{
		$this->componentParameters = $params['componentParameters'] ?? [];

		$this->content = $this->preRender();
	}

	protected function preRender(): string
	{
		$result = '';

		if(empty($this->componentParameters))
		{
			return $result;
		}

		global $APPLICATION;
		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:intranet.binding.menu',
			'',
			$this->componentParameters
		);
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	public function render($jsInit = true): string
	{
		return $this->content;
	}
}