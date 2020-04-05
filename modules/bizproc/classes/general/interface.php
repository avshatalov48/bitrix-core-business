<?
interface IBPEventActivity
{
	public function Subscribe(IBPActivityExternalEventListener $eventHandler);
	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler);
}

interface IBPEventDrivenActivity
{

}

interface IBPActivityEventListener
{
	public function OnEvent(CBPActivity $sender, $arEventParameters = array());
}

interface IBPActivityExternalEventListener
{
	public function OnExternalEvent($arEventParameters = array());
}

interface IBPRootActivity
{
	public function GetDocumentId();
	public function SetDocumentId($documentId);

	public function GetWorkflowStatus();
	public function SetWorkflowStatus($status);

	public function SetProperties($arProperties = array());

	public function SetVariables($arVariables = array());
	public function SetVariable($name, $value);
	public function GetVariable($name);
	public function IsVariableExists($name);

	public function SetCustomStatusMode();
}

interface IBPWorkflowDocument
{
	/**
	 * Method returns document fields values as array (field_code => value, ...). Must be compatible with GetDocumentFields.
	 *
	 * @param string $documentId - Document id.
	 * @return array - Fields values.
	 */
	static public function GetDocument($documentId);

	/**
	 * Method returns document type fields list.
	 *
	 * @param string $documentType - Document type.
	 * @return array - Fields array(field_code => array("NAME" => field_name, "TYPE" => field_type), ...).
	 */
	static public function GetDocumentFields($documentType);

	/**
	 * Method creates new document with specified fields.
	 *
	 * @param $parentDocumentId - Parent document id.
	 * @param array $arFields - Fields values array(field_code => value, ...). Fields codes must be compatible with codes from GetDocumentFields.
	 * @return int - New document id.
	 */
	static public function CreateDocument($parentDocumentId, $arFields);

	/**
	 * Method updates document fields.
	 *
	 * @param string $documentId - Document id.
	 * @param array $arFields - New fields values array(field_code => value, ...). Fields codes must be compatible with codes from GetDocumentFields.
	 */
	static public function UpdateDocument($documentId, $arFields);

	/**
	 * Method deletes specified document.
	 *
	 * @param string $documentId - Document id.
	 */
	static public function DeleteDocument($documentId);

	/**
	 * Method publishes document.
	 *
	 * @param string $documentId - Document id.
	 */
	static public function PublishDocument($documentId);

	/**
	 * Method unpublishes document.
	 *
	 * @param string $documentId - Document id.
	 */
	static public function UnpublishDocument($documentId);

	/**
	 * Method locks specified document for specified workflow state. A locked document can be changed only by the specified workflow.
	 *
	 * @param string $documentId - Document id.
	 * @param string $workflowId - Workflow id.
	 * @return bool - True on success, false on failure.
	 */
	static public function LockDocument($documentId, $workflowId);

	/**
	 * Method unlocks specified document. On unlock fires events like "Entity_OnUnlockDocument" with document id as first parameter.
	 *
	 * @param string $documentId - Document id.
	 * @param string $workflowId - Workflow id.
	 * @return bool - True on success, false on failure.
	 */
	static public function UnlockDocument($documentId, $workflowId);

	/**
	 * Method checks lock status.
	 *
	 * @param string $documentId - Document id.
	 * @param string $workflowId - Workflow id.
	 * @return bool True if document locked.
	 */
	static public function IsDocumentLocked($documentId, $workflowId);

	/**
	 * Method checks can user operate specified document with specified operation.
	 *
	 * @param int $operation - Operation.
	 * @param int $userId - User id.
	 * @param string|int $documentId - Document id.
	 * @param array $arParameters - Additional parameters.
	 * @return bool
	 */
	static public function CanUserOperateDocument($operation, $userId, $documentId, $arParameters = array());

	/**
	 * Method checks can user operate specified document type with specified operation.
	 *
	 * @param int $operation - Operation.
	 * @param int $userId - User id.
	 * @param string $documentType - Document type.
	 * @param array $arParameters - Additional parameters.
	 * @return bool
	 */
	static public function CanUserOperateDocumentType($operation, $userId, $documentType, $arParameters = array());

	/**
	 * Get document admin page URL.
	 *
	 * @param string|int $documentId - Document id.
	 * @return string - URL.
	 */
	static public function GetDocumentAdminPage($documentId);

	/**
	 * Method returns document information. This information uses in method RecoverDocumentFromHistory.
	 *
	 * @param string $documentId - Document id.
	 * @param $historyIndex - History index.
	 * @return array - Document data.
	 */
	static public function GetDocumentForHistory($documentId, $historyIndex);

	/**
	 * Method recovers specified document from information, provided by method RecoverDocumentFromHistory.
	 *
	 * @param string $documentId - Document id.
	 * @param array $arDocument - Document data.
	 */
	static public function RecoverDocumentFromHistory($documentId, $arDocument);

	static public function GetAllowableOperations($documentType);
	static public function GetAllowableUserGroups($documentType);
	static public function GetUsersFromUserGroup($group, $documentId);
}