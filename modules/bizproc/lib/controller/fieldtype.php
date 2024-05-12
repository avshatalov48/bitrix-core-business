<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Main\Engine\Response\HtmlContent;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc;

class FieldType extends Base
{
	protected function inputAndAccessCheck(array &$documentType, array &$type): bool
	{
		$operationParameters = [];

		if (isset($documentType[3]))
		{
			$operationParameters['DocumentCategoryId'] = $documentType[3];
		}

		$documentType = \CBPHelper::ParseDocumentId($documentType);
		$type = Bizproc\FieldType::normalizeProperty($type);

		$user = $this->getCurrentUser();

		if (
			!$user->isAdmin()
			&& !\CBPDocument::CanUserOperateDocumentType(
				\CBPCanUserOperateOperation::ViewWorkflow,
				$user->getId(),
				$documentType,
				$operationParameters
			)
		)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_ACCESS_DENIED')));

			return false;
		}

		return true;
	}

	//TODO: useful?
	private function renderControlOptionsAction(array $documentType, array $type, array $params)
	{
		if (!$this->inputAndAccessCheck($documentType, $type))
		{
			return null;
		}

		$params = (new Bizproc\Validator($params))
			->validateRequire('Func')
			->validateEnum('Func', [
				'BPRIASwitchSubTypeControl',
				'BWFVCSwitchSubTypeControl',
				'WFSSwitchSubTypeControlC',
				'WFSSwitchSubTypeControlV',
				'WFSSwitchSubTypeControlP',
			])
			->setDefault('Value', '')
			->getPureValues();

		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		return $documentService->GetFieldInputControlOptions(
			$documentType,
			$type,
			$params['Func'],
			$params['Value'],
		);
	}

	public function renderControlCollectionAction(): ?HtmlContent
	{
		$createInternalError = static fn ($reason) => new Error('', 0, ['reason' => $reason]);

		if (!$this->request->isJson())
		{
			// Should add some error message?
			$this->addError(
				$createInternalError('Wrong request format. Expected json in request body.'),
			);

			return null;
		}

		$documentType = $this->request->getJsonList()->get('documentType');
		$controlsData = $this->request->getJsonList()->get('controlsData');

		if (!is_array($documentType))
		{
			$this->addError(
				$createInternalError('Wrong request format. Expected documentType in request json body.')
			);
		}
		if (!is_array($controlsData))
		{
			$this->addError(
				$createInternalError('Wrong request format. Expected controlsData in request json body.')
			);
		}
		$renderer = new Bizproc\Controller\Response\RenderControlCollectionContent();

		foreach ($controlsData as $data)
		{
			if (
				is_array($data['property'] ?? null)
				&& is_array($data['params'] ?? null)
				&& $this->inputAndAccessCheck($documentType, $data['property'])
			)
			{
				$property = $this->normalizeProperty($data['property']);

				$params = (new Bizproc\Validator($data['params']))
					->validateRequire('Field')
					->validateArray('Field', Bizproc\Validator::TYPE_STRING)
					->setPureValue('Value')
					->setDefault('Value', '')
					->validateRequire('Als')
					->validateNumeric('Als')
					->validateEnum('RenderMode', ['public', 'designer', ''])
					->setDefault('RenderMode', '')
					->getPureValues()
				;

				$renderer->addProperty($documentType, $property, $params);
			}
		}

		return new HtmlContent($renderer);
	}

	public function renderControlAction(array $documentType, array $property, array $params)
	{
		if (!$this->inputAndAccessCheck($documentType, $property))
		{
			return null;
		}

		$params = (new Bizproc\Validator($params))
			->validateRequire('Field')
			->validateArray('Field', Bizproc\Validator::TYPE_STRING)
			->setPureValue('Value')
			->setDefault('Value', '')
			->validateRequire('Als')
			->validateNumeric('Als')
			->validateEnum('RenderMode', ['public', 'designer', ''])
			->setDefault('RenderMode', '')
			->getPureValues();

		$property = $this->normalizeProperty($property);

		return new HtmlContent(new Response\RenderControlContent($documentType, $property, $params));
	}

	private function normalizeProperty(array $property): array
	{
		if (
			isset($property['OptionsSort']) && is_array($property['OptionsSort'])
			&& isset($property['Options'])
			&& is_array($property['Options'])
			&& count($property['OptionsSort']) === count($property['Options'])
		)
		{
			$sortedOptions = [];
			$sortSuccess = true;
			foreach ($property['OptionsSort'] as $optionKey)
			{
				if (!isset($property['Options'][$optionKey]))
				{
					$sortSuccess = false;
					break;
				}
				$sortedOptions[$optionKey] = $property['Options'][$optionKey];
			}
			if ($sortSuccess)
			{
				$property['Options'] = $sortedOptions;
			}
		}

		return $property;
	}
}
