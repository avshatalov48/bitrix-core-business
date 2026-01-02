<?php

namespace Bitrix\Main\Engine\Response\Render;

use Bitrix\Main\Application;
use Bitrix\Main\HttpResponse;
use CMain;

abstract class Base extends HttpResponse
{
	abstract protected function renderContent(): void;

	protected function __construct(
		bool $withSiteTemplate,
	)
	{
		parent::__construct();

		$this->fillContent($withSiteTemplate);
	}

	final protected function fillContent(bool $withSiteTemplate): void
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$APPLICATION->RestartBuffer();

		if ($withSiteTemplate)
		{
			$this->includeHeader();
		}
		else
		{
			$APPLICATION->ShowAjaxHead();
		}

		$this->renderContent();

		if ($withSiteTemplate)
		{
			$this->includeFooter();
		}

		$content = $APPLICATION->EndBufferContentMan();

		$this->setContent($content);
	}

	final protected function includeHeader(): void
	{
		require Application::getDocumentRoot() . '/bitrix/modules/main/include/prolog_after.php';
	}

	final protected function includeFooter(): void
	{
		require Application::getDocumentRoot() . '/bitrix/modules/main/include/epilog_before.php';
	}
}
