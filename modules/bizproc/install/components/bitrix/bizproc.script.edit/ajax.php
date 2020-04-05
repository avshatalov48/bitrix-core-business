<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main;
use Bitrix\Main\Engine\Response\AjaxJson;

class BizprocScriptEditAjaxController extends Main\Engine\Controller
{
	protected function init()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			throw new Main\SystemException('Module "bizproc" is not installed.');
		}

		parent::init();
	}

	public function configureActions()
	{
		return [
			'exportScript' => [
				'+prefilters' => [
					new Main\Engine\ActionFilter\CloseSession(),
				],
				'-prefilters' => [
					Main\Engine\ActionFilter\Csrf::class,
				],
			],
			//'import' => [
			//	'+prefilters' => [
			//		new Main\Engine\ActionFilter\CloseSession(),
			//	],
			//],
		];
	}

	public function exportScriptAction()
	{
		$params = $this->getUnsignedParameters();

		$tpl = \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable::getById($params['SCRIPT_ID'])
			->fetchObject();
		if (!$tpl)
		{
			return false;
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$user->getId(),
			[$tpl['MODULE_ID'], $tpl['ENTITY'], $tpl['DOCUMENT_TYPE']]
		);

		if (!$canWrite)
		{
			return false;
		}

		$packer = new \Bitrix\Bizproc\Workflow\Template\Packer\RoboPackage();
		$datum = $packer->pack($tpl)->getPackage();

		$response = new Main\HttpResponse();

		$response->setStatus('200 OK');
		$response->addHeader('Content-Type', 'application/force-download; name="robots-'.$params['SCRIPT_ID'].'.bpr"');
		$response->addHeader('Content-Transfer-Encoding', 'binary');
		$response->addHeader('Content-Length', Main\Text\BinaryString::getLength($datum));
		$response->addHeader('Content-Disposition', "attachment; filename=\"robots-".$params['SCRIPT_ID'].".bpr\"");
		$response->addHeader('Cache-Control', "must-revalidate, post-check=0, pre-check=0");
		$response->addHeader('Expires', "0");
		$response->addHeader('Pragma', "public");

		$response->setContent($datum);

		return $response;
	}

	public function deleteScriptAction()
	{
		$params = $this->getUnsignedParameters();
		$tpl = \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable::getById($params['SCRIPT_ID'])
			->fetchObject();
		if (!$tpl)
		{
			return false;
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$user->getId(),
			[$tpl['MODULE_ID'], $tpl['ENTITY'], $tpl['DOCUMENT_TYPE']]
		);

		if (!$canWrite)
		{
			return false;
		}

		\CBPWorkflowTemplateLoader::GetLoader()->DeleteTemplate($tpl['ID']);

		return true;
	}

	private function importScriptAction()
	{
		//TODO
	}
}