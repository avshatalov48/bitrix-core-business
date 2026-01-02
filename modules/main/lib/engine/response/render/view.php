<?php

namespace Bitrix\Main\Engine\Response\Render;

use CMain;

/**
 * Response with static view content.
 *
 * @see \Bitrix\Main\Routing\Controllers\PublicPageController for using static page on router.
 */
final class View extends Base
{
	public function __construct(
		private string $pathOnDocumentRoot,
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

		$APPLICATION->IncludeFile(
			$this->pathOnDocumentRoot,
			$this->params,
			[],
		);
	}
}
