<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main;
use Bitrix\Main\Engine\Response\AjaxJson;

class BizprocWorkflowEditAjaxController extends Main\Engine\Controller
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
			'export' => [
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

	public function exportAction($templateId)
	{
		$params = $this->getUnsignedParameters();
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$user->getId(),
			[$params['MODULE_ID'], $params['ENTITY'], $params['DOCUMENT_TYPE']]
		);

		if (!$canWrite)
		{
			return false;
		}

		$datum = CBPWorkflowTemplateLoader::ExportTemplate($templateId);

		if (!$datum)
		{
			$response = new Main\HttpResponse();
			return $response->setStatus(404);
		}

		$response = new Main\HttpResponse();

		$response->setStatus('200 OK');
		$response->addHeader('Content-Type', 'application/force-download; name="bp-'.$templateId.'.bpt"');
		$response->addHeader('Content-Transfer-Encoding', 'binary');
		$response->addHeader('Content-Length', Main\Text\BinaryString::getLength($datum));
		$response->addHeader('Content-Disposition', "attachment; filename=\"bp-".$templateId.".bpt\"");
		$response->addHeader('Cache-Control', "must-revalidate, post-check=0, pre-check=0");
		$response->addHeader('Expires', "0");
		$response->addHeader('Pragma', "public");

		$response->setContent($datum);

		return $response;
	}

	private function importTemplateAction()
	{

	}

	private function saveUserParamsAction()
	{
		$userParams = is_array($_POST['USER_PARAMS']) ? $_POST['USER_PARAMS'] : [];
		\Bitrix\Bizproc\Activity\Settings::encodeSettings($userParams);
		$serializedUserParams = serialize($userParams);

		$maxLength = 16777215;//pow(2, 24) - 1; //mysql mediumtext column length
		if (Main\Text\BinaryString::getLength($serializedUserParams) > $maxLength)
		{
			return AjaxJson::createError(new Main\ErrorCollection([
				new Main\Error(Main\Localization\Loc::getMessage('BIZPROC_USER_PARAMS_SAVE_ERROR'))
			]));
		}
		$activitySettings = new \Bitrix\Bizproc\Activity\Settings('~bizprocdesigner');
		$activitySettings->save($userParams);

		return true;
	}

	private function saveTemplateAction($templateId)
	{
		$params = $this->getUnsignedParameters();
		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);

		$templateFields = [
			"DOCUMENT_TYPE" => [$params['MODULE_ID'], $params['ENTITY'], $params['DOCUMENT_TYPE']],
			"AUTO_EXECUTE" 	=> (string) $this->getRequest()->getPost("workflowTemplateAutostart"),
			"NAME" 			=> (string) $this->getRequest()->getPost("workflowTemplateName"),
			"DESCRIPTION" 	=> (string) $this->getRequest()->getPost("workflowTemplateDescription"),
			"TEMPLATE" 		=> (array) $this->getRequest()->getPost("arWorkflowTemplate"),
			"PARAMETERS"	=> (array) $this->getRequest()->getPost("arWorkflowParameters"),
			"VARIABLES" 	=> (array) $this->getRequest()->getPost("arWorkflowVariables"),
			"CONSTANTS" 	=> (array) $this->getRequest()->getPost("arWorkflowConstants"),
			"USER_ID"		=> $user->getId(),
			"MODIFIER_USER" => $user,
		];

		$errorMessages = [];
		$errors = [];

		try
		{
			if ($templateId > 0)
			{
				CBPWorkflowTemplateLoader::Update($templateId, $templateFields);
			}
			else
			{
				$templateId = CBPWorkflowTemplateLoader::Add($templateFields);
			}
		}
		catch (Exception $e)
		{
			if (method_exists($e, 'getErrors'))
			{
				$errors = $e->getErrors();
				foreach($errors as $error)
				{
					$errorMessages[] = $error['message'];
				}
			}
			else
			{
				$errorMessages[] = $e->getMessage();
			}
		}

		return ['templateId' => $templateId, 'errors' => $errors, 'messages' => $errorMessages];
	}
}