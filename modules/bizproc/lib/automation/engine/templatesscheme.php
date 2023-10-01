<?php

namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

abstract class TemplatesScheme
{
	public const ERROR_CODE_MISSING_TEMPLATE_ERROR = 'TEMPLATES_SCHEME_ERROR_CODE_TEMPLATE_ERROR';
	public const ERROR_CODE_GETTING_TEMPLATE_ERROR = 'TEMPLATES_SCHEME_ERROR_CODE_TEMPLATE_ERROR';

	/** @var TemplateScope[] */
	private $scheme = [];

	public function isAutomationAvailable(array $complexDocumentType): bool
	{
		return true;
	}

	public function addTemplate(TemplateScope $scope)
	{
		$this->scheme[$scope->getId()] = $scope;
	}

	public function getTemplate(TemplateScope $scope): ?Template
	{
		if ($this->hasTemplate($scope))
		{
			return new Template($scope->getComplexDocumentType(), $scope->getStatusId());
		}

		return null;
	}

	public function toArray(): array
	{
		$scheme = [];
		foreach ($this->scheme as $scopeId => $scope)
		{
			$scheme[$scopeId] = $scope->toArray();
		}

		return $scheme;
	}

	protected function hasTemplate(TemplateScope $scope): bool
	{
		return isset($this->scheme[$scope->getId()]);
	}

	public function createTemplatesTunnel(TemplateScope $srcScope, TemplateScope $dstScope): Result
	{
		$result = new Result();
		if (!$this->hasTemplate($srcScope) || !$this->hasTemplate($dstScope))
		{
			$errorMessage = Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_TEMPLATE_ERROR');
			$result->addError(new Error($errorMessage, self::ERROR_CODE_MISSING_TEMPLATE_ERROR));
		}

		$srcTemplate = $srcScope->getTemplate();
		$dstTemplate = $dstScope->getTemplate();

		if (
			is_null($srcTemplate)
			|| is_null($dstTemplate)
		)
		{
			$errorMessage = Loc::getMessage('BIZPROC_AUTOMATION_SCHEME_TEMPLATE_ERROR');
			$result->addError(new Error($errorMessage, self::ERROR_CODE_GETTING_TEMPLATE_ERROR));
		}
		elseif ($result->isSuccess())
		{
			$result->setData(['templatesTunnel' => new TemplatesTunnel($srcTemplate, $dstTemplate)]);
		}

		return $result;
	}

	abstract public function build(): void;
}
