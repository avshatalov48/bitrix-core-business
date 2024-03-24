<?php

namespace Bitrix\Calendar\Infrastructure\Persistence;

use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Service\SectionRepository;

class OrmSectionRepository implements SectionRepository
{
	public function getSectionById(int $sectionId): ?array
	{
		$section = SectionTable::getList([
				'filter' => [
					'=ACTIVE' => 'Y',
					'=ID' => $sectionId
				],
				'select' => [
					'ID',
					'CAL_TYPE',
					'OWNER_ID',
					'NAME'
				]
			]
		)->fetch();

		return $section
			? $section
			: null;
	}
}