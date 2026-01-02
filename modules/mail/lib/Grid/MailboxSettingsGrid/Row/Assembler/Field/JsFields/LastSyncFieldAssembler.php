<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

class LastSyncFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'LastSyncField';

	/**
	 * @return array{lastSync: ?int, mailboxId: ?int}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		return [
			'lastSync' => $rawValue['LAST_ACTIVITY'] ?? null,
			'mailboxId' => $rawValue['ID'] ?? null,
			'hasError' => $rawValue['HAS_ERROR'] ?? null,
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		return $data['LAST_ACTIVITY'] ?? '';
	}
}
