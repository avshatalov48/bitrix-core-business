<?php

namespace Bitrix\Vote\Controller;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Engine\ActionFilter\CloseSession;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\Request;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Vote\Attach;
use Bitrix\Vote\Attachment\Manager;
use Bitrix\Vote\Controller\Filter\CheckAttachReadAccess;
use Bitrix\Vote\Controller\Filter\CheckAttachReadAccessWithSign;
use Bitrix\Vote\Model\Dto\AttachedVotePayload;
use Bitrix\Vote\Service\AttachedVoteFrontendFormatService;
use Bitrix\Vote\Integration\Pull\VoteChangesSender;
use Bitrix\Vote\Service\VotedCollectorService;

class AttachedVote extends Controller
{
	private const ENTITY_IDS_LIMIT = 50;
	private AttachedVoteFrontendFormatService $formatService;
	private VoteChangesSender $changesSender;

	public function __construct(Request $request = null)
	{
		$this->formatService = new AttachedVoteFrontendFormatService();
		$this->changesSender = new VoteChangesSender();
		parent::__construct($request);
	}

	public function configureActions(): array
	{
		return [
			'download' => [
				'-prefilters' => [
					Csrf::class,
					CheckAttachReadAccess::class,
				],
				'+prefilters' => [new CheckAttachReadAccessWithSign()],
			],
			'get' => [
				'-prefilters' => [CheckAttachReadAccess::class],
				'+prefilters' => [new CheckAttachReadAccessWithSign()],
			],
			'getAnswerVoted' => [
				'-prefilters' => [CheckAttachReadAccess::class],
				'+prefilters' => [new CheckAttachReadAccessWithSign()],
			],
			'getWithVoted' => [
				'-prefilters' => [CheckAttachReadAccess::class],
				'+prefilters' => [new CheckAttachReadAccessWithSign()],
			],
		];
	}

