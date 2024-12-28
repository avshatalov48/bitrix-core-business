<?php

namespace Bitrix\Im\Controller;

use Bitrix\Im\Call\CallUser;
use Bitrix\Im\Call\Integration\EntityType;
use Bitrix\Im\Call\Registry;
use Bitrix\Im\Call\Util;
use Bitrix\Im\Common;
use Bitrix\Im\V2\Call\CallFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Call\Settings;
use Bitrix\Call\Integration\AI\CallAISettings;


class Call extends Engine\Controller
{
	protected const LOCK_TTL = 10; // in seconds

	/**
	 * @restMethod im.call.create
	 * @param int $type
	 * @param string $provider
	 * @param string $entityType
	 * @param string $entityId
	 * @param bool $joinExisting
	 * @return array|null
	 *
	 */
	public function createAction(int $type, string $provider, string $entityType, string $entityId, bool $joinExisting = false): ?array
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$call = null;

		$lockName = static::getLockNameWithEntityId($entityType, $entityId, $currentUserId);
		if (!Application::getConnection()->lock($lockName, 3))
		{
			if ($joinExisting)
			{
				$call = CallFactory::searchActive($type, $provider, $entityType, $entityId, $currentUserId);
			}

			if (!$call)
			{
				$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
				return null;
			}
		}

		if (!$call && $joinExisting)
		{
			$call = CallFactory::searchActive($type, $provider, $entityType, $entityId, $currentUserId);
		}

		$isNew = false;
		try
		{
			if ($call)
			{
				if ($call->hasErrors())
				{
					$this->addErrors($call->getErrors());
					Application::getConnection()->unlock($lockName);
					return null;
				}

				if (!$call->getAssociatedEntity()->checkAccess($currentUserId))
				{
					$this->addError(new Error("You can not access this call", 'access_denied'));
					Application::getConnection()->unlock($lockName);
					return null;
				}

				if (!$call->hasUser($currentUserId))
				{
					$addedUser = $call->addUser($currentUserId);

					if (!$addedUser)
					{
						$this->addError(new Error("User limit reached", "user_limit_reached"));
						Application::getConnection()->unlock($lockName);
						return null;
					}
				}
			}
			else
			{
				$isNew = true;

				try
				{
					$call = CallFactory::createWithEntity($type, $provider, $entityType, $entityId, $currentUserId);
				}
				catch (\Throwable $e)
				{
					$this->addError(new Error($e->getMessage(), $e->getCode()));
					Application::getConnection()->unlock($lockName);
					return null;
				}

				if ($call->hasErrors())
				{
					$this->addErrors($call->getErrors());
					Application::getConnection()->unlock($lockName);
					return null;
				}

				if (!$call->getAssociatedEntity()->canStartCall($currentUserId))
				{
					$this->addError(new Error("You can not create this call", 'access_denied'));
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
			$this->addError(new Error(
				"Can't initiate a call. Server error. (" . ($status ?? "") . ")",
				"call_init_error")
			);

			Application::getConnection()->unlock($lockName);
			return null;
		}

		Application::getConnection()->unlock($lockName);

		return $this->formatCallResponse($call, 0, $isNew);
	}

	/**
	 * @param \Bitrix\Im\Call\Call $call
	 * @param bool $isNew
	 * @return array{call: array, connectionData: array, users: array, userData: array, publicChannels: array, logToken: string, isNew: bool}
	 */
	protected function formatCallResponse(\Bitrix\Im\Call\Call $call, int $initiatorId = 0, bool $isNew = false): array
	{
		$currentUserId = $this->getCurrentUser()->getId();

		$users = $call->getUsers();
		$publicChannels = Loader::includeModule('pull')
			? \Bitrix\Pull\Channel::getPublicIds([
				'TYPE' => \CPullChannel::TYPE_PRIVATE,
				'USERS' => $users,
				'JSON' => true
			])
			: []
		;

		$response = [
			'call' => $call->toArray($initiatorId),
			'connectionData' => $call->getConnectionData($currentUserId),
			'users' => $users,
			'userData' => Util::getUsers($users),
			'publicChannels' => $publicChannels,
			'logToken' => $call->getLogToken($currentUserId),
		];
		if ($isNew)
		{
			$response['isNew'] = $isNew;
		}
		if (Settings::isAIServiceEnabled())
		{
			$response['ai'] = [
				'serviceEnabled' => Settings::isAIServiceEnabled(),
				'settingsEnabled' => CallAISettings::isEnableBySettings(),
				'recordingMinUsers' => CallAISettings::getRecordMinUsers(),
				'agreementAccepted' => CallAISettings::isAgreementAccepted(),
				'tariffAvailable' => CallAISettings::isTariffAvailable(),
				'baasAvailable' => CallAISettings::isBaasServiceHasPackage(),
			];
		}

		return $response;
	}

	/**
	 * @restMethod im.call.createChild
	 * @param int $parentId
	 * @param string $newProvider
	 * @param int[] $newUsers
	 * @return array|null
	 */
	public function createChildCallAction($parentId, $newProvider, $newUsers): ?array
	{
		$parentCall = Registry::getCallWithId($parentId);
		if (!$parentCall)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($parentCall, $currentUserId))
		{
			$this->addError(new Error("You do not have access to the parent call", "access_denied"));
			return null;
		}

		$childCall = $parentCall->makeClone($newProvider);
		if ($childCall->hasErrors())
		{
			$this->addErrors($childCall->getErrors());
			return null;
		}

		$initiator = $childCall->getUser($currentUserId);
		$initiator->updateState(CallUser::STATE_READY);
		$initiator->updateLastSeen(new DateTime());

		foreach ($newUsers as $userId)
		{
			if (!$childCall->hasUser($userId))
			{
				$childCall->addUser($userId)?->updateState(CallUser::STATE_CALLING);
			}
		}

		$users = $childCall->getUsers();

		return [
			'call' => $childCall->toArray(),
			'connectionData' => $childCall->getConnectionData($currentUserId),
			'users' => $users,
			'userData' => Util::getUsers($users),
			'logToken' => $childCall->getLogToken($currentUserId)
		];
	}

