<?php

namespace Bitrix\Main\Grid\Action;

use Bitrix\Main\Grid\Pagination\PageNavigationStorage;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;

/**
 * Pagination grid action.
 *
 * Changes current page and saves it to storage, for correct working interactive grid cells (for example, using components).
 */
final class PaginationAction implements Action
{
	private PageNavigation $pagination;
	private ?PageNavigationStorage $storage;

	/**
	 * @inheritDoc
	 *
	 * @return string
	 */
	public static function getId(): string
	{
		return 'pagination';
	}

	/**
	 * @param PageNavigation $pagination
	 * @param PageNavigationStorage|null $storage if not setted, action only change page.
	 */
	public function __construct(PageNavigation $pagination, ?PageNavigationStorage $storage)
	{
		$this->pagination = $pagination;
		$this->storage = $storage;
	}

	/**
	 * @inheritDoc
	 *
	 *  and saves this in storage.
	 *
	 * @param HttpRequest $request
	 *
	 * @return Result|null
	 */
	public function processRequest(HttpRequest $request): ?Result
	{
		if (\Bitrix\Main\Context::getCurrent()->getRequest() !== $request)
		{
			trigger_error('Pagination working only request from context', E_USER_WARNING);
		}

		$tmp = clone $this->pagination;
		$tmp->setCurrentPage(1);
		$tmp->initFromUri();

		if ($this->pagination->getCurrentPage() !== $tmp->getCurrentPage())
		{
			$this->pagination->setCurrentPage($tmp->getCurrentPage());

			if (isset($this->storage))
			{
				$this->storage->save($this->pagination);
			}
		}

		return new Result();
	}
}
