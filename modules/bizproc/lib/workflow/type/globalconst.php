<?php

namespace Bitrix\Bizproc\Workflow\Type;

class GlobalConst extends GlobalsManager
{
	const CONF_NAME = 'global_const';

	protected static function getTableEntity(): string
	{
		return \Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable::class;
	}

	protected static function getCacheId(): string
	{
		return 'constant';
	}

	public static function getObjectNameForExpressions(): string
	{
		return 'GlobalConst';
	}

	public static function getVisibilityFullNames(array $parameterDocumentType): array
	{
		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		[$moduleId, $entity, $documentType] = \CBPHelper::ParseDocumentId($parameterDocumentType);
		$documentCaption = $documentService->getDocumentTypeCaption($parameterDocumentType);

		$names = [];
		$names['GLOBAL'] = \Bitrix\Main\Localization\Loc::getMessage(
			'BIZPROC_LIB_WF_TYPE_GLOBAL_CONST_VISIBILITY_FULL_GLOBAL'
		);

		switch (mb_strtoupper($moduleId))
		{
			case 'CRM':
				$moduleVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_CONST_VISIBILITY_FULL_MODULE',
					['#MODULE#' => mb_strtoupper($moduleId)]
				);
				$documentVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_CONST_VISIBILITY_FULL_DOCUMENT_SECTION',
					['#SECTION#' => $documentCaption]
				);
				break;
			case 'RPA':
				$moduleVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_CONST_VISIBILITY_FULL_MODULE',
					['#MODULE#' => mb_strtoupper($moduleId)]
				);
				$documentVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_CONST_VISIBILITY_FULL_DOCUMENT_PROCESS',
					['#PROCESS#' => $documentCaption]
				);
				break;
			default:
				$moduleVisibility = '';
				$documentVisibility = '';
		}

		if (!$moduleVisibility)
		{
			return $names;
		}

		$names[mb_strtoupper($moduleId)] = $moduleVisibility;
		$names[mb_strtoupper($moduleId) . '_' . mb_strtoupper($documentType)] = $documentVisibility;

		return $names;
	}

	public static function saveAll(array $all, int $userId = null)
	{
		$diff = array_diff(array_keys(static::getAll()), array_keys($all));

		foreach ($all as $id => $property)
		{
			Entity\GlobalConstTable::upsertByProperty($id, $property, $userId);
		}

		if ($diff)
		{
			foreach ($diff as $toDelete)
			{
				Entity\GlobalConstTable::delete($toDelete);
			}
		}

		//clear cache
		static::clearStaticCache(self::getCacheId());

		return true;
	}
}
