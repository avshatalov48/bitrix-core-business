<?php

$arClasses = array(
	"CBPActivity" => "classes/general/activity.php",
	"CBPActivityCondition" => "classes/general/activitycondition.php",
	"CBPCompositeActivity" => "classes/general/compositeactivity.php",
	"CBPActivityExecutionStatus" => "classes/general/constants.php",
	"CBPActivityExecutionResult" => "classes/general/constants.php",
	"CBPWorkflowStatus" => "classes/general/constants.php",
	"CBPActivityExecutorOperationType" => "classes/general/constants.php",
	"CBPDocumentEventType" => "classes/general/constants.php",
	"CBPCanUserOperateOperation" => "classes/general/constants.php",
	"CBPSetPermissionsMode" => "classes/general/constants.php",
	"CBPTaskStatus" => "classes/general/constants.php",
	"CBPTaskUserStatus" => "classes/general/constants.php",
	"CBPTaskDelegationType" => "classes/general/constants.php",
	"CBPDocument" => "classes/general/document.php",
	"CBPDocumentService" => "classes/general/documentservice.php",
	"CBPArgumentException" => "classes/general/exception.php",
	"CBPArgumentNullException" => "classes/general/exception.php",
	"CBPArgumentOutOfRangeException" => "classes/general/exception.php",
	"CBPArgumentTypeException" => "classes/general/exception.php",
	"CBPInvalidOperationException" => "classes/general/exception.php",
	"CBPNotSupportedException" => "classes/general/exception.php",
	"CBPHelper" => "classes/general/helper.php",
	"CBPHistoryService" => "classes/general/historyservice.php",
	"CBPHistoryResult" => "classes/general/historyservice.php",
	"CBPRuntime" => "classes/general/runtime.php",
	"CBPRuntimeService" => "classes/general/runtimeservice.php",
	"CBPSchedulerService" => "classes/general/schedulerservice.php",
	"CBPStateService" => "classes/general/stateservice.php",
	"CBPTaskService" => "classes/general/taskservice.php",
	"CBPTaskResult" => "classes/general/taskservice.php",
	"CBPTrackingService" => "classes/general/trackingservice.php",
	"CBPTrackingType" => "classes/general/trackingservice.php",
	"CBPVirtualDocument" => "classes/general/virtualdocument.php",
	"CBPWorkflow" => "classes/general/workflow.php",
	"CBPWorkflowPersister" => "classes/general/workflowpersister.php",
	"CBPWorkflowTemplateLoader" => "classes/general/workflowtemplateloader.php",
	"CBPWorkflowTemplateResult" => "classes/general/workflowtemplateloader.php",
	"CBPWorkflowTemplateUser" => "classes/general/workflowtemplateloader.php",
	"CBPWorkflowTemplateValidationException" => "classes/general/workflowtemplateloader.php",
	"CBPViewHelper" => "classes/general/viewhelper.php",
	'CBPTaskChangedStatus' => "classes/general/constants.php",
	'CBPRestActivity' => 'classes/general/restactivity.php',
);
CModule::AddAutoloadClasses("bizproc", $arClasses);

require_once __DIR__.DIRECTORY_SEPARATOR.'compatibility.php';

include_once __DIR__."/classes/general/interface.php";

$trackingServiceClass = \CBPTrackingService::class;
if (
	(
		\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')
		&& time() > mktime(0, 0, 0, 8, 1, 2024)
	)
	|| \Bitrix\Main\Config\Option::get('bizproc', 'tmp_use_restricted_tracking') === 'Y'
)
{
	$trackingServiceClass = \Bitrix\Bizproc\Service\RestrictedTracking::class;
}

\Bitrix\Main\DI\ServiceLocator::getInstance()->addInstanceLazy(
	'bizproc.service.trackingService',
	[
		'className' => $trackingServiceClass,
	]
);
