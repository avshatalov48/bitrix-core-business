<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler;

use Bitrix\Main\Grid\Row\RowAssembler;
use Bitrix\Main\Grid\Settings;

class MailboxRowAssembler extends RowAssembler
{
	protected Settings $settings;

	public function __construct(array $visibleColumnIds, Settings $settings)
	{
		parent::__construct($visibleColumnIds);
		$this->settings = $settings;
	}

	protected function prepareFieldAssemblers(): array
	{
		return [
			new Field\JsFields\EmployeeFieldAssembler(['OWNER'], 'OWNER_DATA', $this->settings),
			new Field\JsFields\SenderNameFieldAssembler(['SENDER_NAME'], $this->settings),
			new Field\JsFields\EmailWithCounterFieldAssembler(['EMAIL'], $this->settings),
			new Field\JsFields\LastSyncFieldAssembler(['LAST_SYNC'], $this->settings),
			new Field\JsFields\CRMStatusFieldAssembler(['CRM_ENABLED_STATUS'], $this->settings),
			new Field\JsFields\UsersWithAvatarsFieldAssembler(['USERS_WITH_ACCESS'], 'USERS_DATA', $this->settings),
			new Field\JsFields\UsersWithAvatarsFieldAssembler(['CRM_LEAD_RESP'], 'CRM_LEAD_RESP_DATA', $this->settings),
			new Field\JsFields\DailySentCountFieldAssembler(['DAILY_SENT_COUNT'], $this->settings),
			new Field\JsFields\MonthlySentCountFieldAssembler(['MONTHLY_SENT_COUNT'], $this->settings),
			new Field\JsFields\ActionFieldAssembler(['ACTION'], $this->settings),
		];
	}
}
