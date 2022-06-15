<?php

namespace Bitrix\Catalog\Update;

use Bitrix\Catalog\StoreDocumentFileTable;

class DocumentFilesCleanup
{
	public static function execAgent(): string
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$queryResult = $connection->query('
			select doc_files.ID, FILE_ID from b_catalog_store_document_file doc_files
			left outer join b_catalog_store_docs docs
			on docs.ID = doc_files.DOCUMENT_ID
			where docs.ID is null
			limit 50
		');
		$documentFiles = $queryResult->fetchAll();

		if (empty($documentFiles))
		{
			return '';
		}

		$filesToRemove = array_column($documentFiles, 'FILE_ID');
		$entriesToRemove = array_column($documentFiles, 'ID');

		foreach ($filesToRemove as $fileId)
		{
			\CFile::Delete($fileId);
		}

		$entriesForQuery = implode(',', $entriesToRemove);

		$connection->queryExecute(
			'delete from ' . $helper->quote(StoreDocumentFileTable::getTableName())
			. ' where ' . $helper->quote('ID') . ' in (' . $entriesForQuery . ')'
		);

		$isCleanupOver = !(bool)$connection->query('
			select doc_files.ID, FILE_ID from b_catalog_store_document_file doc_files
			left outer join b_catalog_store_docs docs
			on docs.ID = doc_files.DOCUMENT_ID
			where docs.ID is null
			limit 1
		')->fetch();

		if ($isCleanupOver)
		{
			return '';
		}

		return '\Bitrix\Catalog\Update\DocumentFilesCleanup::execAgent();';
	}
}
