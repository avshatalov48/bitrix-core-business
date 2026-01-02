<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Logger;

use Bitrix\Calendar\Synchronization\Internal\Model\CalendarLogTable;
use Bitrix\Main\Diag\Logger;
use Bitrix\Main\Web\Json;

class DatabaseLogger extends Logger
{
	protected function logMessage(string $level, string $message): void
	{
		$context = $this->context;

		CalendarLogTable::add($this->buildFields($level, $message, $context));
	}

	private function buildFields(string $level, string $message, array $context = []): array
	{
		$fields = [
			'LEVEL' => $level,
			'MESSAGE' => $message,
		];

		if (isset($context['type']))
		{
			$fields['TYPE'] = (string)$context['type'];

			unset($context['type']);
		}

		if (isset($context['entityId']))
		{
			$fields['UUID'] = (string)$context['entityId'];

			unset($context['entityId']);
		}

		if (isset($context['userId']))
		{
			$fields['USER_ID'] = (int)$context['userId'];

			unset($context['userId']);
		}

		if (!empty($context))
		{
			$encoded = Json::encode($context);
			$maxSize = 16 * 1024 * 1024 - 1024; // 16 MB - 1 KB for safety, medium text field size in MySQL
			if (strlen($encoded) > $maxSize)
			{
				$encoded = substr($encoded, 0, $maxSize);
				$encoded = rtrim($encoded, ',');
				$lastChar = substr($encoded, -1);
				if ($lastChar !== '}' && $lastChar !== ']')
				{
					$encoded .= '}';
				}
			}

			$fields['CONTEXT'] = $encoded;
		}
		else
		{
			$fields['CONTEXT'] = '{}';
		}

		return $fields;
	}
}
