<?php

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
		if (!isset($params['WORKFLOW_ID']))
			$params['WORKFLOW_ID'] = '';

		$params['WORKFLOW_ID'] = trim($params['WORKFLOW_ID']);
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
				if (strtolower($key) == 'workflow_id')
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
			$this->workflowId = !empty($this->arParams['WORKFLOW_ID']) ? preg_replace('#[^A-Z0-9\.]#i', '', $this->arParams['WORKFLOW_ID']) : 0;
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
		$iterator = \CUser::GetList($by='id', $order='asc',
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
		$id = $this->getWorkflowId();
		$this->arResult = array(
			'NeedAuth' => $this->isAuthorizationNeeded()? 'Y' : 'N',
			'FatalErrorMessage' => '',
			'ErrorMessage' => ''
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
}