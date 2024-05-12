<?php

namespace Bitrix\Im\V2\Controller\Call;

use Bitrix\Im\Call\Call;
use Bitrix\Im\V2\Call\CallError;
use Bitrix\Im\V2\Call\CallFactory;
use Bitrix\Main\Service\MicroService\BaseReceiver;

class CallController extends BaseReceiver
{
	/**
	 * @restMethod im.v2.Call.CallController.finishCall
	 */
	public function finishCallAction(string $callUuid): ?array
	{
		$call = CallFactory::searchActiveByUuid(Call::PROVIDER_BITRIX, $callUuid);

		if (!isset($call))
 		{
			$this->addError(new CallError(CallError::CALL_NOT_FOUND));

			return null;
		}

		$isSuccess = $call->getSignaling()->sendFinish();

		if (!$isSuccess)
		{
			$this->addError(new CallError(CallError::SEND_PULL_ERROR));

			return null;
		}

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Call.CallController.disconnectUser
	 */
	public function disconnectUserAction(string $callUuid, int $userId): ?array
	{
		$call = CallFactory::searchActiveByUuid(Call::PROVIDER_BITRIX, $callUuid);

		if (!isset($call))
		{
			$this->addError(new CallError(CallError::CALL_NOT_FOUND));

			return null;
		}

		$isSuccess = $call->getSignaling()->sendHangup($userId, $call->getUsers(), null);

		if (!$isSuccess)
		{
			$this->addError(new CallError(CallError::SEND_PULL_ERROR));

			return null;
		}

		return ['result' => true];
	}
}