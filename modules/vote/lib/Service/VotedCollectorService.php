<?php

namespace Bitrix\Vote\Service;

use Bitrix\Vote\Attach;
use Bitrix\Vote\Integration\Mobile\MobileUserProvider;
use Bitrix\Vote\Model\Dto\VotedIdPage;
use Bitrix\Vote\Model\Dto\VotedUserPage;
use Bitrix\Vote\Service\UserProvider\BaseUserProvider;
use Bitrix\Vote\Service\UserProvider\UserProviderContract;
use JsonSerializable;

class VotedCollectorService
{
	private UserProviderContract $provider;

	public function __construct(
		bool $isMobileUserFormatter = false,
	) {
		$this->provider = $isMobileUserFormatter ? new MobileUserProvider() : new BaseUserProvider();
	}

	/**
	 * @param Attach $attach
	 * @param int $pageSize
	 *
	 * @return array<int, list<JsonSerializable>>
	 */
	public function getByAttach(Attach $attach, int $pageSize = 50): array
	{
		$userIds = [];
		$byAnswers = [];
		foreach ($attach['QUESTIONS'] ?? [] as $question)
		{
			foreach ($question['ANSWERS'] ?? [] as $answer)
			{
				if (isset($answer['COUNTER']) && (int)$answer['COUNTER'] > 0)
				{
					$answerId = (int)($answer['ID'] ?? 0);
					$byAnswers[$answerId] = $this->getAnsweredUserIds($answerId, $pageSize)->userIds;
					$userIds += $byAnswers[$answerId];
				}
			}
		}

		if (empty($userIds))
		{
			return [];
		}

		$formattedUsers = $this->provider->getByUserIds($userIds)->toArray();

		$formattedByAnswers = [];
		foreach ($byAnswers as $answerId => $answerUserIds)
		{
			$formatedAnswerUsersById = array_intersect_key($formattedUsers, $answerUserIds);
			$sortedUsers = $this->getUsersInIdOrder($formatedAnswerUsersById, $answerUserIds);
			$formattedByAnswers[$answerId] = array_values($sortedUsers);
		}

		return $formattedByAnswers;
	}

	/**
	 * @param int $answerId
	 * @param int $pageSize
	 * @param int $page
	 *
	 * @return VotedUserPage
	 */
	public function getByAnswerId(int $answerId, int $pageSize = 50, int $page = 1): VotedUserPage
	{
		$userIdsPage = $this->getAnsweredUserIds($answerId, $pageSize, $page);
		$formattedUsers = $this->provider->getByUserIds($userIdsPage->userIds)->toArray();
		$sortedUsers = $this->getUsersInIdOrder($formattedUsers, $userIdsPage->userIds);

		return new VotedUserPage(
			pageSize: $pageSize,
			page: $page,
			totalCount: $userIdsPage->totalCount,
			items: array_values($sortedUsers),
		);
	}

	/**
	 * @param array<int, JsonSerializable> $usersById
	 * @param array<int, int> $sortedUserIds
	 *
	 * @return array<int, JsonSerializable>
	 */
	private function getUsersInIdOrder(array $usersById, array $sortedUserIds): array
	{
		$orderedUsers = [];
		foreach ($sortedUserIds as $userId)
		{
			if (isset($usersById[$userId]))
			{
				$orderedUsers[$userId] = $usersById[$userId];
			}
		}

		return $orderedUsers;
	}

	private function getAnsweredUserIds(int $answerId, int $pageSize =  50, int $page = 1): VotedIdPage
	{
		$dbRes = \CVoteEvent::getUserAnswerStat([],
			[
				'ANSWER_ID' => $answerId,
				'VALID' => 'Y',
				'VISIBLE' => 'Y',
				'bGetVoters' => 'Y',
				'bGetMemoStat' => 'N',
			],
			[
				'nPageSize' => max(min($pageSize, 50), 1),
				'iNumPage' => max($page, 1),
			],
		);

		$userIds = [];
		while ($res = $dbRes->fetch())
		{
			$userId = (int)($res["AUTH_USER_ID"] ?? 0);
			if ($userId)
			{
				$userIds[$userId] = $userId;
			}
		}

		return new VotedIdPage(
			pageSize: $pageSize,
			page: $page,
			totalCount: (int)$dbRes->nSelectedCount,
			userIds: $userIds,
		);
	}
}