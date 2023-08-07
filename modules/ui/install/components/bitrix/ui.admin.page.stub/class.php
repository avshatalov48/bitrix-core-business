<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;
use Bitrix\UI\Admin\Page\StubProcessor;

/**
 * @see \Bitrix\UI\Admin\Page\StubProcessor for more using details.
 */
class UiAdminPageStubComponent extends CBitrixComponent
{
	private const REQUEST_PARAM_SKIP_PAGE = 'skip_stub';

	/**
	 * @inheritDoc
	 */
	public function onPrepareComponentParams($arParams)
	{
		if (
			!isset($arParams['STUB_PROCESSOR'])
			|| ($arParams['STUB_PROCESSOR'] instanceof StubProcessor) === false
		)
		{
			$arParams['STUB_PROCESSOR'] = new StubProcessor();
		}

		return $arParams;
	}

	/**
	 * @inheritDoc
	 */
	public function executeComponent()
	{
		$currentPage = $this->getCurrentPage();
		if ($this->processSkipRequest($currentPage))
		{
			return;
		}

		$this->initResult();
		$this->includeComponentTemplate();
	}

	private function getStubProcessor(): StubProcessor
	{
		return $this->arParams['STUB_PROCESSOR'];
	}

	private function getCurrentPage(): string
	{
		return Context::getCurrent()->getRequest()->getRequestedPage() ?? '';
	}

	private function processSkipRequest(string $currentPage): void
	{
		$request = Context::getCurrent()->getRequest();
		$isSkipPage = $request->get(self::REQUEST_PARAM_SKIP_PAGE) === 'Y';
		if ($isSkipPage)
		{
			$this->getStubProcessor()->addSkippedPage($currentPage);

			$uri = new Uri(
				(string)$request->getRequestUri()
			);
			$uri->deleteParams([
				self::REQUEST_PARAM_SKIP_PAGE,
			]);

			LocalRedirect($uri);
		}
	}

	private function getSkipStubUrl(): string
	{
		$uri = new Uri(
			(string)Context::getCurrent()->getRequest()->getRequestUri()
		);
		$uri->addParams([
			self::REQUEST_PARAM_SKIP_PAGE => 'Y',
		]);

		return (string)$uri;
	}

	private function initResult(): void
	{
		$this->arResult['TITLE'] = (string)($this->arParams['~TITLE'] ?? $this->arParams['TITLE'] ?? null);
		$this->arResult['LINK_TO_SKIP_STUB'] = $this->getSkipStubUrl();
		$this->arResult['LINK_TO_NEW_PAGE'] = $this->arParams['LINK_TO_NEW_PAGE'] ?? null;
	}
}
