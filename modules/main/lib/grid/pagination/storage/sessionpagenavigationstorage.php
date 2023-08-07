<?php

namespace Bitrix\Main\Grid\Pagination\Storage;

use Bitrix\Main\Application;
use Bitrix\Main\Data\LocalStorage\SessionLocalStorage;
use Bitrix\Main\Grid\Pagination\PageNavigationStorage;
use Bitrix\Main\UI\PageNavigation;

final class SessionPageNavigationStorage implements PageNavigationStorage
{
	private const STORAGE_ID = 'grid-pagination-storage';
	private SessionLocalStorage $storage;

	public function __construct(?SessionLocalStorage $storage = null)
	{
		$this->storage =
			$storage
			?? Application::getInstance()->getSessionLocalStorageManager()->get(self::STORAGE_ID)
		;
	}

	public function fill(PageNavigation $pagination): void
	{
		$data = $this->storage->get($pagination->getId());
		if (is_array($data) && isset($data['current']))
		{
			$pagination->setCurrentPage((int)$data['current']);
		}
	}

	public function save(PageNavigation $pagination): void
	{
		$this->storage->set(
			$pagination->getId(),
			[
				'current' => $pagination->getCurrentPage(),
			]
		);
	}
}
