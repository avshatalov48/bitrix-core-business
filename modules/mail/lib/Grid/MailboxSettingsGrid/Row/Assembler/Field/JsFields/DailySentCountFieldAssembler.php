<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

class DailySentCountFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'DailySentCountField';

	/**
	 * @return array{dailySentCount: int, dailySentLimit: int|null}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		return [
			'dailySentCount' => $rawValue['SENT_LIMITS_AND_COUNTERS']['daily_sent'],
			'dailySentLimit' => $rawValue['SENT_LIMITS_AND_COUNTERS']['daily_limit'],
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		$limit = null;
		if ($data['SENT_LIMITS_AND_COUNTERS']['daily_limit'])
		{
			$limit = $data['SENT_LIMITS_AND_COUNTERS']['daily_limit'];
		}

		$sentCount = $data['SENT_LIMITS_AND_COUNTERS']['daily_sent'];
		if ($limit !== null)
		{
			$output = "{$sentCount}/{$limit}";
		}
		else
		{
			$output = (string)$sentCount;
		}

		return $output;
	}
}
