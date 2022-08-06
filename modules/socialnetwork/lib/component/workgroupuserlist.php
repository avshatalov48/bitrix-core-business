<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Helper;

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

		$relation = $params['RELATION'];
		$groupId = (int)$params['GROUP_ID'];

		if (Helper\Workgroup::canSetOwner([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = self::AVAILABLE_ACTION_SET_OWNER;
		}

		if (Helper\Workgroup::canSetScrumMaster([
			'userId' => $relation->getUser()->getId(),
			'groupId' => $groupId,
		]))
		{
			$result[] = self::AVAILABLE_ACTION_SET_SCRUM_MASTER;
		}

		if (Helper\Workgroup::canSetModerator([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = self::AVAILABLE_ACTION_SET_MODERATOR;
		}
		elseif (Helper\Workgroup::canRemoveModerator([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = self::AVAILABLE_ACTION_REMOVE_MODERATOR;
		}

		$canDeleteOutgoingRequest = Helper\Workgroup::canDeleteOutgoingRequest([
			'relation' => $relation,
			'groupId' => $groupId,
		]);

		if ($canDeleteOutgoingRequest)
		{
			$result[] = self::AVAILABLE_ACTION_DELETE_OUTGOING_REQUEST;
		}
		elseif (Helper\Workgroup::canExclude([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = self::AVAILABLE_ACTION_EXCLUDE;
		}

		if (Helper\Workgroup::canProcessIncomingRequest([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
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

		if (Helper\Workgroup::canDeleteIncomingRequest([
			'relation' => $relation,
			'groupId' => $groupId,
		]))
		{
			$result[] = self::AVAILABLE_ACTION_DELETE_INCOMING_REQUEST;
		}

		return $result;
	}
}
