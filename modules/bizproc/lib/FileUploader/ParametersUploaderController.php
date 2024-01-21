<?php

namespace Bitrix\Bizproc\FileUploader;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\UI\FileUploader\Configuration;
use Bitrix\UI\FileUploader\FileOwnershipCollection;
use Bitrix\UI\FileUploader\UploaderController;

Loader::requireModule('ui');

class ParametersUploaderController extends UploaderController
{
	public function __construct(array $options)
	{
		$complexDocumentId = null;
		$complexDocumentType = null;

		if (isset($options['signedDocument']) && is_string($options['signedDocument']))
		{
			$unsignedDocument = \CBPDocument::unSignParameters($options['signedDocument']);
			if (!empty($unsignedDocument) && count($unsignedDocument) === 2)
			{
				try
				{
					$complexDocumentType = \CBPHelper::parseDocumentId($unsignedDocument[0]);
					$complexDocumentId = \CBPHelper::parseDocumentId(
						[$complexDocumentType[0], $complexDocumentType[1], $unsignedDocument[1]]
					);
				}
				catch (\CBPArgumentNullException $e)
				{}
			}
		}

		$options['complexDocumentId'] = $complexDocumentId;
		$options['complexDocumentType'] = $complexDocumentType;
		$options['templateId'] = is_numeric($options['templateId'] ?? null) ? (int)$options['templateId'] : 0;

		parent::__construct($options);
	}

	public function isAvailable(): bool
	{
		return $this->hasRights();
	}

	public function getConfiguration(): Configuration
	{
		return new Configuration();
	}

	public function canUpload()
	{
		return $this->hasRights();
	}

	public function canView(): bool
	{
		return $this->hasRights();
	}

	public function verifyFileOwner(FileOwnershipCollection $files): void
	{}

	public function canRemove(): bool
	{
		return $this->hasRights();
	}

	private function hasRights(): bool
	{
		[
			'complexDocumentId' => $complexDocumentId,
			'complexDocumentType' => $complexDocumentType,
			'templateId' => $templateId
		] = $this->getOptions();

		if ($templateId <= 0 || !is_array($complexDocumentType))
		{
			return false;
		}

		$currentUser = CurrentUser::get();
		$currentUserId = (int)$currentUser->getId();

		if ($currentUserId > 0)
		{
			if ($complexDocumentId)
			{
				return \CBPDocument::canUserOperateDocument(
					\CBPCanUserOperateOperation::StartWorkflow,
					$currentUserId,
					$complexDocumentId,
					[
						'UserGroups' => \CUser::GetUserGroup($currentUserId),
						'DocumentStates' => \CBPDocument::getActiveStates($complexDocumentId),
						'WorkflowTemplateId' => $templateId,
					]
				);
			}

			return \CBPDocument::canUserOperateDocumentType(
				\CBPCanUserOperateOperation::StartWorkflow,
				$currentUserId,
				$complexDocumentType,
				[
					'UserGroups' => \CUser::GetUserGroup($currentUserId),
					'DocumentStates' => \CBPDocument::getDocumentStates($complexDocumentType, $complexDocumentId),
					'WorkflowTemplateId' => $templateId,
				]
			);
		}

		return false;
	}
}
