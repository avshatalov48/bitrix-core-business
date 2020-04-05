<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPLockDocumentActivity
	extends CBPActivity
	implements IBPActivityExternalEventListener
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "");
	}

	public function Execute()
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$documentService = $this->workflow->GetService("DocumentService");

		if (!($documentService->LockDocument($documentId, $this->GetWorkflowInstanceId())))
		{
			$this->WriteToTrackingService(GetMessage('BPLDA_SUBSCRIBE_ON_UNLOCK'));
			$this->workflow->AddEventHandler($this->name, $this);
			$documentService->SubscribeOnUnlockDocument($documentId, $this->GetWorkflowInstanceId(), $this->name);
			return CBPActivityExecutionStatus::Executing;
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public function Finalize()
	{
		$documentService = $this->workflow->GetService("DocumentService");
		$documentService->UnlockDocument($this->GetDocumentId(), $this->GetWorkflowInstanceId());
	}

	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			$rootActivity = $this->GetRootActivity();
			$documentId = $rootActivity->GetDocumentId();

			$documentService = $this->workflow->GetService("DocumentService");

			if (!($documentService->LockDocument($documentId, $this->GetWorkflowInstanceId())))
				return;

			$documentService->UnsubscribeOnUnlockDocument($documentId, $this->GetWorkflowInstanceId(), $this->name);
			$this->workflow->RemoveEventHandler($this->name, $this);
			$this->workflow->CloseActivity($this);
		}
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		return true;
	}
}