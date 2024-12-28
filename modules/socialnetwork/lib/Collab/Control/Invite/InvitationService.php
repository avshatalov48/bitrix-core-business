<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Invite;

use Bitrix\Main\Config\Option;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\ValidationService;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Invite\Command\InvitationCommand;
use Bitrix\Socialnetwork\Collab\Permission\UserRole;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Collab\User\User;
use Bitrix\Socialnetwork\UserToGroupTable;
use CIMNotify;

class InvitationService
{
	protected const MAX_NOTIFY_LENGTH = 150;
	protected const REQUEST_URL = '/company/personal/user/#USER_ID#/requests/';

	protected ValidationService $validationService;
	protected User $sender;
	protected User $recipient;
	protected Collab $collab;
	protected string $serverName;
	protected int $relationId;

	public function __construct()
	{
		$this->init();
	}

	public function send(InvitationCommand $command): Result
	{
		$result = new Result();

		$validationResult = $this->validationService->validate($command);
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		$this->sender = new User($command->getInitiatorId());
		$this->recipient = new User($command->getRecipientId());
		$collab = CollabRegistry::getInstance()->get($command->getCollabId());

		if ($collab === null)
		{
			return $result;
		}

		$this->collab = $collab;
		$this->relationId = $command->hasRelationId() ? $command->getRelationId() : $this->getRelationId();

		if ($this->relationId <= 0)
		{
			$result->addError(new Error('No relation', 'INVITE_NO_RELATION'));

			return $result;
		}

		$messageFields = $this->getMessageFields();

		$messageResult = CIMNotify::Add($messageFields);
		if ($messageResult === false)
		{
			global $APPLICATION;
			$error = $APPLICATION->GetException();

			if ($error)
			{
				$result->addError(new Error($error->msg, $error->id));
			}
			else
			{
				$result->addError(new Error('Invite is not sent', 'INVITE_NOT_SENT'));
			}
		}

		return $result;
	}

	protected function getNotifyMessageOut(): callable
	{
		return function(?string $languageId = null): string {

			$title = Loc::getMessage('SONET_COLLAB_INVITE_TEXT', [
				'#NAME#' => $this->collab->getName(),
			], $languageId);

			$confirm = Loc::getMessage('SONET_COLLAB_INVITE_CONFIRM', null, $languageId)
				. ': '
				. $this->getActionLink();

			$reject = Loc::getMessage('SONET_COLLAB_INVITE_REJECT', null, $languageId)
				. ': '
				. $this->getActionLink(false);

			return "{$title}\n\n{$confirm}\n\n{$reject}";
		};
	}

	protected function getActionLink(bool $confirm = true): string
	{
		$confirmValue = $confirm ? 'Y' : 'N';

		return "{$this->getRequestUrl()}?INVITE_GROUP={$this->relationId}&CONFIRM={$confirmValue}";
	}

	protected function getMessageFields(): array
	{
		return [
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"TO_USER_ID" => $this->recipient->getId(),
			"FROM_USER_ID" => $this->sender->getId(),
			"NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
			"NOTIFY_MODULE" => "socialnetwork",
			"NOTIFY_EVENT" => "invite_group_btn",
			"NOTIFY_TAG" => $this->getNotifyTag(),
			"NOTIFY_TITLE" => $this->getNotifyTitle(),
			"NOTIFY_MESSAGE" => $this->getNotifyMessage(),
			"NOTIFY_BUTTONS" => $this->getButtons(),
			'NOTIFY_MESSAGE_OUT' => $this->getNotifyMessageOut(),
		];
	}

	protected function getNotifyTag(): string
	{
		return "SOCNET|INVITE_GROUP|{$this->recipient->getId()}|{$this->relationId}";
	}

	protected function getNotifyTitle(): callable
	{
		return fn(?string $languageId = null): string => Loc::getMessage('SONET_COLLAB_INVITE_TEXT', [
			'#NAME#' => rtrim(mb_substr($this->collab->getName(), 0, static::MAX_NOTIFY_LENGTH), '.') . '...',
		], $languageId);
	}

	protected function getNotifyMessage(): callable
	{
		return fn(?string $languageId = null): string => Loc::getMessage('SONET_COLLAB_INVITE_TEXT', [
			'#NAME#' => $this->collab->getName(),
		], $languageId);
	}

	protected function getButtons(): array
	{
		return [
			[
				'TITLE' => static fn(?string $languageId = null): string => Loc::getMessage(
					'SONET_COLLAB_INVITE_CONFIRM',
					null,
					$languageId,
				),
				'VALUE' => 'Y',
				'TYPE' => 'accept',
			],
			[
				'TITLE' => static fn(?string $languageId = null): string => Loc::getMessage(
					'SONET_COLLAB_INVITE_REJECT',
					null,
					$languageId,
				),
				'VALUE' => 'N',
				'TYPE' => 'cancel',
			],
		];
	}

	protected function getRequestUrl(): string
	{
		$requestUrl = Option::get(
			'socialnetwork',
			'user_request_page',
			static::REQUEST_URL,
		);

		return str_replace(["#USER_ID#", "#user_id#"], (string)$this->recipient->getId(), $requestUrl);
	}

	protected function getRelationId(): int
	{
		$row = UserToGroupTable::query()
			->setSelect(['ID'])
			->where('GROUP_ID', $this->collab->getId())
			->where('USER_ID', $this->recipient->getId())
			->where('ROLE', UserRole::REQUEST)
			->exec()
			->fetch();

		if (empty($row))
		{
			return 0;
		}

		return (int)$row['ID'];
	}

	protected function init(): void
	{
		$this->validationService = ServiceLocator::getInstance()->get('main.validation.service');
	}
}
