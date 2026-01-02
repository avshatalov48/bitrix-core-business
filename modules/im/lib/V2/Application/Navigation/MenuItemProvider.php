<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Application\Navigation;

use Bitrix\Im\V2\Application\Features;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Marketplace\Application;
use Bitrix\Im\V2\Marketplace\Placement;
use Bitrix\Im\V2\Permission;
use Bitrix\Im\V2\Permission\GlobalAction;
use Bitrix\Main\Localization\Loc;

/**
 * Class for creating navigation menu items.
 *
 * Provides method for creating menu items list based on configuration
 * including menu items for market applications.
 */
class MenuItemProvider
{
	use ContextCustomer;

	protected Features $applicationFeatures;

	protected array $phoneSettings;

	public function __construct()
	{
		$this->applicationFeatures = Features::get();
		$this->phoneSettings = \CIMMessenger::getPhoneSettings();
	}

	/**
	 * Returns array of all menu items objects.
	 *
	 * @return MenuItem[]
	 */
	public function getMenuItems(): array
	{
		return [
			new MenuItem(
				id: 'chat',
				text: Loc::getMessage('IM_NAVIGATION_MENU_CHATS'),
			),
			new MenuItem(
				id: 'copilot',
				text: Loc::getMessage('IM_NAVIGATION_MENU_COPILOT'),
				isVisible: $this->applicationFeatures->copilotAvailable,
			),
			new MenuItem(
				id: 'tasksTask',
				text: Loc::getMessage('IM_NAVIGATION_MENU_TASKS'),
				isVisible: $this->applicationFeatures->isTasksRecentListAvailable,
			),
			new MenuItem(
				id: 'collab',
				text: Loc::getMessage('IM_NAVIGATION_MENU_COLLAB'),
				isVisible: $this->applicationFeatures->collabAvailable,
			),
			new MenuItem(
				id: 'channel',
				text: Loc::getMessage('IM_NAVIGATION_MENU_CHANNELS'),
				isVisible: $this->isGlobalActionPermitted(GlobalAction::GetChannels),
			),
			new MenuItem(
				id: 'openlines',
				text: Loc::getMessage('IM_NAVIGATION_MENU_OPENLINES'),
				isVisible: (
					!$this->applicationFeatures->openLinesV2
					&& $this->isGlobalActionPermitted(GlobalAction::GetOpenlines)
				),
			),
			new MenuItem(
				id: 'openlinesV2',
				text: Loc::getMessage('IM_NAVIGATION_MENU_OPENLINES'),
				isVisible: (
					$this->applicationFeatures->openLinesV2
					&& $this->isGlobalActionPermitted(GlobalAction::GetOpenlines)
				),
			),
			new MenuItem(
				id: 'notification',
				text: Loc::getMessage('IM_NAVIGATION_MENU_NOTIFICATIONS'),
				isVisible: !$this->applicationFeatures->isNotificationsStandalone,
			),
			new MenuItem(
				id: 'call',
				text: Loc::getMessage('IM_NAVIGATION_MENU_CALLS_V2'),
				isVisible: (
					$this->phoneSettings['phoneEnabled']
					&& $this->phoneSettings['canPerformCallsByUser']
				),
			),
			new MenuItem(
				id: 'market',
				text: Loc::getMessage('IM_NAVIGATION_MENU_MARKET_TITLE'),
				isVisible: $this->isGlobalActionPermitted(GlobalAction::GetMarket),
			),
			...$this->getMarketAppMenuItems(),
			new MenuItem(
				id: 'settings',
				text: Loc::getMessage('IM_NAVIGATION_MENU_SETTINGS'),
			),
		];
	}

	/**
	 * Creates menu item for each market app, placed in navigation
	 * and returns array of menu item objects.
	 *
	 * @return MenuItem[]
	 */
	public function getMarketAppMenuItems(): array
	{
		$applications = (new Application())->getApplications([Placement::IM_NAVIGATION]);
		usort($applications, function($a, $b) {
			return $a->getOrder() - $b->getOrder();
		});

		$menuItems = [];
		foreach ($applications as $application)
		{
			$menuItems[] = new MenuItem(
				id: 'market',
				text: $application->getTitle(),
				entityId: $application->getId(),
			);
		}

		return $menuItems;
	}

	protected function isGlobalActionPermitted(GlobalAction $action): bool
	{
		return Permission::canDoGlobalAction(
			$this->getContext()->getUserId(),
			$action,
			null,
		);
	}
}
