<?php

namespace Bitrix\Bizproc\Api\Data\WorkflowTemplateService;

use Bitrix\Bizproc\Api\Request\WorkflowTemplateService\SaveTemplateRequest;
use Bitrix\Main\ArgumentException;
use Bitrix\Bizproc\Api\Enum\ErrorMessage;
use Bitrix\Main\Localization\Loc;

final class WorkflowTemplate
{
	private int $templateId = 0;
	private array $fields = [];

	/**
	 * create object from Request
	 * @throws ArgumentException
	 */
	public static function createFromRequest(SaveTemplateRequest $request): self
	{
		$fields = self::normalizeFields($request->fields);

		$template = new self();
		$template->templateId = $request->templateId;
		$template->fields = $fields;

		if ($request->user)
		{
			$template->assignUser($request->user);
		}

		if ($request->parameters)
		{
			$template->defineDocumentType($request->parameters);
		}

		return $template;
	}

	/**
	 * normalize and validate fields
	 * @throws ArgumentException
	 */
	private static function normalizeFields(array $fields): array
	{
		$fields['VARIABLES'] = is_array($fields['VARIABLES'] ?? null) ? $fields['VARIABLES'] : [];
		$fields['CONSTANTS'] = is_array($fields['CONSTANTS'] ?? null) ? $fields['CONSTANTS'] : [];

		self::checkRequiredFields($fields);
		self::validateFieldSize($fields, 'PARAMETERS', \CBPWorkflowTemplateLoader::MAX_PARAMETERS_LENGTH);
		self::validateFieldSize($fields, 'VARIABLES', \CBPWorkflowTemplateLoader::MAX_VARIABLES_LENGTH);
		self::validateFieldSize($fields, 'CONSTANTS', \CBPWorkflowTemplateLoader::MAX_CONSTANTS_LENGTH);

		return $fields;
	}

	/**
	 * @throws ArgumentException
	 */
	private static function checkRequiredFields(array $fields): void
	{
		$fieldsToCheck = [
			'NAME',
			'DESCRIPTION',
			'TEMPLATE',
			'PARAMETERS',
			'VARIABLES',
			'CONSTANTS',
			'TEMPLATE_SETTINGS',
		];
		foreach ($fieldsToCheck as $fieldToCheck)
		{
			if (empty($fields[$fieldToCheck]) && !array_key_exists($fieldToCheck, $fields))
			{
				throw new ArgumentException(ErrorMessage::PARAM_REQUIRED->get(['#NAME#' => $fieldToCheck]));
			}
		}
	}

	/**
	 * @throws ArgumentException
	 */
	private static function validateFieldSize(array $fields, string $key, int $maxSize): void
	{
		if (!empty($fields[$key]) && \CBPWorkflowTemplateLoader::getCompressedFieldLength($fields[$key]) > $maxSize)
		{
			throw new ArgumentException(
				Loc::getMessage("BIZPROC_API_DATA_WORKFLOW_TEMPLATE_${key}_ERROR")
			);
		}
	}

	private function assignUser(\CBPWorkflowTemplateUser $user): void
	{
		if ($user->getId() <= 0)
		{
			throw new ArgumentException(ErrorMessage::INVALID_USER_ID->get());
		}

		$this->fields['USER_ID'] = $user->getId();
		$this->fields['MODIFIER_USER'] = $user;
	}

	private function defineDocumentType(array $parameters): void
	{
		if (!isset($parameters['MODULE_ID'], $parameters['ENTITY'], $parameters['DOCUMENT_TYPE']))
		{
			$errorMsg = ErrorMessage::INVALID_PARAM_ARG->get([
				'#PARAM#' => 'DOCUMENT_TYPE',
				'#VALUE#' => $parameters
			]);
			throw new ArgumentException($errorMsg);
		}

		$this->fields['DOCUMENT_TYPE'] = [
			$parameters['MODULE_ID'],
			$parameters['ENTITY'],
			$parameters['DOCUMENT_TYPE'],
		];
	}

	public function getUserId(): ?int
	{
		return $this->fields['USER_ID'] ?? null;
	}

	public function getDocumentType(): array
	{
		return $this->fields['DOCUMENT_TYPE'] ?? [];
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function getTemplateId(): int
	{
		return $this->templateId;
	}
}
