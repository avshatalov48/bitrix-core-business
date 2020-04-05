<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSocNetLogActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"LogTitle" => "",
			"EntityType" => "",
			"EntityId" => "",
			"Event" => "",
			"LogText" => ""
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("socialnetwork"))
			CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$documentService = $this->workflow->GetService("DocumentService");
		$document = $documentService->GetDocument($documentId);

		$entityType = $this->EntityType;
		if ($entityType == "user")
			$entityType = SONET_ENTITY_USER;
		elseif ($entityType == "group")
			$entityType = SONET_ENTITY_GROUP;

		$USER_ID = false;
		if ($GLOBALS["USER"]->IsAuthorized())
			$USER_ID = $GLOBALS["USER"]->GetID();
			
		$logID = CSocNetLog::Add(
			array(
				"ENTITY_TYPE" => $entityType,
				"ENTITY_ID" => $this->EntityId,
				"EVENT_ID" => $this->Event,
				"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
				"TITLE_TEMPLATE" => $this->LogTitle,
				"TITLE" => $document["NAME"],
				"MESSAGE" => nl2br($this->LogText),
				"TEXT_MESSAGE" => $this->LogText,
				"URL" => $documentService->GetDocumentAdminPage($documentId),
				"MODULE_ID" => false,
				"CALLBACK_FUNC" => false,
				"USER_ID" => $USER_ID
			),
			false
		);

		if (intval($logID > 0))
			CSocNetLog::Update($logID, array("TMP_ID" => $logID));

		CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);			

		return CBPActivityExecutionStatus::Closed;
	}
}
?>