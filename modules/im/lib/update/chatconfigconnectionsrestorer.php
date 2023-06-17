<?php

namespace Bitrix\Im\Update;

use Bitrix\Im\Configuration\Configuration;
use Bitrix\Im\Configuration\Department;
use Bitrix\Im\Configuration\EventHandler;
use Bitrix\Im\Configuration\General;
use Bitrix\Im\Configuration\Manager;
use Bitrix\Im\Configuration\Notification;
use Bitrix\Im\Model\OptionAccessTable;
use Bitrix\Im\Model\OptionGroupTable;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use phpDocumentor\Reflection\Types\This;

class ChatConfigConnectionsRestorer extends Stepper
{
	protected static $moduleId = 'im';
	private const OPTION_NAME = 'chat_config_restore';

	function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}
		$haveEventHandlersRestored = Option::get(self::$moduleId, 'have_event_handlers_restored', 'N');

		if ($haveEventHandlersRestored !== 'Y')
		{
			$this->restoreEventHandlers();
		}

		$params = Option::get(self::$moduleId, self::OPTION_NAME, '');
		$params = $params !== '' ? @unserialize($params, ['allowed_classes' => false]) : [];
		$params = is_array($params) ? $params : [];

		if (empty($params))
		{
			$params = [
				'default_group_id' => $this->getDefaultGroupId(),
				'last_recovered_user' => 0,
			];
		}

		$notRecoveredUserIdList =
			\Bitrix\Main\UserTable::query()
				->addSelect('ID')
				->registerRuntimeField(
					'OPTION_USER',
					(new \Bitrix\Main\ORM\Fields\Relations\Reference(
						'OPTION_USER',
						\Bitrix\Im\Model\OptionUserTable::getEntity(),
						\Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.USER_ID')
					))->configureJoinType(\Bitrix\Main\ORM\Query\Join::TYPE_LEFT)
				)
				->whereNull('OPTION_USER.USER_ID')
				->where('IS_REAL_USER', 'Y')
				->where('ID', '>', $params['last_recovered_user'])
				->addOrder('ID')
				->setLimit(100)
				->fetchAll()
		;
		if (empty($notRecoveredUserIdList))
		{
			Option::delete(self::$moduleId, ['name' => self::OPTION_NAME]);
			Option::delete(self::$moduleId, ['name' => 'have_event_handlers_restored']);

			return false;
		}

		$notRecoveredUserIdList = array_map(static fn($user) => (int)$user['ID'], $notRecoveredUserIdList);

		$userPresetList =
			OptionGroupTable::query()
				->addSelect('ID')
				->addSelect('USER_ID')
				->whereIn('USER_ID', $notRecoveredUserIdList)
				->fetchAll()
		;

		$flippedNotRecoveredUserIdList = array_flip($notRecoveredUserIdList);
		foreach ($userPresetList as $userPreset)
		{
			OptionUserTable::add([
				'USER_ID' => (int)$userPreset['USER_ID'],
				'GENERAL_GROUP_ID' => (int)$userPreset['ID'],
				'NOTIFY_GROUP_ID' => (int)$userPreset['ID'],
			]);

			$params['last_recovered_user'] = (int)$userPreset['ID'];
			unset($flippedNotRecoveredUserIdList[(int)$userPreset['USER_ID']]);
		}

		foreach (array_flip($flippedNotRecoveredUserIdList) as $userId)
		{
			OptionUserTable::add([
				'USER_ID' => $userId,
				'GENERAL_GROUP_ID' => $params['default_group_id'],
				'NOTIFY_GROUP_ID' => $params['default_group_id'],
			]);

			$params['last_recovered_user'] = $userId;
		}


		$params = serialize($params);
		Option::set(self::$moduleId, self::OPTION_NAME, $params);

		return true;
	}

	private function restoreEventHandlers(): void
	{
		$eventHandlerClass = '\\' . EventHandler::class;
		$eventManager = EventManager::getInstance();

		$this->restoreEventHandler($eventManager, 'OnAfterUserAdd', $eventHandlerClass);
		$this->restoreEventHandler($eventManager, 'OnAfterUserUpdate', $eventHandlerClass);
		$this->restoreEventHandler($eventManager, 'OnAfterUserDelete', $eventHandlerClass);

		Option::set(self::$moduleId, 'have_event_handlers_restored', 'Y');
	}

	private function restoreEventHandler(EventManager $eventManager, string $event, string $eventHandlerClass): void
	{
		$eventHandler = $this->findEventHandler($eventManager, $event);
		if (empty($eventHandler))
		{
			$eventManager->registerEventHandler('main', $event, 'im', $eventHandlerClass, $event);
		}
	}

	private function findEventHandler(EventManager $eventManager, string $event): array
	{
		return array_filter(
			$eventManager->findEventHandlers('main', $event),
			static fn($handler) =>
				($handler['TO_MODULE_ID'] === 'im')
				&& (mb_strpos($handler['TO_CLASS'], 'Configuration') !== false)
		);
	}

	private function getDefaultGroupId(): int
	{
		$defaultGroupId =
			OptionGroupTable::query()
				->addSelect('ID')
				->where('NAME', Configuration::DEFAULT_PRESET_NAME)
				->fetch()
		;

		if ($defaultGroupId)
		{
			Option::set('im', Configuration::DEFAULT_PRESET_SETTING_NAME, (int)$defaultGroupId['ID']);

			return (int)$defaultGroupId['ID'];
		}

		return $this->installDefaultPreset();
	}

	public function installDefaultPreset(): int
	{
		$defaultGroupId =
			\Bitrix\Im\Model\OptionGroupTable::add([
				'NAME' => Configuration::DEFAULT_PRESET_NAME,
				'SORT' => 0,
				'CREATE_BY_ID' => 0,
			])
				->getId()
		;

		$generalDefaultSettings = General::getDefaultSettings();
		General::setSettings($defaultGroupId, $generalDefaultSettings);

		$notifySettings = Notification::getSimpleNotifySettings($generalDefaultSettings);
		Notification::setSettings($defaultGroupId, $notifySettings);

		if (Loader::includeModule('intranet'))
		{
			$topDepartmentId = Department::getTopDepartmentId();
			OptionAccessTable::add([
				'GROUP_ID' => $defaultGroupId,
				'ACCESS_CODE' => $topDepartmentId ? 'DR' . $topDepartmentId : 'AU'
			]);
		}

		Option::set('im', Configuration::DEFAULT_PRESET_SETTING_NAME, (int)$defaultGroupId);

		return (int)$defaultGroupId;
	}
}