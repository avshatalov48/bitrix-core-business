<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Im\Call\Call;
use Bitrix\Im\Call\Util;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\JWT;

class BitrixCall extends Call
{
	protected function initCall()
	{
		if (!$this->endpoint)
		{
			$this->uuid = Util::generateUUID();
			$this->secretKey = Random::getString(10, true);

			$callControllerClient = new ControllerClient();
			$createResult = $callControllerClient->createCall(
				$this->getUuid(),
				$this->getSecretKey(),
				$this->getInitiatorId(),
				$this->getId()
			);

			if (!$createResult->isSuccess())
			{
				$this->finish();

				throw new \Exception($createResult->getErrorMessages()[0]);
			}
			$callData = $createResult->getData();
			if (!$callData['endpoint'])
			{
				$this->finish();

				throw new \Exception('Empty endpoint');
			}

			$this->setEndpoint($callData['endpoint']);
			$this->save();
		}
	}

	protected function generateJwt(int $userId): string
	{
		return JWT::encode(
			[
				'uuid' => $this->getUuid(),
				'userId' => (string)$userId,
			],
			$this->getSecretKey()
		);
	}

	public function getConnectionData(int $userId): array
	{
		return [
			'endpoint' => $this->endpoint ?: null,
			'jwt' => $this->generateJwt($userId),
		];
	}

	public function inviteUsers(int $senderId, array $toUserIds, $isLegacyMobile, $video = false, $sendPush = true)
	{
		foreach ($toUserIds as $toUserId)
		{
			$this->getSignaling()->sendInviteToUser(
				$senderId,
				$toUserId,
				$toUserIds,
				$isLegacyMobile,
				$video,
				$sendPush
			);
		}
	}

	public function getMaxUsers()
	{
		return static::getMaxCallServerParticipants();
	}
}