<?php

namespace Bitrix\Sender\Service;

use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Internals\Dto\UpdateContactDtoCollection;
use Bitrix\Sender\Internals\SqlBatch;

final class ContactUpdateService
{
	/**
	 * Update contacts from DTO collection
	 *
	 * @param UpdateContactDtoCollection $collection DTOs collection
	 *
	 * @return void
	 */
	public function updateByCollection(UpdateContactDtoCollection $collection): void
	{
		$onDuplicateUpdateFields = [
			'NAME',
			'BLACKLISTED',
			'DATE_UPDATE',
		];
		foreach (SqlBatch::divide($collection->toArray()) as $list)
		{
			SqlBatch::insert(
				ContactTable::getTableName(),
				$list,
				$onDuplicateUpdateFields,
				ContactTable::getConflictFields(),
			);
		}
	}
}
