<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

use Bitrix\Main\Localization\Loc;

class CRMStatusFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'CRMStatusField';

	/**
	 * @return array{enabled: bool}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		return [
			'enabled' => $rawValue['CRM_ENABLED'] === 'Y',
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		$crmEnabled = $data['CRM_ENABLED'] === 'Y';

		return
			$crmEnabled
				? Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_CRM_STATUS_ENABLED')
				: Loc::getMessage('MAIL_MAILBOX_LIST_COLUMN_CRM_STATUS_DISABLED')
			;
	}
}
