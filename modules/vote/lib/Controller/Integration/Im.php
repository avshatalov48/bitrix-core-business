<?php

namespace Bitrix\Vote\Controller\Integration;


use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Vote\Integration\Im\ImVote;
use Bitrix\Vote\Integration\Im\Result\ImVoteSendResult;

class Im extends Controller
{
	public function sendAction(
		int $chatId,
		array $IM_MESSAGE_VOTE_DATA,
		?string $templateId = null,
	): ?array
	{
		$currentUserId = (int)CurrentUser::get()->getId();
		if ($currentUserId <= 0)
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$result = ImVote::sendVote($chatId, $currentUserId, $IM_MESSAGE_VOTE_DATA, $templateId);
		if ($result instanceof ImVoteSendResult)
		{
			return [
				'messageId' => $result->messageId,
				'voteId' => $result->voteId,
			];
		}

		$this->addErrors($result->getErrors());

		return null;
	}
}