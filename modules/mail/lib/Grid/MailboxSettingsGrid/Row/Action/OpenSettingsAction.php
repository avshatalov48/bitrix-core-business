<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Action;

use Bitrix\Main\Grid\Row\Action\BaseAction;
use Bitrix\Main\Localization\Loc;

class OpenSettingsAction extends BaseAction
{
	public static function getId(): ?string
	{
		return 'open_settings';
	}

	public function processRequest(\Bitrix\Main\HttpRequest $request): ?\Bitrix\Main\Result
	{
		return null;
	}

	protected function getText(): string
	{
		return Loc::getMessage('MAIL_MAILBOX_LIST_ROW_ACTIONS_OPEN_SETTINGS') ?? '';
	}

	public function getControl(array $rawFields): ?array
	{
		$mailboxId = (int)$rawFields['ID'];
		$url = sprintf("/mail/config/edit?id=%d", $mailboxId);
		$this->onclick = sprintf("top.BX.SidePanel.Instance.open('%s')", $url);

		return parent::getControl($rawFields);
	}
}