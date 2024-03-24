<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Disk\Driver;
use Bitrix\Disk\Folder;
use Bitrix\Disk\Storage;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Item;
use Bitrix\Tasks\Ui;

class SpacesContentComponent extends \CBitrixComponent
{
	private $application;

	public function __construct($component = null)
	{
		parent::__construct($component);

		global $APPLICATION;
		$this->application = $APPLICATION;
	}

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));

		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params['PAGE'] = $params['PAGE'] ?? 'user_discussions';
		$params['PAGE_TYPE'] = $params['PAGE_TYPE'] ?? 'user';
		$params['PAGE_ID'] = $params['PAGE_ID'] ?? 'discussions';

		$params['GROUP_ID'] = (is_numeric($params['GROUP_ID'] ?? null) ? (int) $params['GROUP_ID'] : 0);

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->arResult['page'] = $this->arParams['PAGE'];
			$this->arResult['pageType'] = $this->arParams['PAGE_TYPE'];
			$this->arResult['pageId'] = $this->arParams['PAGE_ID'];

			$this->includeModules($this->arResult['pageId']);

			$this->arResult['groupId'] = $this->arParams['GROUP_ID'];

			$userId = Helper\User::getCurrentUserId();
			$this->arResult['userId'] = $userId;

			if (Context::getCurrent()->getRequest()->get('empty-state') === 'enabled')
			{
				$provider = new Bitrix\Socialnetwork\Space\List\Provider($userId);
				$space = $provider->getSpaceById($this->arResult['groupId']);

				if (is_null($space))
				{
					throw new SystemException('Space is not found', 404);
				}

				$this->arResult['spaceName'] = $space->getName();

				$this->includeComponentTemplate('empty-state');

				return;
			}

			$componentTemplate = $this->arResult['page'];

			if ($this->arResult['pageType'] === 'user')
			{
				$this->checkUserAccess(
					$userId,
					$this->arResult['pageId']
				);

				if ($this->arResult['pageId'] === 'tasks')
				{
					$componentTemplate = $this->prepareUserTasksResult(
						$userId,
						$this->arResult['page']
					);
				}
			}
			else
			{
				$this->checkGroupAccess(
					$userId,
					$this->arResult['groupId'],
					$this->arResult['pageId']
				);

				if ($this->arResult['pageId'] === 'tasks')
				{
					$componentTemplate = $this->prepareGroupTasksResult(
						$this->arResult['groupId'],
						$this->arResult['page']
					);
				}

				if ($this->arResult['pageId'] === 'users')
				{
					$this->prepareGroupUsersResult();
				}
			}

			if ($this->arResult['pageId'] === 'discussions')
			{
				$this->prepareDiscussionsResult();
			}

			$this->includeComponentTemplate($componentTemplate);
		}
		catch (SystemException $exception)
		{
			$this->arResult['errorMessage'] = $exception->getMessage();
			$this->arResult['errorCode'] = $exception->getCode();

			$this->includeComponentTemplate('error');
		}
	}

	/**
	 * @throws SystemException
	 */
	private function includeModules(string $pageId): void
	{
		try
		{
			if (
				!Loader::includeModule('socialnetwork')
				|| ($pageId === 'tasks' && !Loader::includeModule('tasks'))
				|| ($pageId === 'calendar' && !Loader::includeModule('calendar'))
				|| ($pageId === 'files' && !Loader::includeModule('disk'))
			)
			{
				throw new SystemException('Cannot connect required modules');
			}
		}
		catch (LoaderException $exception)
		{
			throw new SystemException('Cannot connect required modules');
		}
	}

	/**
	 * @throws SystemException
	 */
	private function checkUserAccess(int $userId, string $pageId): void
	{
		if ($pageId === 'tasks')
		{
			if (!CSocNetFeatures::isActiveFeature('U', $userId, 'tasks'))
			{
				$errorMessage = Loc::getMessage('SN_SPACES_ERROR_USER_TASKS_UNAVAILABLE');

				throw new SystemException($errorMessage);
			}
		}
	}

	/**
	 * @throws SystemException
	 */
	private function checkGroupAccess(int $userId, int $groupId, string $pageId): void
	{
		if ($pageId === 'tasks')
		{
			if (!CSocNetFeatures::isActiveFeature('G', $groupId, 'tasks'))
			{
				throw new SystemException(Loc::getMessage('SN_SPACES_ERROR_GROUP_TASKS_UNAVAILABLE'));
			}
		}
	}

	private function getUserTasksState(int $userId): array
	{
		Ui\Filter\Task::setUserId($userId);

		return Ui\Filter\Task::listStateInit()->getState();
	}

	private function prepareUserTasksResult(int $userId, string $page): string
	{
		$state = $this->getUserTasksState($userId);

		$componentTemplate = $this->getTasksComponentTemplate($state, $page);
		if ($componentTemplate === $page . '_list')
		{
			Ui\Filter\Task::listStateInit()->setViewMode(\CTaskListState::VIEW_MODE_LIST);
		}

		$this->prepareTasksKanbanParams(
			$state['VIEW_SELECTED']['CODENAME'],
			$componentTemplate,
			$page
		);

		$this->arResult['state'] = $state;

		return $componentTemplate;
	}

	/**
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getStorageByPageType(string $pageType): ?Storage
	{
		$diskDriver = Driver::getInstance();

		if ($pageType === 'user')
		{
			return $diskDriver->getStorageByUserId($this->arResult['userId']);
		}

		return $diskDriver->getStorageByGroupId($this->arResult['groupId']);
	}

	/**
	 * @throws ArgumentTypeException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws SystemException
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	private function prepareDiscussionsResult(): void
	{
		$this->arResult['storage'] = null;
		$this->arResult['folder'] = null;
		$storage = $this->getStorageByPageType($this->arResult['pageType']);
		if (!is_null($storage))
		{
			$folder = Folder::loadById($storage->getRootObjectId());

			$this->arResult['storage'] = $storage;
			$this->arResult['folder'] = $folder;
		}
	}

	private function prepareGroupTasksResult(int $groupId, string $page): string
	{
		$state = $this->getGroupTasksState($groupId);

		$group = Item\Workgroup::getById($groupId);
		if ($group && $group->isScrumProject())
		{
			$componentTemplate = $page . '_scrum';
		}
		else
		{
			$componentTemplate = $this->getTasksComponentTemplate($state, $page);
		}

		$this->prepareTasksKanbanParams(
			$state['VIEW_SELECTED']['CODENAME'],
			$componentTemplate,
			$page
		);

		$this->arResult['state'] = $state;

		return $componentTemplate;
	}

	private function prepareGroupUsersResult(): void
	{
		$mode = (string) Context::getCurrent()->getRequest()->get('mode');

		$availableModes = ['members', 'requests_in', 'requests_out'];

		$mode = in_array($mode, $availableModes, true) ? $mode : 'members';

		$this->arResult['mode'] = mb_strtoupper($mode);
	}

	private function getGroupTasksState(int $groupId): array
	{
		Ui\Filter\Task::setGroupId($groupId);

		return Ui\Filter\Task::listStateInit()->getState();
	}

	private function getTasksComponentTemplate(array $state, string $page): string
	{
		switch ($state['VIEW_SELECTED']['CODENAME'])
		{
			case 'VIEW_MODE_GANTT':
				$componentTemplate = $page . '_gantt';
				break;
			case 'VIEW_MODE_PLAN':
			case 'VIEW_MODE_KANBAN':
			case 'VIEW_MODE_TIMELINE':
				$componentTemplate = $page . '_kanban';
				break;
			case 'VIEW_MODE_CALENDAR':
				$componentTemplate = $page . '_calendar';
				break;
			default:
				$componentTemplate = $page . '_list';
				break;
		}

		return $componentTemplate;
	}

	private function prepareTasksKanbanParams(
		string $codeName,
		string $componentTemplate,
		string $page
	): void
	{
		$this->arResult['isPersonalKanban'] = 'N';
		$this->arResult['isTimelineKanban'] = 'N';

		if ($componentTemplate === $page . '_kanban')
		{
			if ($codeName === 'VIEW_MODE_PLAN')
			{
				$this->arResult['isPersonalKanban'] = 'Y';
			}
			if ($codeName === 'VIEW_MODE_TIMELINE')
			{
				$this->arResult['isTimelineKanban'] = 'Y';
				$this->arResult['isPersonalKanban'] = 'Y';
			}
		}
	}
}
