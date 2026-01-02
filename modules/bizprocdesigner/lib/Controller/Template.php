<?php

namespace Bitrix\BizprocDesigner\Controller;

use Bitrix\Main;
use Bitrix\Bizproc\Api;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Component\ParameterSigner;

class Template extends Base
{
	protected function init()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			throw new Main\SystemException(Loc::getMessage('BIZPROC_MODULE_NOT_INSTALLED'));
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
			],
			'import' => [
				'+prefilters' => [
					new Main\Engine\ActionFilter\CloseSession(),
				],
			],
		];
	}

	public function exportAction(int $templateId): Main\HttpResponse|false
	{
		$templateService = new Api\Service\WorkflowTemplateService();
		$parameters = (array) $this->getUnsignedParameters();
		$user = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);

		$request = new Api\Request\WorkflowTemplateService\ExportTemplateRequest(
			$templateId,
			$parameters,
			$user,
		);

		$result = $templateService->exportTemplate($request);
		$response = new Main\HttpResponse();

		if (!$result->isSuccess())
		{
			$error = $result->getError();
			if ($error && $error->getCode() === 404)
			{
				return $response->setStatus(404);
			}

			return false;
		}

		$response->setStatus('200 OK');
		$response->addHeader('Content-Type', 'application/force-download; name="bp-' . $templateId . '.bpt"');
		$response->addHeader('Content-Transfer-Encoding', 'binary');
		$response->addHeader('Content-Length', strlen($result->getTemplateData()));
		$response->addHeader('Content-Disposition', "attachment; filename=\"bp-" . $templateId . ".bpt\"");
		$response->addHeader('Cache-Control', "must-revalidate, post-check=0, pre-check=0");
		$response->addHeader('Expires', "0");
		$response->addHeader('Pragma', "public");

		$response->setContent($result->getTemplateData());

		return $response;
	}

	public function importAction(int $templateId): ?int
	{
		$user = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);

		$templateService = new Api\Service\WorkflowTemplateService();

		$isAutomation =
			(int)$this->request->getPost('workflowTemplateAutostart') === \CBPDocumentEventType::Automation;

		if ($templateId > 0 && $isAutomation)
		{
			$workflowTemplateAutostart = \CBPDocumentEventType::Automation;
		}
		else
		{
			$workflowTemplateAutostart = (int) $this->request->getPost('import_template_autostart');
		}

		$parameters = $this->getUnsignedParameters();
		$request = new Api\Request\WorkflowTemplateService\ImportTemplateRequest(
			$templateId,
			(array) $parameters,
			(string) $this->request->getPost('import_template_name'),
			(string) $this->request->getPost('import_template_description'),
			$workflowTemplateAutostart,
			(array) $this->request->getFile('import_template_file'),
			$user,
		);

		$response = $templateService->importTemplate($request);

		if (!$response->isSuccess())
		{
			$this->addErrors($response->getErrors());

			return null;
		}

		return $response->getTemplateId();
	}

	public function saveAction(): array|int
	{
		$json = $this->getRequest()->getJsonList();
		$templateId = (int)$json->get('templateId');
		$fields = (array)$json->get('fields');
		$componentName = (string)$json->get('c');
		$signedParameters = (string)$json->get('signedParameters');
		$user = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);

		$templateService = new Api\Service\WorkflowTemplateService();

		$parameters = ParameterSigner::unsignParameters($componentName, $signedParameters);
		$request = new Api\Request\WorkflowTemplateService\SaveTemplateRequest($templateId, $parameters, $fields, $user);

		$response = $templateService->saveTemplate($request);

		if ($response->isSuccess())
		{
			return $response->getTemplateId();
		}

		$this->addErrors($response->getErrors());

		return ['activityErrors' => $response->getActivityErrors()];
	}

	public function saveDraftAction(): ?int
	{
		$json = $this->getRequest()->getJsonList();
		$templateId = (int)$json->get('templateId');
		$fields = (array)$json->get('fields');
		$componentName = (string)$json->get('c');
		$signedParameters = (string)$json->get('signedParameters');
		$user = new \CBPWorkflowTemplateUser(\CBPWorkflowTemplateUser::CurrentUser);

		$templateService = new Api\Service\WorkflowTemplateService();

		$parameters = ParameterSigner::unsignParameters($componentName, $signedParameters);
		$request = new Api\Request\WorkflowTemplateService\SaveTemplateRequest(
			$templateId,
			$parameters,
			$fields,
			$user
		);

		$response = $templateService->saveTemplateDraft($request);
		if ($response->isSuccess())
		{
			return $response->getTemplateDraftId();
		}

		$this->addErrors($response->getErrors());

		return null;
	}

	public function loadDraftAction(int $draftId): ?array
	{
		$templateService = new Api\Service\WorkflowTemplateService();
		$request = new Api\Request\WorkflowTemplateService\LoadTemplateDraftRequest($draftId);
		$response = $templateService->loadTemplateDraft($request);
		if ($response->isSuccess())
		{
			return $response->getData();
		}

		$this->addErrors($response->getErrors());

		return null;
	}
}
