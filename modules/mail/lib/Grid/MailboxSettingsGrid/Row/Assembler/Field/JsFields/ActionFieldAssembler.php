<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

class ActionFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'ActionField';

	/**
	 * @return array{url: string}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		$mailboxId = (int)$rawValue['ID'];
		$url = sprintf("/mail/config/edit?id=%d", $mailboxId);

		return [
			'url' => $url,
			'hasError' => $rawValue['HAS_ERROR'] ?? null,
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		$mailboxId = (int)$data['ID'];

		return sprintf("/mail/config/edit?id=%d", $mailboxId);
	}
}
