<?php

namespace Bitrix\Bizproc\Integration;

use Bitrix\Bizproc\Api\Request\WorkflowCommentService\CommentRequest;
use Bitrix\Bizproc\Api\Request\WorkflowCommentService\MarkAsReadRequest;
use Bitrix\Bizproc\Api\Service\WorkflowCommentService;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class CommentListener
{
	public static function onAfterCommentAdd(Event $event): void
	{
		[$entityType, $workflowIdInt, $fields] = $event->getParameters();

		if ($entityType !== 'WF' || isset($fields['PARAMS']['SERVICE_TYPE']))
		{
			return;
		}

		$authorId = (int)($fields['PARAMS']['AUTHOR_ID'] ?? 0);
		if (!$authorId)
		{
			return;
		}

		$workflowId = \CBPStateService::getWorkflowByIntegerId($workflowIdInt);
		if (!$workflowId)
		{
			return;
		}

		$created = $fields['PARAMS']['POST_DATE'] ?? new DateTime();
		$mentions = [];
		$messageText = $fields['PARAMS']['POST_MESSAGE'] ?? '';

		if(preg_match_all("/(?<=\[USER=)(?P<id>\d+)(?=])/", $messageText, $matches))
		{
			$mentions = $matches['id'];
		}

		$service = new WorkflowCommentService();
		$service->registerComment(new CommentRequest($workflowId, $authorId, $created, $mentions));
	}

	public static function onCommentDelete(Event $event): void
	{
		[$entityType, $workflowIdInt, $fields] = $event->getParameters();

		if ($entityType !== 'WF')
		{
			return;
		}

		$authorId = (int)($fields['MESSAGE']['AUTHOR_ID'] ?? 0);
		if (!$authorId)
		{
			return;
		}

		$workflowId = \CBPStateService::getWorkflowByIntegerId($workflowIdInt);
		if (!$workflowId)
		{
			return;
		}

		$created = DateTime::createFromUserTime($fields['MESSAGE']['POST_DATE']);

		$service = new WorkflowCommentService();
		$service->unRegisterComment(new CommentRequest($workflowId, $authorId, $created));
	}

	public static function onListsProcessesCommentAdd(string $workflowId, int $authorId, array $mentions): void
	{
		$service = new WorkflowCommentService();
		$service->registerComment(new CommentRequest($workflowId, $authorId, new DateTime(), $mentions));
	}

	public static function onListsProcessesCommentDelete(string $workflowId,  int $authorId, DateTime $created): void
	{
		$service = new WorkflowCommentService();
		$service->unRegisterComment(new CommentRequest($workflowId, $authorId, $created));
	}

	public static function onSocnetContentViewed(Event $event): void
	{
		$params = $event->getParameters();

		if (($params['typeId'] ?? '') !== 'FORUM_POST' || empty($params['entityId']) || empty($params['userId']))
		{
			return;
		}

		if (!Loader::includeModule('forum'))
		{
			return;
		}

		$xmlId = \Bitrix\Forum\MessageTable::query()
			->setSelect(['XML_ID'])
			->where('ID', $params['entityId'])
			->fetch()['XML_ID'] ?? null
		;

		if (!$xmlId || strpos($xmlId, 'WF_') !== 0)
		{
			return;
		}

		$workflowId = substr($xmlId, 3);

		$service = new WorkflowCommentService();
		$service->markAsRead(new MarkAsReadRequest($workflowId, (int)$params['userId']));
	}
}
