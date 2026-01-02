<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Row\Assembler\Field\JsFields;

class MonthlySentCountFieldAssembler extends JsExtensionFieldAssembler
{
	private const EXTENSION_CLASS_NAME = 'MonthlySentCountField';

	/**
	 * @return array{monthlySentCount: int, monthlySentLimit: int|null}
	 */
	protected function getRenderParams(array $rawValue): array
	{
		return [
			'monthlySentCount' => $rawValue['SENT_LIMITS_AND_COUNTERS']['monthly_sent'],
			'monthlySentLimit' => $rawValue['SENT_LIMITS_AND_COUNTERS']['monthly_limit'],
		];
	}

	protected function getExtensionClassName(): string
	{
		return self::EXTENSION_CLASS_NAME;
	}

	protected function prepareColumnForExport(array $data): string
	{
		$limit = null;
		if ($data['SENT_LIMITS_AND_COUNTERS']['monthly_limit'])
		{
			$limit = $data['SENT_LIMITS_AND_COUNTERS']['monthly_limit'];
		}

		$sentCount = $data['SENT_LIMITS_AND_COUNTERS']['monthly_sent'];
		if ($limit !== null)
		{
			$percent = $sentCount / $limit * 100;
			$output = "{$sentCount}/{$limit} ($percent%)";
		}
		else
		{
			$output = (string)$sentCount;
		}

		return $output;
	}
}
