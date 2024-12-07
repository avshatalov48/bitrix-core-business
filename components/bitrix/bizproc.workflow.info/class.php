<?php

use Bitrix\Bizproc\Api\Request\WorkflowAccessService\CanViewTimelineRequest;
use Bitrix\Bizproc\Api\Service\WorkflowAccessService;
use Bitrix\Bizproc\UI\WorkflowUserView;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Workflow\Task\TaskTable;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if(!\Bitrix\Main\Loader::includeModule('bizproc'))
{
	return;
}

Loc::loadMessages(__FILE__);

class BizprocWorkflowInfo extends \CBitrixComponent
{

	protected $workflowId;

	/**
	 * Prepare WORKFLOW_ID parameter. For compatibility with old component
	 * @param $params
	 * @return mixed
	 */

	public function onPrepareComponentParams($params)
	{
		$params['WORKFLOW_ID'] = trim($params['WORKFLOW_ID'] ?? '');
		if (!$params['WORKFLOW_ID'] && isset($_REQUEST['WORKFLOW_ID']))
		{
			$params['WORKFLOW_ID'] = trim($_REQUEST['WORKFLOW_ID']);
		}
		if (!$params['WORKFLOW_ID'] && isset($_REQUEST['workflow_id']))
		{
			$params['WORKFLOW_ID'] = trim($_REQUEST['workflow_id']);
		}
		if (!$params['WORKFLOW_ID'])
		{
			foreach ($_REQUEST as $key => $value)
			{
				if (mb_strtolower($key) == 'workflow_id')
				{
					$params['WORKFLOW_ID'] = trim($_REQUEST[$key]);
					break;
				}
			}
		}

		$params['POPUP'] = (isset($params['POPUP']) && $params['POPUP'] == 'Y');
		$params['SET_TITLE'] = !(isset($params['SET_TITLE']) && $params['SET_TITLE'] == 'N');

		return $params;
	}

	protected function getWorkflowId()
	{
		if ($this->workflowId === null)
		{
			$this->workflowId =
				!empty($this->arParams['WORKFLOW_ID'])
				? preg_replace('#[^A-Z0-9\.]#i', '', $this->arParams['WORKFLOW_ID'])
				: ''
			;
		}

		return $this->workflowId;
	}

	protected function isAuthorizationNeeded()
	{
		global $USER;
		return !(isset($USER) && is_object($USER) && $USER->IsAuthorized());
	}

	protected function getStartedByPhoto($userId)
	{
		$iterator = \CUser::GetList('id', 'asc',
			array('ID' => $userId),
			array('FIELDS' => array('PERSONAL_PHOTO'))
		);
		$startedUser = $iterator->fetch();
		if ($startedUser)
		{
			return \CFile::ResizeImageGet(
				$startedUser['PERSONAL_PHOTO'],
				array('width' => 58, 'height' => 58),
				\BX_RESIZE_IMAGE_EXACT,
				false
			);
		}

		return null;
	}

	protected function setPageTitle($title)
	{
		global $APPLICATION;
		$APPLICATION->SetTitle($title);
	}

	public function executeComponent()
	{
		if ($this->getTemplateName() === 'page-slider')
		{
			$this->includeComponentTemplate();

			return;
		}

		if ($this->getTemplateName() === 'slider')
		{
			$this->prepareSliderResult();
			$this->includeComponentTemplate(empty($this->arResult['errors']) ? '' : 'error');

			return;
		}

		$id = $this->getWorkflowId();
		$this->arResult = array(
			'NeedAuth' => $this->isAuthorizationNeeded()? 'Y' : 'N',
			'FatalErrorMessage' => '',
			'ErrorMessage' => '',
		);

		if ($id)
		{
			$workflowState = \CBPStateService::getWorkflowState($id);
			if (!$workflowState)
			{
				$this->arResult['FatalErrorMessage'] = Loc::getMessage('BPWFI_WORKFLOW_NOT_FOUND');
			}
			else
			{
				$this->arResult['WorkflowState'] = $workflowState;
				$this->arResult['WorkflowTrack'] = \CBPTrackingService::DumpWorkflow($id);

				if ($workflowState['STARTED_BY'] && $photo = $this->getStartedByPhoto($workflowState['STARTED_BY']))
				{
					$this->arResult['startedByPhotoSrc'] = $photo['src'];
				}

				$runtime = CBPRuntime::GetRuntime();
				$runtime->StartRuntime();

				/**
				 * @var CBPDocumentService $documentService
				 */
				$documentService = $runtime->GetService('DocumentService');

				try
				{
					$this->arResult['DOCUMENT_NAME'] =  $documentService->getDocumentName($workflowState['DOCUMENT_ID']);
					$this->arResult['DOCUMENT_ICON'] =  $documentService->getDocumentIcon($workflowState['DOCUMENT_ID']);
				}
				catch (Main\ArgumentException $e)
				{
					$this->arResult['FatalErrorMessage'] = $e->getMessage();
				}

			}
		}
		else
		{
			$this->arResult['FatalErrorMessage'] = Loc::getMessage('BPWFI_WORKFLOW_NOT_FOUND');
		}

		$this->includeComponentTemplate();

		if ($this->arParams['SET_TITLE'])
		{
			$this->setPageTitle(Loc::getMessage('BPWFI_PAGE_TITLE'));
		}
	}

