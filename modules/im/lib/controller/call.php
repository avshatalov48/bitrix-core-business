<?php

namespace Bitrix\Im\Controller;

use Bitrix\Im\Call\CallUser;
use Bitrix\Im\Call\Integration\EntityType;
use Bitrix\Im\Call\Registry;
use Bitrix\Im\Call\Util;
use Bitrix\Im\Common;
use Bitrix\Im\V2\Call\CallFactory;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Main\Application;
use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Im\Dialog;

class Call extends Engine\Controller
{
	protected const LOCK_TTL = 15; // in seconds

	public function createAction($type, $provider, $entityType, $entityId, $joinExisting = false)
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$lockName = static::getLockNameWithEntityId($entityType, $entityId, $currentUserId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->errorCollection[] = new Error("Could not get exclusive lock", "could_not_lock");
			return null;
		}

		$call = $joinExisting ? CallFactory::searchActive($type, $provider, $entityType, $entityId, $currentUserId) : false;

		try
		{
			if ($call)
			{
				if (!$call->getAssociatedEntity()->checkAccess($currentUserId))
				{
					$this->errorCollection[] = new Error("You can not access this call", 'access_denied');
					Application::getConnection()->unlock($lockName);
					return null;
				}

				$isNew = false;
				if (!$call->hasUser($currentUserId))
				{
					$addedUser = $call->addUser($currentUserId);

					if (!$addedUser)
					{
						$this->errorCollection[] = new Error("User limit reached", "user_limit_reached");
						Application::getConnection()->unlock($lockName);
						return null;
					}
				}
			}
			else
			{
				$isNew = true;

				try {
					$call = CallFactory::createWithEntity($type, $provider, $entityType, $entityId, $currentUserId);
				} catch (\Throwable $e) {
					$this->addError(new Error($e->getMessage(), $e->getCode()));
					Application::getConnection()->unlock($lockName);
					return null;
				}

				if (!$call->getAssociatedEntity()->canStartCall($currentUserId))
				{
					$this->errorCollection[] = new Error("You can not create this call", 'access_denied');
					Application::getConnection()->unlock($lockName);
					return null;
				}

				$initiator = $call->getUser($currentUserId);
				$initiator->update([
					'STATE' => CallUser::STATE_READY,
					'LAST_SEEN' => new DateTime(),
					'FIRST_JOINED' => new DateTime()
				]);
			}
		}
		catch(\Exception $e)
		{
			$this->errorCollection[] = new Error(
				"Can't initiate a call. Server error. (" . ($status ?? "") . ")",
				"call_init_error");

			Application::getConnection()->unlock($lockName);
			return null;
		}

		$users = $call->getUsers();
		$publicChannels = Loader::includeModule('pull')
			? \Bitrix\Pull\Channel::getPublicIds([
				'TYPE' => \CPullChannel::TYPE_PRIVATE,
				'USERS' => $users,
				'JSON' => true
			])
			: []
		;

		Application::getConnection()->unlock($lockName);

		if ($provider !== 'Plain')
		{
			$this->sendPrecallInviteMessage($entityId, $entityType, $call);
		}

