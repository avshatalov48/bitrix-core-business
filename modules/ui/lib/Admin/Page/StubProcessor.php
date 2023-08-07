<?php

namespace Bitrix\UI\Admin\Page;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use CMain;

/**
 * Processor for working with stubs on admin pages.
 *
 * Example:
 * ```php
	$stubController = new \Bitrix\UI\Admin\Page\StubProcessor();
	if ($stubController->isShowStub())
	{
		$stubController->showStub(
			'Stub title',
			'Link to new public page'
		);
	}
 * ```
 */
class StubProcessor
{
	private const OPTION_SKIPED_PAGES = 'skip_stub_pages';

	/**
	 * Mark the page as skipped and don't show the stub anymore.
	 *
	 * @param string $page
	 *
	 * @return void
	 */
	public function addSkippedPage(string $page): void
	{
		$pages = $this->getSkippedPages();
		$pages[$page] = true;

		Option::set('ui', self::OPTION_SKIPED_PAGES, json_encode($pages));
	}

	/**
	 * List skipped pages.
	 *
	 * @return array
	 */
	private function getSkippedPages(): array
	{
		$pages = Option::get('ui', self::OPTION_SKIPED_PAGES);
		if (!empty($pages))
		{
			$pages = json_decode($pages, true);
			if (is_array($pages))
			{
				return $pages;
			}
		}

		return [];
	}

	/**
	 * Show stub?
	 *
	 * @param string|null $page
	 *
	 * @return bool
	 */
	public function isShowStub(?string $page = null): bool
	{
		$page ??= Context::getCurrent()->getRequest()->getRequestedPage();
		$skippedPages = $this->getSkippedPages();

		return !isset($skippedPages[$page]);
	}

	/**
	 * Show stub!
	 *
	 * @param string $title
	 * @param string|null $linkToNewPage
	 *
	 * @return void
	 */
	public function showStub(string $title, ?string $linkToNewPage): void
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$APPLICATION->IncludeComponent('bitrix:ui.admin.page.stub', '', [
			'TITLE' => $title,
			'STUB_PROCESSOR' => $this,
			'LINK_TO_NEW_PAGE' => $linkToNewPage,
		]);
	}
}
