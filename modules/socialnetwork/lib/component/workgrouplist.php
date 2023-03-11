<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Grid;
use Bitrix\Socialnetwork\Component\WorkgroupList\EntityManager;
use Bitrix\Socialnetwork\Component\WorkgroupList\TasksCounter;
use Bitrix\Socialnetwork\Helper;

class WorkgroupList extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	public const AVAILABLE_ACTION_VIEW = 'view';
	public const AVAILABLE_ACTION_EDIT = 'edit';
	public const AVAILABLE_ACTION_DELETE = 'delete';
	public const AVAILABLE_ACTION_ADD_TO_ARCHIVE = 'add_to_archive';
	public const AVAILABLE_ACTION_REMOVE_FROM_ARCHIVE = 'remove_from_archive';
	public const AVAILABLE_ACTION_ADD_TO_FAVORITES = 'add_to_favorites';
	public const AVAILABLE_ACTION_REMOVE_FROM_FAVORITES = 'remove_from_favorites';

	public const AVAILABLE_ACTION_SET_CURRENT_USER_OWNER = 'set_current_user_owner';
	public const AVAILABLE_ACTION_SET_CURRENT_USER_SCRUM_MASTER = 'set_current_user_scrum_master';
	public const AVAILABLE_ACTION_LEAVE = 'leave';
	public const AVAILABLE_ACTION_JOIN = 'join';
	public const AVAILABLE_ACTION_DELETE_INCOMING_REQUEST = 'delete_incoming_request';

	public const AJAX_ACTION_SET_OWNER = 'setOwner';
	public const AJAX_ACTION_JOIN = 'join';
	public const AJAX_ACTION_SET_SCRUM_MASTER = 'setScrumMaster';
	public const AJAX_ACTION_DELETE_INCOMING_REQUEST = 'deleteIncomingRequest';
	public const AJAX_ACTION_REJECT_OUTGOING_REQUEST = 'rejectOutgoingRequest';
	public const AJAX_ACTION_ADD_TO_ARCHIVE = 'addToArchive';
	public const AJAX_ACTION_REMOVE_FROM_ARCHIVE = 'removeFromArchive';
	public const AJAX_ACTION_ADD_TO_FAVORITES = 'addToFavorites';
	public const AJAX_ACTION_REMOVE_FROM_FAVORITES = 'removeFromFavorites';

	public const GROUP_ACTION_ADD_TO_ARCHIVE = 'addToArchive';
	public const GROUP_ACTION_REMOVE_FROM_ARCHIVE = 'removeFromArchive';
	public const GROUP_ACTION_DELETE = 'delete';

	public const MODE_COMMON = '';
	public const MODE_USER = 'user_groups';
	public const MODE_TASKS_PROJECT = 'tasks_project';
	public const MODE_TASKS_SCRUM = 'tasks_scrum';

	protected \Bitrix\Main\Grid\Options $gridOptions;
	protected \Bitrix\Main\UI\Filter\Options $filterOptions;

	/** @var ErrorCollection errorCollection */
	protected $errorCollection = null;
	protected int $currentUserId = 0;
	protected WorkgroupList\RuntimeFieldsManager $runtimeFieldsManager;
	protected WorkgroupList\SelectFieldsManager $selectFieldsManager;
	protected array $counterData = [];

	public function configureActions()
	{
		return [
		];
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->runtimeFieldsManager = new WorkgroupList\RuntimeFieldsManager();
		$this->selectFieldsManager = new WorkgroupList\SelectFieldsManager();
		$this->errorCollection = new ErrorCollection();
		$this->currentUserId = Helper\User::getCurrentUserId();
	}

	/**
	 * Adds error to error collection.
	 * @param Error $error Error.
	 *
	 * @return $this
	 */
	protected function addError(Error $error)
	{
		$this->errorCollection[] = $error;

		return $this;
	}

	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	protected function printErrors(): void
	{
		foreach ($this->errorCollection as $error)
		{
			ShowError($error);
		}
	}

	public static function getActions(
		WorkgroupList\RuntimeFieldsManager $runtimeFieldsManager,
		array $params = []
	): array
	{
		$result = [
			self::AVAILABLE_ACTION_VIEW,
		];

		$groupFields = $params['GROUP'];
		$runtimeFields = $runtimeFieldsManager->get();
		$queryInitAlias = $params['QUERY_INIT_ALIAS'];

		$entityManager = new EntityManager([
			'queryInitAlias' => $params['QUERY_INIT_ALIAS'],
		]);

		if (in_array('CURRENT_RELATION', $runtimeFields, true))
		{
			$group = $entityManager->wakeUpWorkgroupEntityObject($groupFields);
			$currentUserRelation = $entityManager->wakeUpCurrentRelationEntityObject($groupFields, $queryInitAlias);
			$favorites = $entityManager->wakeUpFavoritesEntityObject($groupFields);

			$accessManager = new \Bitrix\Socialnetwork\Item\Workgroup\AccessManager(
				$group,
				$currentUserRelation,
				$currentUserRelation,
				[
					'currentUserFavorites' => $favorites,
				]
			);

			if ($accessManager->canEdit())
			{
				$result[] = self::AVAILABLE_ACTION_EDIT;
			}

			if ($accessManager->canSetOwner())
			{
				$result[] = self::AVAILABLE_ACTION_SET_CURRENT_USER_OWNER;
			}

			if ($accessManager->canSetScrumMaster())
			{
				$result[] = self::AVAILABLE_ACTION_SET_CURRENT_USER_SCRUM_MASTER;
			}

			if ($accessManager->canJoin())
			{
				$result[] = self::AVAILABLE_ACTION_JOIN;
			}

			if ($accessManager->canLeave())
			{
				$result[] = self::AVAILABLE_ACTION_LEAVE;
			}

			if ($accessManager->canDeleteIncomingRequest())
			{
				$result[] = self::AVAILABLE_ACTION_DELETE_INCOMING_REQUEST;
			}

			if ($accessManager->canAddToFavorites())
			{
				$result[] = self::AVAILABLE_ACTION_ADD_TO_FAVORITES;
			}

			if ($accessManager->canRemoveFromFavorites())
			{
				$result[] = self::AVAILABLE_ACTION_REMOVE_FROM_FAVORITES;
			}

			if ($accessManager->canAddToArchive())
			{
				$result[] = self::AVAILABLE_ACTION_ADD_TO_ARCHIVE;
			}

			if ($accessManager->canRemoveFromArchive())
			{
				$result[] = self::AVAILABLE_ACTION_REMOVE_FROM_ARCHIVE;
			}

			if ($accessManager->canDelete())
			{
				$result[] = self::AVAILABLE_ACTION_DELETE;
			}
		}

		return $result;
	}

	public static function getCellActions(
		WorkgroupList\RuntimeFieldsManager $runtimeFieldsManager,
		$params
	): array
	{

		$gridId = (string)($params['GRID_ID'] ?? '');
		$groupFields = ($params['GROUP'] ?? null);
		if (
			!$groupFields
			|| $gridId === ''
		)
		{
			return [];
		}

		$runtimeFields = $runtimeFieldsManager->get();

		$entityManager = new EntityManager([
			'queryInitAlias' => $params['QUERY_INIT_ALIAS'],
		]);

		$group = $entityManager->wakeUpWorkgroupEntityObject($groupFields);
		if (!$group)
		{
			return [];
		}

		$groupId = (int)$group->get('ID');

		$pin = [];
		if (in_array('PIN', $runtimeFields, true))
		{
			$pinEntity = $entityManager->wakeUpPinEntityObject($groupFields);
			$isPinned = ($pinEntity !== null);

			$pin = [
				'class' => [
					Grid\CellActions::PIN,
					($isPinned ? Grid\CellActionState::ACTIVE : Grid\CellActionState::SHOW_BY_HOVER),
				],
				'events' => [
					'click' => 'BX.Socialnetwork.UI.Grid.ActionController
						.changePin.bind(BX.Socialnetwork.UI.Grid.ActionController, ' . $groupId . ')',
				],
			];
		}

		return [
			'NAME' => [
				$pin,
			],
		];
	}

	protected function checkAjaxAction(string $action = ''): bool
	{
		return in_array($action, [
			self::AJAX_ACTION_JOIN,
			self::AJAX_ACTION_SET_OWNER,
			self::AJAX_ACTION_SET_SCRUM_MASTER,
			self::AJAX_ACTION_DELETE_INCOMING_REQUEST,
			self::AJAX_ACTION_REJECT_OUTGOING_REQUEST,
			self::AJAX_ACTION_ADD_TO_ARCHIVE,
			self::AJAX_ACTION_REMOVE_FROM_ARCHIVE,
		], true);
	}

	public static function getTasksModeList(): array
	{
		return [
			self::MODE_TASKS_PROJECT,
			self::MODE_TASKS_SCRUM,
		];
	}

	protected function getAccessToTasksCounters(): bool
	{
		return TasksCounter::getAccessToTasksCounters([
			'mode' => $this->arParams['MODE'],
			'contextUserId' => $this->arParams['USER_ID'],
		]);
	}

	protected function getTasksCounters(): array
	{
		return TasksCounter::getTasksCounters([
			'mode' => $this->arParams['MODE'],
		]);
	}

	protected function getTasksCountersScope(): string
	{
		return TasksCounter::getTasksCountersScope([
			'mode' => $this->arParams['MODE'],
		]);
	}

	protected function isFilterApplied(): bool
	{
		$currentPreset = $this->filterOptions->getCurrentFilterId();
		$isDefaultPreset = ($this->filterOptions->getDefaultFilterId() === $currentPreset);
		$additionalFields = $this->filterOptions->getAdditionalPresetFields($currentPreset);
		$isSearchStringEmpty = ($this->filterOptions->getSearchString() === '');

		return (!$isSearchStringEmpty || !$isDefaultPreset || !empty($additionalFields));
	}
}
