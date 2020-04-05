<?php

namespace Bitrix\Im\Call;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Signaling
{
	protected  $call;

	public function __construct(Call $call)
	{
		$this->call = $call;
	}

	public function sendInvite($senderId, array $toUserIds, $isMobile, $video = false)
	{
		$users = $this->call->getUsers();
		$userData = \CIMContactList::getUserData(['ID' => $users, 'DEPARTMENT' => 'N', 'HR_PHOTO' => 'Y']);
		$config = array(
			'call' => $this->call->toArray((count($toUserIds) == 1 ? $toUserIds[0] : 0)),
			'users' => $users,
			'userData' => $userData,
			'senderId' => $senderId,
			'publicIds' => $this->getPublicIds($users),
			'isMobile' => $isMobile,
			'video' => $video
		);

		if(count($users) == 2 && $this->call->getProvider() === Call::PROVIDER_PLAIN)
		{
			$name = '';
			if($this->call->getAssociatedEntity())
			{
				$name = $this->call->getAssociatedEntity()->getName($toUserIds[0]);
			}

			if($name != '')
			{
				$pushText = Loc::getMessage('IM_CALL_INVITE', ['#USER_NAME#' => $name]);
			}
			else
			{
				$pushText = Loc::getMessage('IM_CALL_INVITE', ['#USER_NAME#' => Loc::getMessage('IM_CALL_INVITE_NA')]);
			}

			$push = [
				'message' => $pushText,
				'expiry' => 0,
				'params' => [
					'ACTION' => 'IMINV_' . $this->call->getId() . "_" . time() . "_" . ($video ? 'Y' : 'N'),
					'PARAMS' => [
						'call' => [
							'ID' => $this->call->getId(),
							'PROVIDER' => $this->call->getProvider()
						],
						'video' => $video,
						'users' => $users,
						'userData' => $userData,
						'senderId' => $senderId
					]
				],
				'advanced_params' => [
					'id' => 'IM_CALL_'.$this->call->getId(),
					'notificationsToCancel' => ['IM_CALL_'.$this->call->getId()],
					'androidHighPriority' => true,
					'useVibration' => true
				],
				'sound' => 'call.aif',
				'send_immediately' => 'Y'
			];
		}
		else
		{
			$push = null;
		}

		return self::send('Call::incoming', $toUserIds, $config, $push);
	}

	public function sendUsersInvited($senderId, array $toUserIds, array $users)
	{
		$config = array(
			'call' => $this->call->toArray(),
			'users' => $users,
			'userData' => \CIMContactList::GetUserData(Array('ID' => $users, 'DEPARTMENT' => 'N', 'HR_PHOTO' => 'Y')),
			'senderId' => $senderId,
			'publicIds' => $this->getPublicIds($users),
		);

		return self::send('Call::usersInvited', $toUserIds, $config);
	}

	public function sendAssociatedEntityReplaced($senderId)
	{
		$config = array(
			'call' => $this->call->toArray(),
			'senderId' => $senderId,
		);

		$toUserIds = $this->call->getUsers();

		return self::send('Call::associatedEntityReplaced', $toUserIds, $config);
	}

	public function sendAnswer($senderId, $callInstanceId)
	{
		$config = array(
			'call' => $this->call->toArray(),
			'senderId' => $senderId,
			'callInstanceId' => $callInstanceId
		);

		$toUserIds = $this->call->getUsers();

		return self::send('Call::answer', $toUserIds, $config, $this->getCancelingPush());
	}

	public function sendPing($senderId, $requestId)
	{
		$config = array(
			'requestId' => $requestId,
			'call' => $this->call->toArray(),
			'senderId' => $senderId
		);

		$toUserIds = $this->call->getUsers();
		$toUserIds = array_filter($toUserIds, function($value) use ($senderId) {
			return $value != $senderId;
		});
		return self::send('Call::ping', $toUserIds, $config);
	}

	public function sendNegotiationNeeded($senderId, $toUserId)
	{
		return self::send('Call::negotiationNeeded', $toUserId, array(
			'senderId' => $senderId
		));
	}

	public function sendConnectionOffer($senderId, $toUserId, $connectionId, $offerSdp, $userAgent)
	{
		return self::send('Call::connectionOffer', $toUserId, array(
			'senderId' => $senderId,
			'connectionId' => $connectionId,
			'sdp' => $offerSdp,
			'userAgent' => $userAgent
		));
	}

	public function sendConnectionAnswer($senderId, $toUserId, $connectionId, $answerSdp, $userAgent)
	{
		return self::send('Call::connectionAnswer', $toUserId, array(
			'senderId' => $senderId,
			'connectionId' => $connectionId,
			'sdp' => $answerSdp,
			'userAgent' => $userAgent
		));
	}

	public function sendIceCandidates($senderId, $toUserId, array $iceCandidates)
	{
		return self::send('Call::iceCandidate', $toUserId, array(
			'senderId' => $senderId,
			'candidates' => $iceCandidates
		));
	}

	public function sendHangup($senderId, array $toUserIds, $callInstanceId, $code = 200)
	{
		if(count($this->call->getUsers()) == 2 && $this->call->getProvider() === Call::PROVIDER_PLAIN)
		{
			$push = $this->getCancelingPush();
		}
		else
		{
			$push = null;
		}

		return self::send('Call::hangup', $toUserIds, array(
			'senderId' => $senderId,
			'callInstanceId' => $callInstanceId,
			'code' => $code,
			'push' => $push
		));
	}

	public function sendFinish()
	{
		if(count($this->call->getUsers()) == 2 && $this->call->getProvider() === Call::PROVIDER_PLAIN)
		{
			$push = $this->getCancelingPush();
		}
		else
		{
			$push = null;
		}

		return self::send('Call::finish', $this->call->getUsers(), [], $push);
	}

	protected function getPublicIds(array $userIds)
	{
		if(!Loader::includeModule('pull'))
		{
			return [];
		}

		return \Bitrix\Pull\Channel::getPublicIds([
			'USERS' => $userIds,
			'JSON' => true
		]);
	}

	protected function getCancelingPush()
	{
		return [
			'send_immediately' => 'Y',
			'advanced_params' => [
				"notificationsToCancel" => ['IM_CALL_'.$this->call->getId()],
			]
		];
	}

	protected function send($command, $users, array $params = [], $push = null, $ttl = 0)
	{
		if(!Loader::includeModule('pull'))
			return false;

		if(!isset($params['call']))
			$params['call'] = $this->call->toArray();

		if(!isset($params['callId']))
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