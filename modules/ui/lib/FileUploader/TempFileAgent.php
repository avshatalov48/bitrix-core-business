<?php

namespace Bitrix\UI\FileUploader;

use Bitrix\Main\Type\DateTime;

class TempFileAgent
{
	const AGENT_MAX_INTERVAL = 1800;
	const AGENT_MIN_INTERVAL = 360;

	public static function clearOldRecords(): string
	{
		$expired = new DateTime();
		$expired->add('-1 days');
		$limit = 20;

		$tempFiles = TempFileTable::getList([
			'filter' => ['<CREATED_AT' => $expired->toString()],
			'limit' => $limit,
			'order' => ['CREATED_AT' => 'ASC']
		])->fetchCollection();

		foreach ($tempFiles as $tempFile)
		{
			$tempFile->delete();
		}

		$agentName = '\\' . __METHOD__ . '();';
		$agents = \CAgent::getList(['ID' => 'DESC'], [
			'MODULE_ID' => 'ui',
			'NAME' => $agentName,
		]);

		if ($agent = $agents->fetch())
		{
			$interval = $tempFiles->count() < $limit ? static::AGENT_MAX_INTERVAL : static::AGENT_MIN_INTERVAL;
			if ((int)$agent['AGENT_INTERVAL'] !== $interval)
			{
				\CAgent::update($agent['ID'], ['AGENT_INTERVAL' => $interval]);
			}
		}

		return $agentName;
	}
}
