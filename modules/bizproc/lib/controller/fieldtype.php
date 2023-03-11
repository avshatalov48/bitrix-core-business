<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Main\Engine\Response\HtmlContent;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc;

class FieldType extends Base
{
	protected function inputAndAccessCheck(array &$documentType, array &$type): void
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
			throw new AccessDeniedException(Loc::getMessage('BIZPROC_ACCESS_DENIED'));
		}
	}

	//TODO: useful?
	private function renderControlOptionsAction(array $documentType, array $type, array $params)
	{
		$this->inputAndAccessCheck($documentType, $type);

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

	public function renderControlAction(array $documentType, array $property, array $params)
	{
		$this->inputAndAccessCheck($documentType, $property);

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
