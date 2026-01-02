<?php

namespace Bitrix\Main\Engine\Response\Render;

use CMain;

/**
 * Response with component on site template.
 *
 * @see \Bitrix\Main\Engine\Response\Component for AJAX responses.
 */
final class Component extends Base
{
	public function __construct(
		private string $name,
		private string $template,
		private array $params = [],
		bool $withSiteTemplate = true,
	)
	{
		parent::__construct($withSiteTemplate);
	}

	protected function renderContent(): void
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$APPLICATION->IncludeComponent(
			$this->name,
			$this->template,
			$this->params
		);
	}
}
