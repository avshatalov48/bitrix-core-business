<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

class SenderNameFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'SenderNameField';

	/**
	 * @return array{senderName: string}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		return [
			'senderName' => $rawValue['SENDER_NAME'] ?? '',
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		return $data['SENDER_NAME'] ?? '';
	}
}
