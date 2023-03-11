<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc;

class Activity extends Base
{
	public function requestAction(array $documentType, string $activity, array $params)
	{
		try
		{
			$documentType = \CBPHelper::ParseDocumentId($documentType);
			$activity = (new Bizproc\Validator(['activity' => $activity]))
				->validateString('activity')
				->getPureValues()['activity'];

			$params = (new Bizproc\Validator($params))
				->validateRequire('lists_document_type')
				->validateString('lists_document_type')
				->validateRequire('form_name')
				->validateString('form_name')
				->validateEnum('public_mode', ['Y', ''])
				->setDefault('public_mode', '')
				->getPureValues();
		}
		catch (\Throwable $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}
		$user = $this->getCurrentUser();

		if (
			!\CBPDocument::CanUserOperateDocumentType(
				\CBPCanUserOperateOperation::CreateWorkflow,
				$user->getId(),
				$documentType
			)
		)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_ACCESS_DENIED')));
			return null;
		}

		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		$arActivityDescription = $runtime->GetActivityDescription($activity);
		if ($arActivityDescription == null)
		{
			$this->addError(new Error("Bad activity type!" . htmlspecialcharsbx($activity)));
			return null;
		}

		$runtime->IncludeActivityFile($activity);

		return \CBPActivity::CallStaticMethod(
			$activity,
			"getAjaxResponse",
			[$params]
		);
	}
}
