<?php

namespace Bitrix\Sender\Service;

use Bitrix\Main\Application;
use Bitrix\Sender\ContactListTable;
use Bitrix\Sender\ContactTable;
use Bitrix\Sender\Internals\Dto\UpdateContactDtoCollection;
use Bitrix\Sender\Internals\SqlBatch;

class ContactListUpdateService
{
	/**
	 * Update contact list using DTOs collection
	 *
	 * @param UpdateContactDtoCollection $collection DTOs collection
	 * @param int $listId List ID
	 *
	 * @return void
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 */
	public function updateByCollection(UpdateContactDtoCollection $collection, int $listId): void
	{
		$codesByType = [];
		foreach ($collection->all() as $updateItem)
		{
			$codesByType[$updateItem->typeId][] = $updateItem->code;
		}
		$sqlHelper = Application::getConnection()->getSqlHelper();

		foreach ($codesByType as $typeId => $allCodes)
		{
			$typeId = (int)$typeId;
			$listId = (int)$listId;
			$contactTableName = ContactTable::getTableName();
			$contactListTableName = ContactListTable::getTableName();
			foreach (SqlBatch::divide($allCodes) as $codes)
			{
				$codes = SqlBatch::getInString($codes);

				$fields = '(CONTACT_ID, LIST_ID) ';
				$subSelect = "SELECT ID AS CONTACT_ID, $listId as LIST_ID ";
				$subSelect .= "FROM $contactTableName ";
				$subSelect .= "WHERE TYPE_ID=$typeId AND CODE in ($codes)";
				$sql = $sqlHelper->getInsertIgnore($contactListTableName, $fields, $subSelect);
				Application::getConnection()->query($sql);
			}
		}
	}
}
