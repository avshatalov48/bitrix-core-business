<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

use Bitrix\Main\Localization\Loc;

class EmailWithCounterFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'EmailWithCounterField';

	/**
	 * @return array{email: string, count: int, isOverLimit: bool}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		$email = $rawValue['EMAIL'] ?? '';
		$serviceName = $rawValue['SERVICE_NAME'] ?? '';
		$count = $rawValue['COUNTERS']['EMAIL']['value'] ?? 0;
		$isOverLimit = $rawValue['COUNTERS']['EMAIL']['isOverLimit'] ?? false;

		return [
			'email' => $email,
			'serviceName' => $serviceName,
			'count' => (int)$count,
			'isOverLimit' => $isOverLimit,
			'counterHintText' => Loc::getMessage('MAIL_MAILBOX_LIST_ROW_FIELDS_COUNTER_HINT'),
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		return $data['EMAIL'] ?? '';
	}
}
