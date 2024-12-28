<?php

namespace Bitrix\Im\V2\Application;

use Bitrix\Im\Promotion;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\ImOpenLines\V2\Queue\Queue;
use Bitrix\ImOpenLines\V2\Status\Status;
use Bitrix\Im\V2\TariffLimit\Limit;

class Config implements \JsonSerializable
{
	use ContextCustomer;

	private const NODE = '#bx-im-external-recent-list';

	private bool $isDesktop = false;

	public function setDesktopFlag(bool $isDesktop): self
	{
		$this->isDesktop = $isDesktop;

		return $this;
	}


	public function jsonSerialize(): array
	{
		return [
			'node' => self::NODE,
			'preloadedList' => $this->getPreloadedList(),
			'permissions' => $this->getPermissions(),
			'marketApps' => $this->getMarketApps(),
			'currentUser' => $this->getCurrentUser(),
			'loggerConfig' => $this->getLoggerConfig(),
			'counters' => $this->getCounters(),
			'settings' => $this->getSettings(),
			'promoList' => $this->getPromoList(),
			'phoneSettings' => $this->getPhoneSettings(),
			'sessionTime' => $this->getSessionTime(),
			'featureOptions' => $this->getFeatureOptions(),
			'sessionStatusMap' => $this->getSessionStatusMap(),
			'tariffRestrictions' => $this->getTariffRestrictions(),
		];
	}

	protected function getPreloadedList(): array
	{
		return \Bitrix\Im\Recent::getList($this->getContext()->getUserId(), [
			'SKIP_NOTIFICATION' => 'Y',
			'SKIP_OPENLINES' => 'Y',
			'JSON' => 'Y',
			'GET_ORIGINAL_TEXT' => 'Y',
			'SHORT_INFO' => 'Y',
		]) ?: [];
	}

	protected function getPermissions(): array
	{
		$permissionManager = new \Bitrix\Im\V2\Permission(true);

		return [
			'byChatType' => $permissionManager->getByChatTypes(),
			'byUserType' => $permissionManager->getByUserTypes(),
			'actionGroups' => $permissionManager->getActionGroupDefinitions(),
			'actionGroupsDefaults' => $permissionManager->getDefaultPermissionForGroupActions()
		];
	}

	protected function getMarketApps(): array
	{
		return (new \Bitrix\Im\V2\Marketplace\Application())->toRestFormat();
	}

	protected function getCurrentUser(): array
	{
		$currentUser =  \CIMContactList::GetUserData([
			'ID' => $this->getContext()->getUserId(),
			'PHONES' => 'Y',
			'SHOW_ONLINE' => 'N',
			'EXTRA_FIELDS' => 'Y',
			'DATE_ATOM' => 'Y'
		])['users'][$this->getContext()->getUserId()];
		$currentUser['isAdmin'] = $this->getContext()->getUser()->isAdmin();

		return $currentUser;
	}

	protected function getLoggerConfig(): array
	{
		return \Bitrix\Im\Settings::getLoggerConfig();
	}

	protected function getCounters(): array
	{
		return (new \Bitrix\Im\V2\Message\CounterService($this->getContext()->getUserId()))->get();
	}

	protected function getSettings(): array
	{
		$settings = (new \Bitrix\Im\V2\Settings\UserConfiguration($this->getContext()->getUserId()))->getGeneralSettings();
		$settings['notifications'] = (new \Bitrix\Im\V2\Settings\UserConfiguration($this->getContext()->getUserId()))->getNotifySettings();

		return $settings;
	}

	protected function getPromoList(): array
	{
		$promoType = $this->isDesktop ? Promotion::DEVICE_TYPE_DESKTOP : Promotion::DEVICE_TYPE_BROWSER;

		return Promotion::getActive($promoType);
	}

	protected function getPhoneSettings(): array
	{
		return \CIMMessenger::getPhoneSettings();
	}

	protected function getSessionTime(): int
	{
		return (new \Bitrix\Im\V2\UpdateState())->getInterval() ?? 0;
	}

	protected function getFeatureOptions(): Features
	{
		return Features::get();
	}

	protected function getSessionStatusMap(): array
	{
		if (!\Bitrix\Main\Loader::includeModule('imopenlines'))
		{
			return [];
		}

		return Status::getMap();
	}

	protected function getTariffRestrictions(): array
	{
		return Limit::getInstance()->getRestrictions();
	}
}
