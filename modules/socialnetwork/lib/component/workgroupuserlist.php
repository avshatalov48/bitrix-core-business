<?php

namespace Bitrix\Socialnetwork\Component;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Item\Workgroup\AccessManager;

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
}
