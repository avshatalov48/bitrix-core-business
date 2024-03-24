<?php
namespace Bitrix\Rest;


use Bitrix\Main\Entity\Query;

class EventOfflineQuery extends Query
{
	public function mark(string $processId): void
	{
		$ids = $this->setSelect(['ID'])->fetchAll();

		if (!empty($ids))
		{
			EventOfflineTable::updateMulti($ids, ['PROCESS_ID' => $processId]);
		}
	}
}