<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Call\Integration\Chat;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Signaling
{
	protected $call;

	public function __construct(Call $call)
	{
		$this->call = $call;
	}

	public function sendInvite(int $senderId, array $toUserIds, $isLegacyMobile, $video = false, $sendPush = true)
	{
		$users = $this->call->getUsers();

		$parentCall = $this->call->getParentId() ? Call::loadWithId($this->call->getParentId()) : null;
		$skipPush = $parentCall ?  $parentCall->getUsers() : [];
		$skipPush = array_flip($skipPush);

		$associatedEntity = $this->call->getAssociatedEntity();
		$isBroadcast = ($associatedEntity instanceof Chat) && $associatedEntity->isBroadcast();

		foreach ($toUserIds as $toUserId)
		{
			$config = [
				'call' => $this->call->toArray((count($toUserIds) == 1 ? $toUserId : 0)),
				'users' => $users,
				'invitedUsers' => $toUserIds,
				'userData' => Util::getUsers($users),
				'senderId' => $senderId,
				'publicIds' => $this->getPublicIds($users),
				'isLegacyMobile' => $isLegacyMobile,
				'video' => $video,
				'logToken' => $this->call->getLogToken($toUserId),
			];
			if (!isset($skipPush[$toUserId]) && $sendPush && !$isBroadcast)
			{
				$push = $this->getInvitePush($senderId, $toUserId, $isLegacyMobile, $video);
			}

			$this->send('Call::incoming', $toUserId, $config, $push);
		}
	}

	protected function getInvitePush(int $senderId, int $toUserId, $isLegacyMobile, $video)
	{
		$users = $this->call->getUsers();
		$associatedEntity = $this->call->getAssociatedEntity();
		$name = $associatedEntity ? $associatedEntity->getName($toUserId) : Loc::getMessage('IM_CALL_INVITE_NA');

		$email = null;
		$phone = null;
		if ($associatedEntity instanceof Chat)
		{
			if ($associatedEntity->isPrivateChat())
			{
				$userInstance = \Bitrix\Im\User::getInstance($senderId);
				$email = $userInstance->getEmail();
				$phone = $userInstance->getPhone();
				$phone = preg_replace("/[^0-9#*+,;]/", "", $phone);
			}
			$avatar = $associatedEntity->getAvatar($toUserId);
		}

		$pushText = Loc::getMessage('IM_CALL_INVITE', ['#USER_NAME#' => $name]);
		$pushTag = 'IM_CALL_'.$this->call->getId();
		$push = [
			'message' => $pushText,
			'expiry' => 0,
			'params' => [
				'ACTION' => 'IMINV_'.$this->call->getId()."_".time()."_".($video ? 'Y' : 'N'),
				'PARAMS' => [
					'type' => 'internal',
					'callerName' => htmlspecialcharsback($name),
					'callerAvatar' => $avatar ?? '',
					'call' => $this->call->toArray($toUserId),
					'video' => $video,
					'users' => $users,
					'isLegacyMobile' => $isLegacyMobile,
					'senderId' => $senderId,
					'senderEmail' => $email,
					'senderPhone' => $phone,
					'logToken' => $this->call->getLogToken($toUserId),
					'ts' => time(),
				]
			],
			'advanced_params' => [
				'id' => $pushTag,
				'notificationsToCancel' => [$pushTag],
				'androidHighPriority' => true,
				'useVibration' => true,
				'isVoip' => true,
				'callkit' => true,
			],
			'sound' => 'call.aif',
			'send_immediately' => 'Y'
		];

		return $push;
	}

	public function sendUsersJoined(int $senderId, array $joinedUsers)
	{
		$config = array(
			'call' => $this->call->toArray(),
			'users' => $joinedUsers,
			'userData' => Util::getUsers($joinedUsers),
			'senderId' => $senderId,
			'publicIds' => $this->getPublicIds($joinedUsers),
		);

		return $this->send('Call::usersJoined', $this->call->getUsers(), $config);

	}

	public function sendUsersInvited(int $senderId, array $toUserIds, array $users)
	{
		$config = array(
			'call' => $this->call->toArray(),
			'users' => $users,
			'userData' => Util::getUsers($users),
			'senderId' => $senderId,
			'publicIds' => $this->getPublicIds($users),
		);

		return $this->send('Call::usersInvited', $toUserIds, $config);
	}

	public function sendAssociatedEntityReplaced(int $senderId)
	{
		$config = array(
			'call' => $this->call->toArray(),
			'senderId' => $senderId,
		);

		$toUserIds = $this->call->getUsers();

		return $this->send('Call::associatedEntityReplaced', $toUserIds, $config);
	}

	public function sendAnswer(int $senderId, $callInstanceId, $isLegacyMobile)
	{
		$config = array(
			'call' => $this->call->toArray(),
			'senderId' => $senderId,
			'callInstanceId' => $callInstanceId,
			'isLegacyMobile' => $isLegacyMobile,
		);

		$toUserIds = array_diff($this->call->getUsers(), [$senderId]);
		$this->send('Call::answer', $toUserIds, $config, null, 3600);

		$push = [
			'send_immediately' => 'Y',
			'expiry' => 0,
			'params' => [],
			'advanced_params' => [
				'id' => 'IM_CALL_'.$this->call->getId().'_ANSWER',
				'notificationsToCancel' => ['IM_CALL_'.$this->call->getId()],
				'isVoip' => true,
				'callkit' => true,
				'filterCallback' => [static::class, 'filterPushesForApple'],
			]
		];

		$this->send('Call::answer', $senderId, $config, $push, 3600);
	}

	public function sendPing(int $senderId, $requestId)
	{
		$config = array(
			'requestId' => $requestId,
			'callId' => $this->call->getId(),
			'senderId' => $senderId
		);

		$toUserIds = $this->call->getUsers();
		$toUserIds = array_filter($toUserIds, function ($value) use ($senderId) {
			return $value != $senderId;
		});
		return $this->send('Call::ping', $toUserIds, $config, null, 0);
	}

	public function sendNegotiationNeeded(int $senderId, int $toUserId, $restart)
	{
		return $this->send('Call::negotiationNeeded', $toUserId, array(
			'senderId' => $senderId,
			'restart' => $restart
		));
	}

	public function sendConnectionOffer(int $senderId, int $toUserId, string $connectionId, string $offerSdp, string $userAgent)
	{
		return $this->send('Call::connectionOffer', $toUserId, array(
			'senderId' => $senderId,
			'connectionId' => $connectionId,
			'sdp' => $offerSdp,
			'userAgent' => $userAgent
		));
	}

	public function sendConnectionAnswer(int $senderId, int $toUserId, string $connectionId, string $answerSdp, string $userAgent)
	{
		return $this->send('Call::connectionAnswer', $toUserId, array(
			'senderId' => $senderId,
			'connectionId' => $connectionId,
			'sdp' => $answerSdp,
			'userAgent' => $userAgent
		));
	}

	public function sendIceCandidates(int $senderId, int $toUserId, string $connectionId, array $iceCandidates)
	{
		return $this->send('Call::iceCandidate', $toUserId, array(
			'senderId' => $senderId,
			'connectionId' => $connectionId,
			'candidates' => $iceCandidates
		));
	}

	public function sendHangup(int $senderId, array $toUserIds, string $callInstanceId, $code = 200)
	{
		$config = [
			'senderId' => $senderId,
			'callInstanceId' => $callInstanceId,
			'code' => $code,
		];

		$push = [
			'send_immediately' => 'Y',
			//'expiry' => 0,
			'params' => [],
			'advanced_params' => [
				'id' => 'IM_CALL_'.$this->call->getId().'_FINISH',
				'notificationsToCancel' => ['IM_CALL_'.$this->call->getId()],
				'callkit' => true,
				'filterCallback' => [static::class, 'filterPushesForApple'],
			]
		];

		return $this->send('Call::hangup', $toUserIds, $config, $push, 3600);
	}

	public function sendFinish()
	{
		$push = [
			'send_immediately' => 'Y',
			//'expiry' => 0,
			'params' => [],
			'advanced_params' => [
				'id' => 'IM_CALL_'.$this->call->getId().'_FINISH',
				'notificationsToCancel' => ['IM_CALL_'.$this->call->getId()],
				'callkit' => true,
				'filterCallback' => [static::class, 'filterPushesForApple'],
			]
		];

		return $this->send('Call::finish', $this->call->getUsers(), [], $push, 3600);
	}

	public static function filterPushesForApple($message, $deviceType, $deviceToken)
	{
		if (!Loader::includeModule('pull'))
		{
			return false;
		}
		$result = !in_array(
			$deviceType,
			[
				\CPushDescription::TYPE_APPLE,
				\CPushDescription::TYPE_APPLE_VOIP,
			],
			true)
		;
		return $result;
	}

	protected function getPublicIds(array $userIds)
	{
		if (!Loader::includeModule('pull'))
		{
			return [];
		}

		return \Bitrix\Pull\Channel::getPublicIds([
			'USERS' => $userIds,
			'JSON' => true
		]);
	}

	protected function send(string $command, $users, array $params = [], $push = null, $ttl = 5)
	{
		if (!Loader::includeModule('pull'))
			return false;

		if (!isset($params['call']))
			$params['call'] = ['ID' => $this->call->getId()];

		if (!isset($params['callId']))
			$params['callId'] = $this->call->getId();

		\Bitrix\Pull\Event::add($users, array(
			'module_id' => 'im',
			'command' => $command,
			'params' => $params,
			'push' => $push,
			'expiry' => $ttl
		));

		return true;
	}
}