<?php

namespace Bitrix\Bizproc\Workflow\Type;

class GlobalVar extends GlobalsManager
{
	protected static function getTableEntity(): string
	{
		return \Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable::class;
	}

	protected static function getCacheId(): string
	{
		return 'variable';
	}

	public static function getObjectNameForExpressions(): string
	{
		return 'GlobalVar';
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
			'BIZPROC_LIB_WF_TYPE_GLOBAL_VAR_VISIBILITY_FULL_GLOBAL'
		);

		switch (mb_strtoupper($moduleId))
		{
			case 'RPA':
				$moduleVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_VAR_VISIBILITY_FULL_MODULE',
					['#MODULE#' => mb_strtoupper($moduleId)]
				);
				$documentVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_VAR_VISIBILITY_FULL_DOCUMENT_PROCESS',
					['#PROCESS#' => $documentCaption]
				);
				break;
			case 'CRM':
				$moduleVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_VAR_VISIBILITY_FULL_MODULE',
					['#MODULE#' => mb_strtoupper($moduleId)]
				);
				$documentVisibility = \Bitrix\Main\Localization\Loc::getMessage(
					'BIZPROC_LIB_WF_TYPE_GLOBAL_VAR_VISIBILITY_FULL_DOCUMENT_SECTION',
					['#SECTION#' => $documentCaption]
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

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Exception
	 */
	public static function saveAll(array $all): bool
	{
		$diff = array_diff(array_keys(static::getAll()), array_keys($all));

		foreach ($all as $id => $property)
		{
			if (!isset($property['Changed']) || \CBPHelper::getBool($property['Changed']) === true)
			{
				static::upsert($id, $property);
			}
		}

		if ($diff)
		{
			foreach ($diff as $toDelete)
			{
				static::delete($toDelete);
			}
		}

		static::clearStaticCache(self::getCacheId());

		return true;
	}
}
