<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main;

class BizprocScriptPlacementAjaxController extends Main\Engine\Controller
{
	protected function init()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			throw new Main\SystemException('Module "bizproc" is not installed.');
		}

		parent::init();
	}


	public function createScriptAction($fields)
	{
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateAutomation,
			$user->getId(),
			[$fields['MODULE_ID'], $fields['ENTITY'], $fields['DOCUMENT_TYPE']]
		);

		if (!$canWrite)
		{
			return false;
		}

		return \Bitrix\Bizproc\Automation\Script\Manager::createScript($fields);
	}
}