<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Application\Navigation;

use Bitrix\Im\Revision;
use Bitrix\Main\DI\ServiceLocator;

/**
 * Communication navigation menu service class.
 * Provides methods for retrieving communication navigation menu items.
 */
class Menu
{
	public function __construct(
		protected MenuItemProvider $menuItemProvider,
	) {}

	/**
	 * Returns instance of the Menu class via Service Locator.
	 *
	 * @return Menu
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public static function getInstance(): Menu
	{
		return ServiceLocator::getInstance()->get(Menu::class);
	}

	/**
	 * Returns communication navigation menu items, filtered by visibility condition,
	 * formatted to array structure.
	 *
	 * @return array<array{id: string, text: string, entityId: int|null}>
	 */
	public function getMenuItems(): array
	{
		$menuItemsFormatted = [];
		foreach ($this->getAllMenuItems() as $menuItem)
		{
			if ($menuItem->isVisible())
			{
				$menuItemsFormatted[] = $menuItem->toArray();
			}
		}

		return $menuItemsFormatted;
	}

	/**
	 * Returns current menu revision number.
	 *
	 * @return int
	 */
	public static function getRevision(): int
	{
		return Revision::getMenu();
	}

	/**
	 * Returns all possible communication navigation menu item objects
	 *
	 * @return MenuItem[]
	 */
	protected function getAllMenuItems(): array
	{
		return $this->menuItemProvider->getMenuItems();
	}
}