		return [
			'call' => $call->toArray(),
			'connectionData' => $call->getConnectionData($currentUserId),
			'isNew' => $isNew,
			'users' => $users,
			'userData' => Util::getUsers($users),
			'publicChannels' => $publicChannels,
			'logToken' => $call->getLogToken($currentUserId),
		];
	}

	protected function sendPrecallInviteMessage($entityId, $entityType, $call): bool
	{
		$settings = \Bitrix\Main\Config\Configuration::getValue('im');
		$betaWebUrl  = $settings['call']['beta_web_url'] ?? '';

		if (empty($betaWebUrl))
		{
			return false;
		}

		$currentUserId = $this->getCurrentUser()->getId();

		$chatId = null;
		if($entityType === EntityType::CHAT && (Common::isChatId($entityId) || (int)$entityId > 0))
		{
			$chatId = Dialog::getChatId($entityId, $currentUserId);
		}

		if (is_null($chatId))
		{
			return false;
		}

		$chat = Chat::getInstance($chatId);

		$link = $betaWebUrl . '/?roomId=' . $call->getUuid();
		$text = Loc::getMessage("IM_BITRIX_CALL_INVITE_TEMP", [
			'#LINK#' => '[URL=' . $link . ']' . Loc::getMessage('IM_BITRIX_CALL_INVITE_BUTTON_TEMP') . '[/URL]',
		]);

		$message = new Message();
		$message->setMessage($text)->markAsImportant();
		$message->getParams()
			->fill([
				Params::COMPONENT_ID => 'CallInviteMessage',
				Params::COMPONENT_PARAMS => [
					'LINK' => $link
				]
			])
			->save()
		;
		$chat->sendMessage($message);

		return true;
	}

	public function createChildCallAction($parentId, $newProvider, $newUsers)
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$parentCall = Registry::getCallWithId($parentId);
		if (!$parentCall)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($parentCall, $currentUserId))
		{
			$this->errorCollection[] = new Error("You do not have access to the parent call", "access_denied");
			return null;
		}

		$childCall = $parentCall->makeClone($newProvider);

		$initiator = $childCall->getUser($currentUserId);
		$initiator->updateState(CallUser::STATE_READY);
		$initiator->updateLastSeen(new DateTime());

		foreach ($newUsers as $userId)
		{
			if(!$childCall->hasUser($userId))
			{
				$childCall->addUser($userId)->updateState(CallUser::STATE_CALLING);
			}
		}

		$users = $childCall->getUsers();
		return array(
			'call' => $childCall->toArray(),
			'connectionData' => $childCall->getConnectionData($currentUserId),
			'users' => $users,
			'userData' => Util::getUsers($users),
			'logToken' => $childCall->getLogToken($currentUserId)
		);
	}

	public function tryJoinCallAction($type, $provider, $entityType, $entityId)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = CallFactory::searchActive($type, $provider, $entityType, $entityId, $currentUserId);
		if(!$call)
		{
			return [
				'success' => false
			];
		}

		if(!$call->getAssociatedEntity()->checkAccess($currentUserId))
		{
			$this->errorCollection[] = new Error("You can not access this call", 'access_denied');
			return null;
		}

		if(!$call->hasUser($currentUserId))
		{
			$addedUser = $call->addUser($currentUserId);
			if(!$addedUser)
			{
				$this->errorCollection[] = new Error("User limit reached",  "user_limit_reached");
				return null;
			}
			$call->getSignaling()->sendUsersJoined($currentUserId, [$currentUserId]);
		}

		return [
			'success' => true,
			'call' => $call->toArray(),
			'connectionData' => $call->getConnectionData($currentUserId),
			'logToken' => $call->getLogToken($currentUserId)
		];
	}

	public function interruptAction($callId)
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}
		if(!$this->checkCallAccess($call, $currentUserId))
		{
			$this->errorCollection[] = new Error("You do not have access to the parent call", "access_denied");
			return null;
		}

		$call->finish();

		return array(
			'call' => $call->toArray($currentUserId),
			'connectionData' => $call->getConnectionData($currentUserId),
			'logToken' => $call->getLogToken($currentUserId)
		);
	}

	public function getAction($callId)
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}
		if(!$this->checkCallAccess($call, $currentUserId))
		{
			$this->errorCollection[] = new Error("You do not have access to the parent call", "access_denied");
			return null;
		}

		$users = $call->getUsers();
		return array(
			'call' => $call->toArray($currentUserId),
			'connectionData' => $call->getConnectionData($currentUserId),
			'users' => $users,
			'userData' => Util::getUsers($users),
			'logToken' => $call->getLogToken($currentUserId)
		);
	}

	public function inviteAction($callId, array $userIds, $video = "N", $legacyMobile = "N", $repeated = "N")
	{
		$isVideo = ($video === "Y");
		$isLegacyMobile = ($legacyMobile === "Y");
		$isRepeated = ($repeated === "Y");
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$call->getUser($currentUserId)->update([
			'LAST_SEEN' => new DateTime(),
			'IS_MOBILE' => ($isLegacyMobile ? 'Y' : 'N')
		]);

		$lockName = static::getLockNameWithCallId($callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		$this->inviteUsers($call, $userIds, $isLegacyMobile, $isVideo, $isRepeated);

		Application::getConnection()->unlock($lockName);

		return true;
	}

	public function inviteUsers(\Bitrix\Im\Call\Call $call, $userIds, $isLegacyMobile, $isVideo, $isRepeated)
	{
		$usersToInvite = [];
		foreach ($userIds as $userId)
		{
			$userId = (int)$userId;
			if (!$userId)
			{
				continue;
			}
			if(!$call->hasUser($userId))
			{
				if(!$call->addUser($userId))
				{
					continue;
				}
			}
			$usersToInvite[] = $userId;
			$callUser = $call->getUser($userId);
			if($callUser->getState() != CallUser::STATE_READY)
			{
				$callUser->updateState(CallUser::STATE_CALLING);
			}
		}

		if (count($usersToInvite) === 0)
		{
			$this->addError(new Error("No users to invite", "empty_users"));
			return null;
		}

		$sendPush = $isRepeated !== true;

		// send invite to the ones being invited.
		$call->inviteUsers(
			$this->getCurrentUser()->getId(),
			$usersToInvite,
			$isLegacyMobile,
			$isVideo,
			$sendPush
		);

		// send userInvited to everyone else.
		$allUsers = $call->getUsers();
		$otherUsers = array_diff($allUsers, $userIds);
		$call->getSignaling()->sendUsersInvited(
			$this->getCurrentUser()->getId(),
			$otherUsers,
			$usersToInvite
		);

		if($call->getState() === \Bitrix\Im\Call\Call::STATE_NEW)
		{
			$call->updateState(\Bitrix\Im\Call\Call::STATE_INVITING);
		}
	}

	public function cancelAction($callId)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;
	}

	public function answerAction($callId, $callInstanceId, $legacyMobile = "N")
	{
		$isLegacyMobile = $legacyMobile === "Y";
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);

		$lockName = static::getLockNameWithCallId($callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		if ($callUser)
		{
			$callUser->update([
				'STATE' => CallUser::STATE_READY,
				'LAST_SEEN' => new DateTime(),
				'FIRST_JOINED' => $callUser->getFirstJoined() ? $callUser->getFirstJoined() : new DateTime(),
				'IS_MOBILE' => $isLegacyMobile ? 'Y' : 'N',
			]);
		}

		Application::getConnection()->unlock($lockName);

		$call->getSignaling()->sendAnswer($currentUserId, $callInstanceId, $isLegacyMobile);
	}

	public function declineAction(int $callId, $callInstanceId, int $code = 603)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$lockName = static::getLockNameWithCallId($callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if(!$callUser)
		{
			$this->addError(new Error("User is not part of the call", "unknown_call_user"));
			Application::getConnection()->unlock($lockName);
			return null;
		}

		if ($callUser->getState() === CallUser::STATE_READY)
		{
			$this->addError(new Error("Can not decline in {$callUser->getState()} user state", "wrong_user_state"));
			Application::getConnection()->unlock($lockName);
			return null;
		}

		if($code === 486)
		{
			$callUser->updateState(CallUser::STATE_BUSY);
		}
		else
		{
			$callUser->updateState(CallUser::STATE_DECLINED);
		}
		$callUser->updateLastSeen(new DateTime());

		$userIds = $call->getUsers();
		$call->getSignaling()->sendHangup($currentUserId, $userIds, $callInstanceId, $code);

		if(!$call->hasActiveUsers())
		{
			$call->finish();
		}

		Application::getConnection()->unlock($lockName);
	}

	/**
	 * @param $callId
	 * @return bool
	 */
	public function pingAction($callId, $requestId, $retransmit = true)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
			if($callUser->getState() == CallUser::STATE_UNAVAILABLE)
			{
				$callUser->updateState(CallUser::STATE_IDLE);
			}
		}

		if($retransmit)
		{
			$call->getSignaling()->sendPing($currentUserId, $requestId);
		}

		return true;
	}

	public function onShareScreenAction($callId)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->update([
				'SHARED_SCREEN' => 'Y'
			]);
		}
	}

	public function onStartRecordAction($callId)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->update([
				'RECORDED' => 'Y'
			]);
		}
	}

	public function negotiationNeededAction($callId, $userId, $restart = false)
	{
		$restart = (bool)$restart;
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendNegotiationNeeded($currentUserId, $userId, $restart);
		return true;
	}

	public function connectionOfferAction($callId, $userId, $connectionId, $sdp, $userAgent)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendConnectionOffer($currentUserId, $userId, $connectionId, $sdp, $userAgent);
		return true;
	}

	public function connectionAnswerAction($callId, $userId, $connectionId, $sdp, $userAgent)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendConnectionAnswer($currentUserId, $userId, $connectionId, $sdp, $userAgent);
		return true;
	}

	public function iceCandidateAction($callId, $userId, $connectionId, array $candidates)
	{
		// mobile can alter key order, so we recover it
		ksort($candidates);

		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendIceCandidates($currentUserId, $userId, $connectionId, $candidates);
		return true;
	}

	public function hangupAction($callId, $callInstanceId, $retransmit = true)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
			return null;

		$lockName = static::getLockNameWithCallId($callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if($callUser)
		{
			$callUser->updateState(CallUser::STATE_IDLE);
			$callUser->updateLastSeen(new DateTime());
		}

		if(!$call->hasActiveUsers())
		{
			$call->finish();
		}

		Application::getConnection()->unlock($lockName);

		if($retransmit)
		{
			$userIds = $call->getUsers();
			$call->getSignaling()->sendHangup($currentUserId, $userIds, $callInstanceId);
		}
	}

	public function getUsersAction($callId, array $userIds = [])
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if(!$this->checkCallAccess($call, $currentUserId))
		{
			$this->errorCollection[] = new Error("You do not have access to the call", "access_denied");
			return null;
		}
		if (empty($userIds))
		{
			$allowedUserIds = $call->getUsers();
		}
		else
		{
			$allowedUserIds = array_filter($userIds, function($userId) use ($call, $currentUserId)
			{
				return $userId == $currentUserId || $call->hasUser($userId);
			});
		}

		if (empty($allowedUserIds))
		{
			$this->errorCollection[] = new Error("Users are not part of the call", "access_denied");
			return null;
		}

		return Util::getUsers($allowedUserIds);
	}

	public function getUserStateAction($callId, int $userId = 0)
	{
		$currentUserId = (int)$this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);

		if(!$call || !$this->checkCallAccess($call, $currentUserId))
		{
			$this->errorCollection[] = new Error("Call is not found or you do not have access to the call", "access_denied");
			return null;
		}

		if ($userId === 0)
		{
			$userId = $currentUserId;
		}

		$lockName = static::getLockNameWithCallId($callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		$callUser = $call->getUser($userId);
		if (!$callUser)
		{
			$this->addError(new Error("User is not part of the call", "unknown_call_user"));
			Application::getConnection()->unlock($lockName);
			return null;
		}

		Application::getConnection()->unlock($lockName);
		return $callUser->toArray();
	}

	public function getCallLimitsAction()
	{
		return [
			'callServerEnabled' => \Bitrix\Im\Call\Call::isCallServerEnabled(),
			'maxParticipants' => \Bitrix\Im\Call\Call::getMaxParticipants(),
		];
	}

	public function reportConnectionStatusAction(int $callId, bool $connectionStatus)
	{
		AddEventToStatFile('im', 'call_connection', $callId, ($connectionStatus ? 'Y' : 'N'));
	}

	protected function checkCallAccess(\Bitrix\Im\Call\Call $call, $userId)
	{
		if(!$call->checkAccess($userId))
		{
			$this->errorCollection[] = new Error("You don't have access to the call " . $call->getId() . "; (current user id: " . $userId . ")", 'access_denied');
			return false;
		}
		else
		{
			return true;
		}
	}

	public static function getLockNameWithEntityId(string $entityType, $entityId, $currentUserId): string
	{
		if($entityType === EntityType::CHAT && (Common::isChatId($entityId) || (int)$entityId > 0))
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($entityId, $currentUserId);

			return "call_entity_{$entityType}_{$chatId}";
		}

		return "call_entity_{$entityType}_{$entityId}";
	}

	protected static function getLockNameWithCallId($callId): string
	{
		//TODO: int|string after switching to php 8
		if (is_string($callId) || is_numeric($callId))
		{
			return "im_call_{$callId}";
		}

		return '';
	}

	public function configureActions()
	{
		return [
			'getUsers' => [
				'+prefilters' => [new Engine\ActionFilter\CloseSession()],
			],
			'reportConnectionStatus' => [
				'+prefilters' => [new Engine\ActionFilter\CloseSession()],
			],
		];
	}
}