	/**
	 * @restMethod im.call.tryJoinCall
	 * @param int $type
	 * @param string $provider
	 * @param string $entityType
	 * @param string $entityId
	 * @return array|null
	 */
	public function tryJoinCallAction($type, $provider, $entityType, $entityId): ?array
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = CallFactory::searchActive($type, $provider, $entityType, $entityId, $currentUserId);
		if (!$call)
		{
			return ['success' => false];
		}

		if ($call->hasErrors())
		{
			$this->addErrors($call->getErrors());
			return null;
		}

		if (!$call->getAssociatedEntity()->checkAccess($currentUserId))
		{
			$this->addError(new Error("You can not access this call", 'access_denied'));
			return null;
		}

		if (!$call->hasUser($currentUserId))
		{
			$addedUser = $call->addUser($currentUserId);
			if (!$addedUser)
			{
				$this->addError(new Error("User limit reached",  "user_limit_reached"));
				return null;
			}
			$call->getSignaling()->sendUsersJoined($currentUserId, [$currentUserId]);
		}

		return array_merge(
			['success' => true],
			$this->formatCallResponse($call)
		);
	}

	/**
	 * @restMethod im.call.interrupt
	 * @param int $callId
	 * @return array|null
	 */
	public function interruptAction($callId): ?array
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			$this->addError(new Error("You do not have access to the parent call", "access_denied"));
			return null;
		}

		$call->setActionUserId($currentUserId)->finish();

		return [
			'call' => $call->toArray($currentUserId),
			'connectionData' => $call->getConnectionData($currentUserId),
			'logToken' => $call->getLogToken($currentUserId)
		];
	}

	/**
	 * @restMethod im.call.get
	 * @param int $callId
	 * @return array|null
	 */
	public function getAction($callId): ?array
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			$this->addError(new Error("You do not have access to the parent call", "access_denied"));
			return null;
		}

		return $this->formatCallResponse($call, $currentUserId);
	}

	/**
	 * @restMethod im.call.invite
	 * @param int $callId
	 * @param int[] $userIds
	 * @param string $video
	 * @param string $show
	 * @param string $legacyMobile
	 * @param string $repeated
	 * @return true|null
	 */
	public function inviteAction($callId, array $userIds, $video = "N", $show = "Y", $legacyMobile = "N", $repeated = "N"): ?bool
	{
		$isVideo = ($video === "Y");
		$isShow = ($show === "Y");
		$isLegacyMobile = ($legacyMobile === "Y");
		$isRepeated = ($repeated === "Y");
		$userIds = array_map('intVal', $userIds);

		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		if ($call->hasErrors())
		{
			$this->addErrors($call->getErrors());
			return null;
		}

		$call->getUser($currentUserId)->update([
			'LAST_SEEN' => new DateTime(),
			'IS_MOBILE' => ($isLegacyMobile ? 'Y' : 'N')
		]);

		$lockName = static::getLockNameWithCallId('invite', $callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		$this->inviteUsers($call, $userIds, $isLegacyMobile, $isVideo, $isShow, $isRepeated);

		Application::getConnection()->unlock($lockName);

		return true;
	}

	protected function inviteUsers(\Bitrix\Im\Call\Call $call, $userIds, $isLegacyMobile, $isVideo, $isShow, $isRepeated): void
	{
		$usersToInvite = [];
		$existingUsers = [];
		foreach ($userIds as $userId)
		{
			$userId = (int)$userId;
			if (!$userId)
			{
				continue;
			}
			if (!$call->hasUser($userId))
			{
				if (!$call->addUser($userId))
				{
					continue;
				}
			}
			else if ($isRepeated === false && $call->getAssociatedEntity())
			{
				$existingUsers[] = $userId;
			}
			$usersToInvite[] = $userId;
			$callUser = $call->getUser($userId);
			if($callUser->getState() != CallUser::STATE_READY)
			{
				$callUser->updateState(CallUser::STATE_CALLING);
			}
		}

		if (!empty($existingUsers))
		{
			$call->getAssociatedEntity()->onExistingUsersInvite($existingUsers);
		}

		if (count($usersToInvite) === 0)
		{
			$this->addError(new Error("No users to invite", "empty_users"));
			return;
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
			$usersToInvite,
			$isShow
		);

		if ($call->getState() === \Bitrix\Im\Call\Call::STATE_NEW)
		{
			$call->updateState(\Bitrix\Im\Call\Call::STATE_INVITING);
		}
	}

	/**
	 * @restMethod im.call.cancel
	 * @param int $callId
	 * @return void|null
	 */
	public function cancelAction($callId)
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}
	}

	/**
	 * @restMethod im.call.answer
	 * @param int $callId
	 * @param int $callInstanceId
	 * @param string $legacyMobile
	 * @return void|null
	 */
	public function answerAction($callId, $callInstanceId, $legacyMobile = "N")
	{
		$isLegacyMobile = $legacyMobile === "Y";
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$lockName = static::getLockNameWithCallId('user'.$currentUserId, $callId);
			if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
			{
				$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
				return null;
			}

			$callUser->update([
				'STATE' => CallUser::STATE_READY,
				'LAST_SEEN' => new DateTime(),
				'FIRST_JOINED' => $callUser->getFirstJoined() ?: new DateTime(),
				'IS_MOBILE' => $isLegacyMobile ? 'Y' : 'N',
			]);

			Application::getConnection()->unlock($lockName);
		}

		$call->getSignaling()->sendAnswer($currentUserId, $callInstanceId, $isLegacyMobile);
	}

	/**
	 * @restMethod im.call.decline
	 * @param int $callId
	 * @param int $callInstanceId
	 * @param int $code
	 * @return void|null
	 */
	public function declineAction(int $callId, $callInstanceId, int $code = 603)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if (!$callUser)
		{
			$this->addError(new Error("User is not part of the call", "unknown_call_user"));
			return null;
		}

		if ($callUser->getState() === CallUser::STATE_READY)
		{
			$this->addError(new Error("Can not decline in {$callUser->getState()} user state", "wrong_user_state"));
			return null;
		}

		$lockName = static::getLockNameWithCallId('user'.$currentUserId, $callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		if ($code === 486)
		{
			$callUser->updateState(CallUser::STATE_BUSY);
		}
		else
		{
			$callUser->updateState(CallUser::STATE_DECLINED);
		}
		$callUser->updateLastSeen(new DateTime());

		Application::getConnection()->unlock($lockName);

		$userIds = $call->getUsers();
		$call->getSignaling()->sendHangup($currentUserId, $userIds, $callInstanceId, $code);

		if (!$call->hasActiveUsers())
		{
			$call->setActionUserId($currentUserId)->finish();
		}
	}

	/**
	 * @restMethod im.call.ping
	 * @param int $callId
	 * @param int $requestId
	 * @param bool $retransmit
	 * @return bool
	 */
	public function pingAction($callId, $requestId, $retransmit = true)
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
			if ($callUser->getState() == CallUser::STATE_UNAVAILABLE)
			{
				$callUser->updateState(CallUser::STATE_IDLE);
			}
		}

		if (
			is_bool($retransmit) && $retransmit===true
			|| is_string($retransmit) && in_array($retransmit, ['true', 'Y', '1'], true)
		)
		{
			$call->getSignaling()->sendPing($currentUserId, $requestId);
		}

		return true;
	}

	/**
	 * @restMethod im.call.onShareScreen
	 * @param int $callId
	 * @return void|null
	 */
	public function onShareScreenAction($callId)
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->update([
				'SHARED_SCREEN' => 'Y'
			]);
		}
	}

	/**
	 * @restMethod im.call.onStartRecord
	 * @param int $callId
	 * @return void|null
	 */
	public function onStartRecordAction($callId)
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->update([
				'RECORDED' => 'Y'
			]);
		}
	}

	/**
	 * @restMethod im.call.negotiationNeeded
	 * @param int $callId
	 * @param int $userId
	 * @param bool $restart
	 * @return void|null
	 */
	public function negotiationNeededAction($callId, $userId, $restart = false)
	{
		$restart = (bool)$restart;
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendNegotiationNeeded($currentUserId, $userId, $restart);
	}

	/**
	 * @restMethod im.call.connectionOffer
	 * @param int $callId
	 * @param int $userId
	 * @param int $connectionId
	 * @param string $sdp
	 * @param string $userAgent
	 * @return void|null
	 */
	public function connectionOfferAction($callId, $userId, $connectionId, $sdp, $userAgent)
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendConnectionOffer($currentUserId, $userId, $connectionId, $sdp, $userAgent);
	}

	/**
	 * @restMethod im.call.connectionAnswer
	 * @param int $callId
	 * @param int $userId
	 * @param int $connectionId
	 * @param string $sdp
	 * @param string $userAgent
	 * @return void|null
	 */
	public function connectionAnswerAction($callId, $userId, $connectionId, $sdp, $userAgent)
	{
		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendConnectionAnswer($currentUserId, $userId, $connectionId, $sdp, $userAgent);
	}

	/**
	 * @restMethod im.call.iceCandidate
	 * @param int $callId
	 * @param int $userId
	 * @param int $connectionId
	 * @param array $candidates
	 * @return void|null
	 */
	public function iceCandidateAction($callId, $userId, $connectionId, array $candidates)
	{
		// mobile can alter key order, so we recover it
		ksort($candidates);

		$currentUserId = $this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);

		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->updateLastSeen(new DateTime());
		}

		$call->getSignaling()->sendIceCandidates($currentUserId, $userId, $connectionId, $candidates);
	}

	/**
	 * @restMethod im.call.hangup
	 * @param int $callId
	 * @param int $callInstanceId
	 * @param bool $retransmit
	 * @return void|null
	 */
	public function hangupAction($callId, $callInstanceId, $retransmit = true)
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			return null;
		}

		$lockName = static::getLockNameWithCallId('user'.$currentUserId, $callId);
		if (!Application::getConnection()->lock($lockName, static::LOCK_TTL))
		{
			$this->addError(new Error("Could not get exclusive lock", "could_not_lock"));
			return null;
		}

		$callUser = $call->getUser($currentUserId);
		if ($callUser)
		{
			$callUser->updateState(CallUser::STATE_IDLE);
			$callUser->updateLastSeen(new DateTime());
		}

		if (
			is_bool($retransmit) && $retransmit===true
			|| is_string($retransmit) && in_array($retransmit, ['true', 'Y', '1'], true)
		)
		{
			$userIds = $call->getUsers();
			$call->getSignaling()->sendHangup($currentUserId, $userIds, $callInstanceId);
		}

		Application::getConnection()->unlock($lockName);

		if (!$call->hasActiveUsers())
		{
			$call->setActionUserId($currentUserId)->finish();
		}
	}

	/**
	 * @restMethod im.call.finish
	 * @param int $callId
	 * @return void|null
	 */
	public function finishAction(int $callId): ?array
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}
		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			$this->addError(new Error("You do not have access to the parent call", "access_denied"));
			return null;
		}

		$call->setActionUserId($currentUserId)->finish();

		return [
			'call' => $call->toArray($currentUserId),
			'connectionData' => $call->getConnectionData($currentUserId),
			'logToken' => $call->getLogToken($currentUserId)
		];
	}

	/**
	 * @restMethod im.call.getUsers
	 * @param int $callId
	 * @param int[] $userIds
	 * @return null|array
	 */
	public function getUsersAction($callId, array $userIds = [])
	{
		$call = Registry::getCallWithId($callId);
		if (!$call)
		{
			$this->addError(new Error(Loc::getMessage("IM_REST_CALL_ERROR_CALL_NOT_FOUND"), "call_not_found"));
			return null;
		}

		$currentUserId = $this->getCurrentUser()->getId();
		if (!$this->checkCallAccess($call, $currentUserId))
		{
			$this->addError(new Error("You do not have access to the call", "access_denied"));
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
			$this->addError(new Error("Users are not part of the call", "access_denied"));
			return null;
		}

		return Util::getUsers($allowedUserIds);
	}

	/**
	 * @restMethod im.call.getUserState
	 * @param int $callId
	 * @param int $userId
	 * @return null|array
	 */
	public function getUserStateAction($callId, int $userId = 0)
	{
		$currentUserId = (int)$this->getCurrentUser()->getId();
		$call = Registry::getCallWithId($callId);

		if (!$call || !$this->checkCallAccess($call, $currentUserId))
		{
			$this->addError(new Error("Call is not found or you do not have access to the call", "access_denied"));
			return null;
		}

		if ($userId === 0)
		{
			$userId = $currentUserId;
		}

		$callUser = $call->getUser($userId);
		if (!$callUser)
		{
			$this->addError(new Error("User is not part of the call", "unknown_call_user"));
			return null;
		}

		return $callUser->toArray();
	}

	/**
	 * @restMethod im.call.getCallLimits
	 * @return array
	 */
	public function getCallLimitsAction(): array
	{
		return [
			'callServerEnabled' => \Bitrix\Im\Call\Call::isCallServerEnabled(),
			'maxParticipants' => \Bitrix\Im\Call\Call::getMaxParticipants(),
		];
	}

	/**
	 * @restMethod im.call.reportConnectionStatus
	 * @param int $callId
	 * @param bool $connectionStatus
	 * @return void
	 */
	public function reportConnectionStatusAction(int $callId, bool $connectionStatus): void
	{
		AddEventToStatFile('im', 'call_connection', $callId, ($connectionStatus ? 'Y' : 'N'));
	}

	protected function checkCallAccess(\Bitrix\Im\Call\Call $call, $userId)
	{
		if (!$call->checkAccess($userId))
		{
			$this->addError(new Error("You don't have access to the call " . $call->getId() . "; (current user id: " . $userId . ")", 'access_denied'));
			return false;
		}

		return true;
	}

	protected static function getLockNameWithEntityId(string $entityType, $entityId, $currentUserId): string
	{
		if ($entityType === EntityType::CHAT && (Common::isChatId($entityId) || (int)$entityId > 0))
		{
			$chatId = \Bitrix\Im\Dialog::getChatId($entityId, $currentUserId);

			return "call_entity_{$entityType}_{$chatId}";
		}

		return "call_entity_{$entityType}_{$entityId}";
	}

	protected static function getLockNameWithCallId(string $prefix, $callId): string
	{
		//TODO: int|string after switching to php 8
		if (is_string($callId) || is_numeric($callId))
		{
			return "{$prefix}_call_{$callId}";
		}

		return '';
	}

	public function configureActions(): array
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