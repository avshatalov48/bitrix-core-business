<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Request\WorkflowCommentService\CommentRequest;
use Bitrix\Bizproc\Api\Request\WorkflowCommentService\MarkAsReadRequest;
use Bitrix\Bizproc\Api\Request\WorkflowCommentService\AddSystemCommentRequest;
use Bitrix\Bizproc\Api\Response\WorkflowCommentService\AddSystemCommentResponse;
use Bitrix\Bizproc\Integration\Push\CommentPush;
use Bitrix\Bizproc\Integration\Push\Dto\UserCounter;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable;
use Bitrix\Bizproc\Workflow\WorkflowUserCounters;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Forum;

class WorkflowCommentService
{
	public function registerComment(CommentRequest $comment): void
	{
		if (!$this->isCorrectRequest($comment))
		{
			return;
		}

		$toIncrement = $this->getRecipientsByComment($comment);
		if ($toIncrement)
		{
			WorkflowUserCommentTable::incrementUnreadCounter($comment->workflowId, $toIncrement);
			$this->incrementUsersCounters($toIncrement);
			$this->pushCounters($comment->workflowId, $toIncrement);

			$documentId = \CBPStateService::getStateDocumentId($comment->workflowId); //TODO using events mechanism
			$documentService = \CBPRuntime::getRuntime()->getDocumentService();
			$documentService->onWorkflowCommentAdded($documentId, $comment->workflowId, $comment->authorId);
		}
	}

	public function unRegisterComment(CommentRequest $comment): void
	{
		if (!$this->isCorrectRequest($comment))
		{
			return;
		}

		$userIds = WorkflowUserCommentTable::decrementUnreadCounterByDate($comment->workflowId, $comment->created);
		$this->decrementUsersCounters($userIds);
		$this->pushCounters($comment->workflowId, $userIds);

		$documentId = \CBPStateService::getStateDocumentId($comment->workflowId); //TODO using events mechanism
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();
		$documentService->onWorkflowCommentDeleted($documentId, $comment->workflowId, $comment->authorId);
	}

	public function markAsRead(MarkAsReadRequest $markRead): void
	{
		$filter = [
			'=WORKFLOW_ID' => $markRead->workflowId,
			'=USER_ID' => $markRead->userId,
		];

		$hasUnread = (bool)WorkflowUserCommentTable::query()->setFilter($filter)->fetch();

		if ($hasUnread)
		{
			$documentId = \CBPStateService::getStateDocumentId($markRead->workflowId); //TODO using events mechanism
			$documentService = \CBPRuntime::getRuntime()->getDocumentService();
			$documentService->onWorkflowAllCommentViewed($documentId, $markRead->workflowId, $markRead->userId);

			WorkflowUserCommentTable::delete([
				'WORKFLOW_ID' => $markRead->workflowId,
				'USER_ID' => $markRead->userId,
			]);
			$this->updateUserCounters($markRead->userId);
			$this->pushCounters($markRead->workflowId, [$markRead->userId]);
		}
	}

	public function addSystemComment(AddSystemCommentRequest $comment): AddSystemCommentResponse
	{
		$response = new AddSystemCommentResponse();
		if (!Loader::includeModule('forum'))
		{
			$response->addError(new Error('no forum here')); // TODO

			return $response;
		}

		$workflowIdInt = \CBPStateService::getWorkflowIntegerId($comment->workflowId);

		$feed = new Forum\Comments\Feed(
			\CBPHelper::getForumId(),
			[
				'type' => 'WF',
				'id' => $workflowIdInt,
				'xml_id' => 'WF_' . $comment->workflowId,
			],
			$comment->authorId,
		);

		if (!$feed->addServiceComment([
			'POST_MESSAGE' => $comment->message,
		]))
		{
			$response->addErrors($feed->getErrors());
		}
		else
		{
			WorkflowUserCommentTable::incrementUnreadCounter(
				$comment->workflowId,
				[$comment->authorId],
				WorkflowUserCommentTable::COMMENT_TYPE_SYSTEM
			);
			$this->incrementUsersCounters([$comment->authorId]);
			$this->pushCounters($comment->workflowId, [$comment->authorId]);
		}

		return $response;
	}

	private function getRecipientsByComment(CommentRequest $comment): array
	{
		$workflowUsers = $this->getWorkflowUsers($comment->workflowId);
		unset($workflowUsers[$comment->authorId]);

		if (!$workflowUsers)
		{
			return [];
		}

		$mentions = array_map(static fn($userId) => (int)$userId, $comment->mentionUserIds);
		$directly = array_intersect($mentions, array_keys($workflowUsers));

		if ($directly)
		{
			return $directly;
		}

		//send to active users
		$activeUsers = array_filter(
			$workflowUsers,
			static fn ($user) => $user['TASK_STATUS'] === WorkflowUserTable::TASK_STATUS_ACTIVE,
		);

		if ($activeUsers)
		{
			return array_keys($activeUsers);
		}

		$author = array_filter(
			$workflowUsers,
			static fn ($user) => $user['IS_AUTHOR'] === 1,
		);

		return array_keys($author);
	}

	private function getWorkflowUsers(string $workflowId): array
	{
		$result = WorkflowUserTable::getList([
			'select' => ['USER_ID', 'IS_AUTHOR', 'TASK_STATUS'],
			'filter' => ['=WORKFLOW_ID' => $workflowId],
		]);

		$users = [];

		while ($row = $result->fetch())
		{
			$users[(int)$row['USER_ID']] = [
				'IS_AUTHOR' => (int)$row['IS_AUTHOR'],
				'TASK_STATUS' => (int)$row['TASK_STATUS'],
			];
		}

		return $users;
	}

	private function isCorrectRequest(CommentRequest $comment): bool
	{
		return (
			$comment->workflowId
			&& $comment->authorId
			&& WorkflowStateTable::exists($comment->workflowId)
		);
	}

	private function pushCounters(string $workflowId, array $touchUserIds): void
	{
		$userIds = WorkflowUserTable::getUserIdsByWorkflowId($workflowId);
		$rows = WorkflowUserCommentTable::query()
			->setSelect(['USER_ID', 'UNREAD_CNT'])
			->where('WORKFLOW_ID', $workflowId)
			->fetchAll();

		$values = array_combine(
			array_column($rows, 'USER_ID'),
			array_column($rows, 'UNREAD_CNT')
		);

		$all = 0;
		if (Loader::includeModule('forum'))
		{
			$topic = \CForumTopic::getList([], ['XML_ID' => 'WF_' . $workflowId])->fetch() ?: [];
			$all = (int)($topic['POSTS'] ?? 0);
		}

		foreach ($userIds as $userId)
		{
			CommentPush::pushCounter(
				$workflowId,
				$userId,
				new UserCounter(
					$all,
					$values[$userId] ?? 0,
					WorkflowUserCommentTable::getCountUserUnread($userId),
				),
			);
		}

		WorkflowUserTable::touchWorkflowUsers($workflowId, $touchUserIds);
	}

	private function incrementUsersCounters(array $userIds): void
	{
		foreach ($userIds as $userId)
		{
			$userCounters = new WorkflowUserCounters($userId);
			$userCounters->incrementComment();
		}
	}

	private function decrementUsersCounters(array $userIds): void
	{
		foreach ($userIds as $userId)
		{
			$userCounters = new WorkflowUserCounters($userId);
			$userCounters->decrementComment();
		}
	}

	private function updateUserCounters(int $userId): void
	{
		$userCounters = new WorkflowUserCounters($userId);
		$userCounters->setComment(WorkflowUserCommentTable::getCountUserUnread($userId));
	}
}
