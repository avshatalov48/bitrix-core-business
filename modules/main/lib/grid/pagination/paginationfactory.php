<?php

namespace Bitrix\Main\Grid\Pagination;

use Bitrix\Main\Grid\Grid;
use Bitrix\Main\UI\PageNavigation;

final class PaginationFactory
{
	public function __construct(
		private Grid $grid,
		private ?PageNavigationStorage $storage,
	)
	{}

	public function create(): PageNavigation
	{
		$navId = $this->grid->getId() . '_nav';
		$navParams = $this->grid->getOptions()->GetNavParams();
		$pageSizes = [
			5,
			10,
			20,
			50,
			100,
		];

		$pagination = new PageNavigation($navId);
		$pagination->allowAllRecords(false);
		$pagination->setPageSize($navParams['nPageSize']);
		$pagination->setPageSizes($pageSizes);
		$pagination->setCurrentPage(1);

		return $pagination;
	}
}
