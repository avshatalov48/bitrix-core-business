<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\Engine\AutoWire\ValidationParameter;
use Bitrix\SocialNetwork\Collab\Access\CollabAccessController;
use Bitrix\SocialNetwork\Collab\Access\CollabDictionary;
use Bitrix\Socialnetwork\Collab\Controller\Dto\InviteLinkDto;
use Bitrix\Socialnetwork\Collab\Controller\Trait\GetCollabIdBySourceTrait;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionMessageFactory;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;

class InviteLink extends Controller
{
	use GetCollabIdBySourceTrait;

	protected ActionMessageFactory $messageFactory;
	protected GroupRegistry $groupRegistry;

	protected int $userId;

	public function getAutoWiredParameters(): array
	{
		return [
			new ValidationParameter(
				InviteLinkDto::class,
				function (): InviteLinkDto {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();
					$requestData['collabId'] = $this->resolveCollabId($request);

					return InviteLinkDto::createFromRequest($requestData);
				}
			),
		];
	}

	/**
	 * @restMethod socialnetwork.collab.InviteLink.onCopy
	 */
	public function onCopyAction(InviteLinkDto $dto): ?bool
	{
		if (!CollabAccessController::can($this->userId, CollabDictionary::INVITE, $dto->collabId))
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$messageId = $this->sendMessage((int)$dto->collabId);
		if ($messageId === 0)
		{
			$this->addError(new Error('Message not sent'));

			return null;
		}

		return true;
	}

	protected function sendMessage(int $collabId): int
	{
		return
			$this
				->messageFactory
				->getActionMessage(ActionType::CopyLink, $collabId, $this->userId)
				->runAction()
			;
	}

	protected function init(): void
	{
		parent::init();

		$this->messageFactory = ActionMessageFactory::getInstance();
		$this->groupRegistry = GroupRegistry::getInstance();

		$this->userId = (int)CurrentUser::get()->getId();
	}
}
