<?php

namespace Bitrix\Vote\Service;

use Bitrix\Main\Engine\UrlManager;
use Bitrix\Vote\Attach;
use Bitrix\Vote\Vote\Option;

class AttachedVoteFrontendFormatService
{
	private AttachedVoteResultUrlService $urlService;

	public function __construct()
	{
		$this->urlService = new AttachedVoteResultUrlService();
	}

	public function format(Attach $attach, int $userId): array
	{
		$signedAttachId = (new AttachedVoteSigner())->sign($attach->getAttachId());

		return [
			'ID' => (int)$attach['ID'],
			'VOTE_ID' => (int)$attach['VOTE_ID'],
			'COUNTER' => (int)$attach['COUNTER'],
			'QUESTIONS' => $attach['QUESTIONS'],
			'ANONYMITY' => (int)$attach['ANONYMITY'],
			'OPTIONS' => (int)$attach['OPTIONS'],
			'userAnswerMap' => $attach->getUserEventsAnswersStatByUserId($userId),
			'canEdit' => $attach->canEdit($userId),
			'canVote' => $attach->canParticipate($userId) && $attach->canVote($userId)->isSuccess(),
			'canRevote' => $attach->canParticipate($userId)
				&& $attach->canRevote($userId)->isSuccess()
				&& $attach['OPTIONS'] & Option::ALLOW_REVOTE
			,
			'isVoted' => (bool)$attach->isVotedFor($userId),
			'signedAttachId' => $signedAttachId,
			'resultUrl' => $this->urlService->getResultUrl($signedAttachId, $attach['UID'] ?? null),
			'downloadUrl' => $this->getDownloadUrl($signedAttachId),
			'entityId' => (int)$attach->getEntityId(),
			'isFinished' => $attach->isFinished(),
		];
	}

	private function getDownloadUrl(string $signedAttachId): string
	{
		return UrlManager::getInstance()
			->create('vote.AttachedVote.download', ['signedAttachId' => $signedAttachId])
			->getUri()
		;
	}
}