<?php

namespace Bitrix\Catalog\v2\Contractor\Provider;

use Bitrix\Main\Result;

/**
 * Interface IProvider
 *
 * @package Bitrix\Catalog\v2\Contractor\Provider
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
interface IProvider
{
	/**
	 * @return string
	 */
	public static function getModuleId(): string;

	/**
	 * @param int $documentId
	 * @return IContractor|null
	 */
	public static function getContractorByDocumentId(int $documentId): ?IContractor;

	// region Documents grid

	/**
	 * @return array
	 */
	public static function getDocumentsGridFilterFields(): array;

	/**
	 * @param string $fieldId
	 * @return bool
	 */
	public static function isDocumentsGridFilterFieldSupported(string $fieldId): bool;

	/**
	 * @param string $fieldId
	 * @return array
	 */
	public static function getDocumentsGridFilterFieldData(string $fieldId): array;

	/**
	 * @param array $filter
	 * @return void
	 */
	public static function setDocumentsGridFilter(array &$filter): void;

	// endregion

	// region Document card

	/**
	 * @return string
	 */
	public static function getEditorFieldType(): string;

	/**
	 * @return array
	 */
	public static function getEditorFieldData(): array;

	/**
	 * @param int $documentId
	 * @return array
	 */
	public static function getEditorEntityData(int $documentId): array;

	/**
	 * @param string $action
	 * @return void
	 */
	public static function processDocumentCardAjaxActions(string $action): void;

	/**
	 * @param int $documentId
	 */
	public static function onAfterDocumentDelete(int $documentId): void;

	/**
	 * @param array $fields
	 * @return Result
	 */
	public static function onBeforeDocumentSave(array $fields): Result;

	/**
	 * @param int $documentId
	 * @param Result $result
	 * @param array $options
	 */
	public static function onAfterDocumentSaveSuccess(int $documentId, Result $result, array $options = []): void;

	/**
	 * @param int|null $documentId
	 * @param Result $result
	 * @param array $options
	 */
	public static function onAfterDocumentSaveFailure(?int $documentId, Result $result, array $options = []): void;

	/**
	 * @param int $documentId
	 * @param array $data
	 * @return void
	 */
	public static function onAfterDocumentSaveSuccessForMobile(int $documentId, array $data): void;

	// endregion
}
