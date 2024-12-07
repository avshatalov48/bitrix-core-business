<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Im\Call\Call;
use Bitrix\Im\Call\Util;
use Bitrix\Main\Error;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\JWT;


class BitrixCall extends Call
{
	protected $provider = parent::PROVIDER_BITRIX;

	/**
	 * @return void
	 */
	protected function initCall(): void
	{
		if (!$this->endpoint)
		{
			$this->uuid = Util::generateUUID();
			$this->secretKey = Random::getString(10, true);

			if (!$this->getId())
			{
				$this->save();
			}

			$createResult = (new ControllerClient())->createCall($this);

			if (!$createResult->isSuccess())
			{
				parent::finish();

				$this->addErrors($createResult->getErrors());
			}
			$callData = $createResult->getData();
			if (!$callData['endpoint'])
			{
				parent::finish();

				$this->addError(new Error('Empty endpoint', 'empty_endpoint'));

				return;
			}

			$this->setEndpoint($callData['endpoint']);
			$this->save();
		}
	}

	public function finish(): void
	{
		if ($this->getState() != static::STATE_FINISHED)
		{
			(new ControllerClient())->finishCall($this);
		}
		parent::finish();
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

	public function inviteUsers(int $senderId, array $toUserIds, $isLegacyMobile, $video = false, $sendPush = true): void
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

	public function getMaxUsers(): int
	{
		return parent::getMaxCallServerParticipants();
	}
}