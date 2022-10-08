<?php

interface IBPEventActivity
{
	public function subscribe(IBPActivityExternalEventListener $eventHandler);
	public function unsubscribe(IBPActivityExternalEventListener $eventHandler);
}

interface IBPEventDrivenActivity
{

}

interface IBPActivityEventListener
{
	public function onEvent(CBPActivity $sender, $arEventParameters = array());
}

interface IBPActivityExternalEventListener
{
	public function onExternalEvent($arEventParameters = array());
}

interface IBPActivityDebugEventListener
{
	public function onDebugEvent(array $eventParameters = []);
}

interface IBPRootActivity
{
	public function getDocumentId();
	public function setDocumentId($documentId);

	public function getWorkflowStatus();
	public function setWorkflowStatus($status);

	public function setProperties($arProperties = array());

	public function setVariables($arVariables = array());
	public function setVariable($name, $value);
	public function getVariable($name);
	public function isVariableExists($name);

	public function setCustomStatusMode();
}

interface IBPWorkflowDocument
{
	/**
	 * Method returns document fields values as array (field_code => value, ...). Must be compatible with GetDocumentFields.
	 *
	 * @param string $documentId - Document id.
	 * @return array - Fields values.
	 */
	public static function getDocument($documentId);

	/**
	 * Method returns document type fields list.
	 *
	 * @param string $documentType - Document type.
	 * @return array - Fields array(field_code => array("NAME" => field_name, "TYPE" => field_type), ...).
	 */
	public static function getDocumentFields($documentType);

	/**
	 * Method creates new document with specified fields.
	 *
	 * @param $parentDocumentId - Parent document id.
	 * @param array $arFields - Fields values array(field_code => value, ...). Fields codes must be compatible with codes from GetDocumentFields.
	 * @return int - New document id.
	 */
	public static function createDocument($parentDocumentId, $arFields);

	/**
	 * Method updates document fields.
	 *
	 * @param string $documentId - Document id.
	 * @param array $arFields - New fields values array(field_code => value, ...). Fields codes must be compatible with codes from GetDocumentFields.
	 */
	public static function updateDocument($documentId, $arFields);

	/**
	 * Method deletes specified document.
	 *
	 * @param string $documentId - Document id.
	 */
	public static function deleteDocument($documentId);

	/**
	 * Method publishes document.
	 *
	 * @param string $documentId - Document id.
	 */
	public static function publishDocument($documentId);

	/**
	 * Method unpublishes document.
	 *
	 * @param string $documentId - Document id.
	 */
	public static function unpublishDocument($documentId);

	/**
	 * Method locks specified document for specified workflow state. A locked document can be changed only by the specified workflow.
	 *
	 * @param string $documentId - Document id.
	 * @param string $workflowId - Workflow id.
	 * @return bool - True on success, false on failure.
	 */
	public static function lockDocument($documentId, $workflowId);

	/**
	 * Method unlocks specified document. On unlock fires events like "Entity_OnUnlockDocument" with document id as first parameter.
	 *
	 * @param string $documentId - Document id.
	 * @param string $workflowId - Workflow id.
	 * @return bool - True on success, false on failure.
	 */
	public static function unlockDocument($documentId, $workflowId);

	/**
	 * Method checks lock status.
	 *
	 * @param string $documentId - Document id.
	 * @param string $workflowId - Workflow id.
	 * @return bool True if document locked.
	 */
	public static function isDocumentLocked($documentId, $workflowId);

	/**
	 * Method checks can user operate specified document with specified operation.
	 *
	 * @param int $operation - Operation.
	 * @param int $userId - User id.
	 * @param string|int $documentId - Document id.
	 * @param array $arParameters - Additional parameters.
	 * @return bool
	 */
	public static function canUserOperateDocument($operation, $userId, $documentId, $arParameters = array());

	/**
	 * Method checks can user operate specified document type with specified operation.
	 *
	 * @param int $operation - Operation.
	 * @param int $userId - User id.
	 * @param string $documentType - Document type.
	 * @param array $arParameters - Additional parameters.
	 * @return bool
	 */
	public static function canUserOperateDocumentType($operation, $userId, $documentType, $arParameters = array());

	/**
	 * Get document admin page URL.
	 *
	 * @param string|int $documentId - Document id.
	 * @return string - URL.
	 */
	public static function getDocumentAdminPage($documentId);

	/**
	 * Method returns document information. This information uses in method RecoverDocumentFromHistory.
	 *
	 * @param string $documentId - Document id.
	 * @param $historyIndex - History index.
	 * @return array - Document data.
	 */
	public static function getDocumentForHistory($documentId, $historyIndex);

	/**
	 * Method recovers specified document from information, provided by method RecoverDocumentFromHistory.
	 *
	 * @param string $documentId - Document id.
	 * @param array $arDocument - Document data.
	 */
	public static function recoverDocumentFromHistory($documentId, $arDocument);

	public static function getAllowableOperations($documentType);
	public static function getAllowableUserGroups($documentType);
	public static function getUsersFromUserGroup($group, $documentId);
}
