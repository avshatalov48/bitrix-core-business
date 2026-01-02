<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Column\Provider;

use Bitrix\Main\Grid\Column\DataProvider;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class MailboxDataProvider extends DataProvider
{
	public function prepareColumns(): array
	{
		$result = [];

		$result[] =
			$this->createColumn('OWNER')
				->setSelect(['OWNER'])
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_OWNER'))
				->setDefault(true)
				->setSort('OWNER')
		;

		$result[] =
			$this->createColumn('EMAIL')
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_EMAIL'))
				->setDefault(true)
				->setSort('EMAIL')
		;

		$result[] =
			$this->createColumn('LAST_SYNC')
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_LAST_SYNC'))
				->setDefault(true)
				->setSort('LAST_SYNC')
		;

		$result[] =
			$this->createColumn('CRM_ENABLED_STATUS')
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_CRM_ENABLED_STATUS'))
				->setDefault(true)
		;

		$result[] =
			$this->createColumn('VOLUME_MB')
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_VOLUME_MB'))
				->setDefault(true)
		;

		$result[] =
			$this->createColumn('ACTION')
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_ACTION'))
				->setDefault(true)
		;

		$result[] =
			$this->createColumn('MAILBOX_NAME')
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_MAILBOX_NAME'))
				->setDefault(false)
		;

		$result[] =
			$this->createColumn('USERS_WITH_ACCESS')
				->setSelect(['USERS_WITH_ACCESS'])
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_USERS_WITH_ACCESS'))
				->setDefault(false)
		;

		$result[] =
			$this->createColumn('CRM_LEAD_RESP')
				->setSelect(['CRM_LEAD_RESP'])
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_CRM_LEAD_RESP'))
				->setDefault(false)
		;

		$result[] =
			$this->createColumn('SENDER_NAME')
				->setSelect(['SENDER_NAME'])
				->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_SENDER_NAME'))
				->setDefault(false)
		;

		if (false && ModuleManager::isModuleInstalled('bitrix24'))
		{
			$result[] =
				$this->createColumn('DAILY_SENT_COUNT')
					->setSelect(['DAILY_SENT_COUNT'])
					->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_DAILY_SENT_COUNT'))
					->setDefault(false)
			;

			$result[] =
				$this->createColumn('MONTHLY_SENT_COUNT')
					->setSelect(['MONTHLY_SENT_COUNT'])
					->setName(Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_MONTHLY_SENT_COUNT'))
					->setDefault(false)
			;
		}

		return $result;
	}
}
