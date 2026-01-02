<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Action;

use Bitrix\Mail\Grid\MailboxSettingsGrid\Settings\MailboxSettings;
use Bitrix\Main\Grid\Row\Action\DataProvider;

/**
 * @method MailboxSettings getSettings()
 */
class MailboxDataProvider extends DataProvider
{
	public function prepareActions(): array
	{
		return [
			new OpenSettingsAction(),
			new SyncMailboxAction($this->getSettings()),
		];
	}
}