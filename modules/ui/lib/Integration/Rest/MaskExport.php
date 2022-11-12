<?php
namespace Bitrix\UI\Integration\Rest;

use \Bitrix\Main;
use \Bitrix\Main\Event;
use \Bitrix\Ui;
use \Bitrix\UI\Avatar;

class MaskExport extends ExportStep
{
	private const PAGE_SIZE = 1;
	public static function getSettings(Event $event): ?array
	{
		$step = $event->getParameter('STEP');
		$setting = $event->getParameter('SETTING');
		return [
			'SETTING' => $setting,
			'NEXT' => false
		];
	}

	public function init(): void
	{
		$query = Avatar\Mask\ItemTable::query()
			->setFilter([
				'=OWNER_TYPE' => Avatar\Mask\Owner\User::class,
				'=OWNER_ID' => $this->entityId
			])
			->setSelect(['ID', 'FILE_ID', 'TITLE', 'DESCRIPTION', 'SORT'])
			->setOrder(['ID' => 'ASC'])
			->setLimit(static::PAGE_SIZE)
			->setOffset($this->stepNumber * static::PAGE_SIZE)
			->exec();

		while ($res = $query->fetch())
		{
			$this->data[] = $res;
			$this->files[] = ['ID' => $res['FILE_ID']];
		}
		if ($this->data->count() >= static::PAGE_SIZE)
		{
			$this->nextStep->set('last', $this->data->current());
		}
	}
}