	private function prepareSliderResult(): void
	{
		$this->arResult['pageTitle'] = $this->arParams['SET_TITLE'] ? Loc::getMessage('BPWFI_PAGE_TITLE') : '';

		$workflowId = $this->getWorkflowId();
		if (!$workflowId)
		{
			$workflowId = $this->getWorkflowIdFromTask();
		}

		if (!$workflowId)
		{
			$this->arResult['errors'] = [Loc::getMessage('BPWFI_WORKFLOW_NOT_FOUND')];

			return;
		}

		$currentUserId = $this->getCurrentUserId();
		$isAdmin = (new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser))->isAdmin();

		$userId = $this->getUserId() ?: $currentUserId;

		if (!$isAdmin)
		{
			$accessService = new WorkflowAccessService();
			$accessRequest = new CanViewTimelineRequest(workflowId: $workflowId, userId: $userId);
			$accessResponse = $accessService->canViewTimeline($accessRequest);

			if (!$accessResponse->isSuccess())
			{
				$this->arResult['errors'] = $accessResponse->getErrorMessages();

				return;
			}

			if ($currentUserId !== $userId && !CBPHelper::checkUserSubordination($currentUserId, $userId))
			{
				$this->arResult['errors'] = [$accessService::getViewAccessDeniedError()->getMessage()];

				return;
			}
		}

		$workflowState = WorkflowStateTable::getById($workflowId)->fetchObject();
		if (!$workflowState)
		{
			$this->arResult['errors'] = [Loc::getMessage('BPWFI_WORKFLOW_NOT_FOUND')];

			return;
		}

		$workflowView = new WorkflowUserView($workflowState, $userId);

		$this->arResult['workflow'] = $workflowView;
		$this->arResult['documentUrl'] = \CBPDocument::getDocumentAdminPage($workflowState->getComplexDocumentId());
		$this->arResult['documentType'] = $this->getDocumentType($workflowState->getComplexDocumentId());

		$this->arResult['isMyTask'] = $currentUserId === $userId;
		$this->arResult['userName'] = $this->getUserFormatName($userId);

		$this->arResult['task'] = $this->extractTask($workflowView);

		$this->arResult['isAdmin'] = $isAdmin;
	}

	private function getCurrentUserId()
	{
		return (int)(\Bitrix\Main\Engine\CurrentUser::get()->getId());
	}

	private function getUserId(): int
	{
		return (int)($this->arParams['USER_ID'] ?? 0);
	}

	private function getTaskId(): int
	{
		return (int)($this->arParams['TASK_ID'] ?? 0);
	}

	private function getWorkflowIdFromTask(): ?string
	{
		$taskId = $this->getTaskId();

		$row = TaskTable::query()
			->where('ID', $taskId)
			->setSelect(['WORKFLOW_ID'])
			->fetch()
		;

		return $row['WORKFLOW_ID'] ?? null;
	}

	private function getUserFormatName(int $userId)
	{
		$format = \CSite::GetNameFormat(false);
		$user = \CUser::GetList(
			'id',
			'asc',
			['ID_EQUAL_EXACT' => $userId],
			[
				'FIELDS' => [
					'TITLE',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'NAME_SHORT',
					'LAST_NAME_SHORT',
					'SECOND_NAME_SHORT',
					'EMAIL',
					'ID',
				],
			]
		)->Fetch();

		return $user ? \CUser::FormatName($format, $user, false, false) : '';
	}

	private function getDocumentType(array $documentId): ?array
	{
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();
		try
		{
			return $documentService->getDocumentType($documentId);
		}
		catch (\Exception $exception)
		{}

		return null;
	}

	private function extractTask(WorkflowUserView $workflowUserView): ?array
	{
		$taskId = $this->getTaskId();
		if ($taskId > 0)
		{
			$task = $workflowUserView->getTaskById($taskId);
			if ($task)
			{
				return $task;
			}
		}

		return $workflowUserView->getTasks()[0] ?? null;
	}
}
