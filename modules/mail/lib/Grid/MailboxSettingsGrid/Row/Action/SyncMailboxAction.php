<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Action;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class SyncMailboxAction extends JsGridAction
{
	public static function getId(): ?string
	{
		return 'sync_mailbox';
	}

	public function processRequest(Main\HttpRequest $request): ?Main\Result
	{
		return null;
	}

	protected function getText(): string
	{
		return Loc::getMessage('MAIL_MAILBOX_LIST_ROW_ACTIONS_SYNC_MAILBOX') ?? '';
	}

	public function isEnabled(array $rawFields): bool
	{
		return true;
	}

	public function getActionId(): string
	{
		return 'syncAction';
	}

	protected function getActionParams(array $rawFields): array
	{
		return [
			'mailboxId' => $rawFields['ID'],
		];
	}
}