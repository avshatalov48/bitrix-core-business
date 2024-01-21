<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Filter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Internals\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Item\Workgroup\AccessManager;
use Bitrix\Socialnetwork\UserToGroupTable;

class WorkgroupUserList extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
	public const AVAILABLE_ACTION_VIEW_PROFILE = 'view_profile';
	public const AVAILABLE_ACTION_SET_OWNER = 'set_owner';
	public const AVAILABLE_ACTION_SET_SCRUM_MASTER = 'set_scrum_master';
	public const AVAILABLE_ACTION_EXCLUDE = 'exclude';
	public const AVAILABLE_ACTION_SET_MODERATOR = 'set_moderator';
	public const AVAILABLE_ACTION_REMOVE_MODERATOR = 'remove_moderator';
	public const AVAILABLE_ACTION_PROCESS_INCOMING_REQUEST = 'process_incoming_request';
	public const AVAILABLE_ACTION_DELETE_OUTGOING_REQUEST = 'delete_outgoing_request';
	public const AVAILABLE_ACTION_DELETE_INCOMING_REQUEST = 'delete_incoming_request';
	public const AVAILABLE_ACTION_REINVITE = 'reinvite';

	public const AJAX_ACTION_SET_OWNER = 'setOwner';
	public const AJAX_ACTION_SET_SCRUM_MASTER = 'setScrumMaster';
	public const AJAX_ACTION_SET_MODERATOR = 'setModerator';
	public const AJAX_ACTION_REMOVE_MODERATOR = 'removeModerator';
	public const AJAX_ACTION_EXCLUDE = 'exclude';
	public const AJAX_ACTION_DELETE_OUTGOING_REQUEST = 'deleteOutgoingRequest';
	public const AJAX_ACTION_DELETE_INCOMING_REQUEST = 'deleteIncomingRequest';
	public const AJAX_ACTION_ACCEPT_INCOMING_REQUEST = 'acceptIncomingRequest';
	public const AJAX_ACTION_REJECT_INCOMING_REQUEST = 'rejectIncomingRequest';
	public const AJAX_ACTION_REINVITE = 'reinvite';

	protected const PRESET_ALL = 'all';
	protected const PRESET_COMPANY = 'company';
	protected const PRESET_REQUESTS_IN = 'requests_in';
	protected const PRESET_REQUESTS_OUT = 'requests_out';
	protected const PRESET_EXTERNAL = 'external';
	protected const PRESET_AUTO = 'auto';

	protected static $gridId = 'SOCIALNETWORK_WORKGROUP_USER_LIST';
	protected static $filterId = 'SOCIALNETWORK_WORKGROUP_USER_LIST';

	/** @var ErrorCollection errorCollection */
	protected $errorCollection = null;

	public function configureActions()
	{
		return [
		];
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
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

	public static function getActions(array $params = []): array
	{
		$result = [
			self::AVAILABLE_ACTION_VIEW_PROFILE,
		];

		$groupId = (int)$params['GROUP_ID'];
		$group = $params['GROUP'];
		$currentUserRelation = $params['CURRENT_RELATION'];
		$relation = $params['RELATION'];

		$accessManager = new AccessManager(
			$group,
			$relation,
			$currentUserRelation,
		);

		if ($accessManager->canSetOwner())
		{
			$result[] = self::AVAILABLE_ACTION_SET_OWNER;
		}

		if ($accessManager->canSetScrumMaster())
		{
			$result[] = self::AVAILABLE_ACTION_SET_SCRUM_MASTER;
		}

		if ($accessManager->canSetModerator())
		{
			$result[] = self::AVAILABLE_ACTION_SET_MODERATOR;
		}
		elseif ($accessManager->canRemoveModerator())
		{
			$result[] = self::AVAILABLE_ACTION_REMOVE_MODERATOR;
		}

		$canDeleteOutgoingRequest = $accessManager->canDeleteOutgoingRequest();
		if ($accessManager->canDeleteOutgoingRequest())
		{
			$result[] = self::AVAILABLE_ACTION_DELETE_OUTGOING_REQUEST;
		}
		elseif ($accessManager->canExclude())
		{
			$result[] = self::AVAILABLE_ACTION_EXCLUDE;
		}

		if ($accessManager->canProcessIncomingRequest())
		{
			$result[] = self::AVAILABLE_ACTION_PROCESS_INCOMING_REQUEST;
		}

		if (
			$canDeleteOutgoingRequest
			&& ModuleManager::isModuleInstalled('intranet')
			&& !empty($relation->getUser()->getConfirmCode())
		)
		{
			$result[] = self::AVAILABLE_ACTION_REINVITE;
		}

		if ($accessManager->canDeleteIncomingRequest())
		{
			$result[] = self::AVAILABLE_ACTION_DELETE_INCOMING_REQUEST;
		}

		return $result;
	}

	public static function prepareFilterResult(
		array $result,
		array $groupPerms,
		string $mode = ''
	): array
	{
		$entityFilter = Filter\Factory::createEntityFilter(
			UserToGroupTable::getUfId(),
			[
				'ID' => self::$filterId,
			]
		);
		$result['FILTER'] = $entityFilter->getFieldArrays();

		$result['GRID_ID'] = self::$gridId;
		$result['FILTER_ID'] = self::$filterId;

		$result['CURRENT_PRESET_ID'] = self::getCurrentPresetId($mode);
		$result['CURRENT_COUNTER'] = self::getCounter($mode);
		$result['CUSTOM_FILTER'] = self::getCurrentCustomFilter($mode);
		$result['FILTER_PRESETS'] = self::getFilterPresets($result['CURRENT_PRESET_ID'], $groupPerms);

		return $result;
	}

	private static function getCurrentPresetId(string $mode): string
	{
		switch ($mode)
		{
			case 'MEMBERS':
				$result = self::PRESET_COMPANY;
				break;
			case 'REQUESTS_IN':
				$result = self::PRESET_REQUESTS_IN;
				break;
			case 'REQUESTS_OUT':
				$result = self::PRESET_REQUESTS_OUT;
				break;
			default:
				$result = 'tmp_filter';
		}

		return $result;
	}

	private static function getCounter(string $mode): string
	{
		switch ($mode)
		{
			case 'REQUESTS_IN':
				$result = CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN;
				break;
			case 'REQUESTS_OUT':
				$result = CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT;
				break;
			default:
				$result = '';
		}

		return $result;
	}

	private static function getCurrentCustomFilter(string $mode): array
	{
		switch ($mode)
		{
			case 'MODERATORS':
				$result = [
					'ROLE' => [ UserToGroupTable::ROLE_MODERATOR ],
				];
				break;
			default:
				$result = [];
		}

		return $result;
	}

	private static function getFilterPresets(string $currentPresetId, array $groupPerms): array
	{
		$result = [];

		$extranetAvailable = \Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability();

		$result[self::PRESET_ALL] = [
			'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_ALL'),
			'fields' => [],
			'default' => ($currentPresetId === self::PRESET_ALL),
		];

		$companyFilter = [
			'FIRED' => 'N',
			'ROLE' => UserToGroupTable::getRolesMember(),
		];
		if ($extranetAvailable)
		{
			$companyFilter['EXTRANET'] = 'N';
		}

		$result[self::PRESET_COMPANY] = [
			'name' => (
			$extranetAvailable
				? Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_COMPANY')
				: Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_MEMBERS')
			),
			'fields' => $companyFilter,
			'default' => ($currentPresetId === self::PRESET_COMPANY),
		];

		if ($extranetAvailable)
		{
			$result[self::PRESET_EXTERNAL] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_EXTERNAL'),
				'fields' => [
					'EXTRANET' => 'Y',
					'FIRED' => 'N',
					'ROLE' => UserToGroupTable::getRolesMember(),
				],
				'default' => false,
			];
		}

		if ($groupPerms['UserCanProcessRequestsIn'] ?? null)
		{
			$result[self::PRESET_REQUESTS_IN] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_REQUESTS_IN'),
				'fields' => [
					'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
					'FIRED' => 'N',
					'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_USER,
				],
				'default' => ($currentPresetId === self::PRESET_REQUESTS_IN),
			];
		}

		if ($groupPerms['UserCanInitiate'])
		{
			$result[self::PRESET_REQUESTS_OUT] = [
				'name' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_FILTER_PRESET_REQUESTS_OUT'),
				'fields' => [
					'ROLE' => [ UserToGroupTable::ROLE_REQUEST ],
					'FIRED' => 'N',
					'INITIATED_BY_TYPE' => UserToGroupTable::INITIATED_BY_GROUP,
				],
				'default' => ($currentPresetId === self::PRESET_REQUESTS_OUT),
			];
		}

		return $result;
	}
}