	protected function getDefaultPreFilters(): array
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new CloseSession(true),
				new CheckAttachReadAccess(),
			]
		);
	}

	public function getAutoWiredParameters(): array
	{
		return [
			new ExactParameter(
				AttachedVotePayload::class,
				'attachPayload',
				function(string $className, int $attachId): AttachedVotePayload
				{
					return AttachedVotePayload::makeByAttachId($attachId);
				},
			),
			new ExactParameter(
				AttachedVotePayload::class,
				'attachPayload',
				function(string $className, string $moduleId, string $entityType, int $entityId): AttachedVotePayload
				{
					return AttachedVotePayload::makeByEntityId($moduleId, $entityType, $entityId);
				}
			),
			new ExactParameter(
				AttachedVotePayload::class,
				'attachPayload',
				function(string $className, string $signedAttachId): AttachedVotePayload
				{
					return AttachedVotePayload::makeBySignedAttachId($signedAttachId);
				}
			)
		];
	}

	public function getAction(AttachedVotePayload $attachPayload): array
	{
		$attach = $attachPayload->attach;
		$this->changesSender->addUserWatch($this->getUserId(), $attach->getVoteId());

		return [
			'attach' => $this->formatService->format($attach, $this->getUserId()),
		];
	}

	public function voteAction(
		AttachedVotePayload $attachPayload,
		array $ballot,
		string $actionUuid = '',
	): array
	{
		$attach = $attachPayload->attach;
		$voted = $attach->voteFor(
			[\Bitrix\Vote\Event::EVENT_FIELD_NAME => [$attach->getAttachId() => ['BALLOT' => $ballot]]],
			$actionUuid,
		);
		if ($voted)
		{
			return [
				'attach' => $this->formatService->format($attach, $this->getUserId()),
			];
		}

		$errors = $attach->getErrors()
			? $attach->getErrors()
			: [new Error('VOTE_CONTROLLER_ATTACH_VOTE_DEFAULT_ERROR')]
		;
		$this->addErrors($errors);

		return [];
	}

	public function stopAction(AttachedVotePayload $attachPayload, string $actionUuid = ''): array
	{
		$attach = $attachPayload->attach;
		$this->throwAccessDeniedIfNotAbleToEdit($attach);
		$attach->stop($actionUuid);

		return [];
	}

	public function resumeAction(AttachedVotePayload $attachPayload, string $actionUuid = ''): array
	{
		$attach = $attachPayload->attach;
		$this->throwAccessDeniedIfNotAbleToEdit($attach);
		$attach->resume($actionUuid);

		return [];
	}

	public function getAnswerVotedAction(
		AttachedVotePayload $attachPayload,
		int $answerId,
		PageNavigation $pageNavigation,
		bool $userForMobileFormat = false,
	): Page
	{
		$attach = $attachPayload->attach;
		$this->throwAccessDeniedIfAnswerNotBelongsAttachedVote($attach, $answerId);

		$votedService = new VotedCollectorService($userForMobileFormat);
		$votedUserPage = $votedService->getByAnswerId($answerId, $pageNavigation->getPageSize(), $pageNavigation->getCurrentPage());

		return new Page('items', $votedUserPage->items, $votedUserPage->totalCount);
	}

	public function recallAction(AttachedVotePayload $attachPayload, string $actionUuid = ''): array
	{
		$attach = $attachPayload->attach;
		if (!$attach->canRead($this->getUserId()))
		{
			$this->addError(new Error('Attach read access denied', 'ATTACH_READ_ACCESS_DENIED'));

			return [];
		}

		$result = $attach->recall($this->getUserId(), $actionUuid);
		if ($result->isSuccess())
		{
			return [
				'attach' => $this->formatService->format($attach, $this->getUserId()),
			];
		}

		$this->addErrors($result->getErrors());

		return [];
	}

	public function downloadAction(AttachedVotePayload $attachPayload): void
	{
		$attach = $attachPayload->attach;
		$attach->exportExcel();
	}

	public function getWithVotedAction(
		AttachedVotePayload $attachPayload,
		int $pageSize = 10,
		bool $userForMobileFormat = false,
	): array
	{
		$attach = $attachPayload->attach;
		$votedService = new VotedCollectorService($userForMobileFormat);
		$this->changesSender->addUserWatch($this->getUserId(), $attach->getVoteId());

		return [
			'attach' => $this->formatService->format($attach, $this->getUserId()),
			'voted' => $votedService->getByAttach($attach, $pageSize),
		];
	}

	public function getManyAction(string $moduleId, string $entityType, array $entityIds): array
	{
		$entityIds = array_filter(array_map(fn($value) => (int)$value, $entityIds));
		if (count($entityIds) > self::ENTITY_IDS_LIMIT)
		{
			$this->addError(new Error('Too many entity ids'));

			return [];
		}

		$userId = $this->getUserId();
		$formatted = [];
		foreach ($entityIds as $entityId)
		{
			$attach = Manager::loadFirstFromEntity($moduleId, $entityType, $entityId);
			if (!$attach)
			{
				$this->addError(new Error("Attach with entityId $entityId not found "));

				return [];
			}

			if (!$attach->canRead($userId))
			{
				$this->addError(new Error("Attach with entityId $entityId read access denied"));

				return [];
			}

			$formatted[] = $this->formatService->format($attach, $userId);
			$this->changesSender->addUserWatch($this->getUserId(), $attach->getVoteId());
		}

		return [
			'items' => $formatted,
		];
	}

	private function throwAccessDeniedIfNotAbleToEdit(Attach $attach): void
	{
		if (!$attach->canEdit($this->getUserId()))
		{
			throw new AccessDeniedException();
		}
	}

	private function getUserId(): int
	{
		return (int)$this->getCurrentUser()?->getId();
	}

	private function throwAccessDeniedIfAnswerNotBelongsAttachedVote(Attach $attach, int $answerId): void
	{
		if (!$this->isAnswerBelongsToAttachedVote($attach, $answerId))
		{
			throw new AccessDeniedException();
		}
	}

	private function isAnswerBelongsToAttachedVote(Attach $attach, int $answerId): bool
	{
		foreach ($attach["QUESTIONS"] ?? [] as $question)
		{
			if (array_key_exists($answerId, $question["ANSWERS"]))
			{
				return true;
			}
		}

		return false;
	}

	protected function writeToLogException(\Throwable $e): void
	{
		if ($e instanceof AccessDeniedException)
		{
			return; // do not write to error log access denied errors
		}

		parent::writeToLogException($e);
	}
}