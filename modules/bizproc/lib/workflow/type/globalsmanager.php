<?php

namespace Bitrix\Bizproc\Workflow\Type;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

abstract class GlobalsManager
{
	protected static $allCache;
	protected static $allSortByVisibilityCache;

	abstract protected static function getTableEntity(): string;

	abstract protected static function getCacheId(): string;

	abstract public static function getObjectNameForExpressions(): string;

	public static function getAll(array $parameterDocumentType = []): array
	{
		// TODO: if the user is an admin then show all globals ?
		return static::getAllAvailable($parameterDocumentType);
	}

	protected static function getAllAvailable(array $parameterDocumentType = []): array
	{
		$cacheId = static::getCacheId();

		if (!isset(static::$allSortByVisibilityCache[$cacheId]))
		{
			$all = static::getAllRows();
			$allSortByVisibility = [];
			foreach ($all as $id => $property)
			{
				$visibility = $property['Visibility'];
				$allSortByVisibility[$visibility][$id] = $property;
			}

			static::$allSortByVisibilityCache[$cacheId] = $allSortByVisibility;
		}

		$global = static::$allSortByVisibilityCache[$cacheId]['GLOBAL'] ?? [];

		if (!$parameterDocumentType)
		{
			return $global;
		}

		try
		{
			[$moduleId, $entity, $documentType] = \CBPHelper::ParseDocumentId($parameterDocumentType);
		}
		catch (\CBPArgumentNullException $e)
		{
			return $global;
		}

		$module = static::$allSortByVisibilityCache[$cacheId][mb_strtoupper($moduleId)] ?? [];
		$document =
			static::$allSortByVisibilityCache[$cacheId][mb_strtoupper($moduleId) . '_' . mb_strtoupper($documentType)]
			?? []
		;

		return array_merge($global, $module, $document);
	}

	protected static function getAllRows(): array
	{
		$cacheId = static::getCacheId();

		if (!isset(static::$allCache[$cacheId]))
		{
			$all = [];
			$table = static::getTableEntity();
			if (method_exists($table, 'getList') && method_exists($table, 'convertToProperty'))
			{
				$rows = $table::getList();

				foreach ($rows as $row)
				{
					$all[$row['ID']] = $table::convertToProperty($row);
				}
			}

			static::$allCache[$cacheId] = $all;
		}

		return static::$allCache[$cacheId];
	}

	public static function upsert($id, $property, int $userId = null): bool
	{
		return static::upsertByProperty($id, $property, $userId)->isSuccess();
	}

	public static function upsertByProperty($id, $property, int $userId = null): Result
	{
		$table = static::getTableEntity();
		if (method_exists($table, 'upsertByProperty'))
		{
			$result = $table::upsertByProperty($id, $property, $userId);

			$cacheId = static::getCacheId();
			static::clearStaticCache($cacheId);

			return $result;
		}

		return (new Result())->addError(
			new Error(Loc::getMessage('BIZPROC_LIB_WF_TYPE_GLOBALS_MANAGER_CAN_NOT_UPSERT'))
		);
	}

	public static function delete($id): bool
	{
		$table = static::getTableEntity();
		if (method_exists($table, 'delete'))
		{
			$result = $table::delete($id);

			$cacheId = static::getCacheId();
			static::clearStaticCache($cacheId);

			return $result->isSuccess();
		}

		return false;
	}

	protected static function clearStaticCache($cacheId)
	{
		static::$allCache[$cacheId] = null;
		static::$allSortByVisibilityCache[$cacheId] = null;
	}

	public static function getById($id)
	{
		$all = static::getAllRows();

		return $all[$id] ?? null;
	}

	public static function getVisibleById($id, $documentType)
	{
		$all = static::getAllAvailable($documentType);

		return $all[$id] ?? null;
	}

	public static function getValue($id)
	{
		$property = is_array($id) ? $id : static::getById($id);

		return $property ? $property['Default'] : null;
	}

	public static function canUserRead(array $documentType, int $userId): bool
	{
		$user = new \CBPWorkflowTemplateUser($userId);
		if ($user->isAdmin())
		{
			return true;
		}

		$canCreateAutomation = \CBPDocument::CanUserOperateDocumentType(
			\CBPCanUserOperateOperation::CreateAutomation,
			$user->getId(),
			$documentType
		);
		if ($canCreateAutomation)
		{
			return true;
		}

		return \CBPDocument::CanUserOperateDocumentType(
			\CBPCanUserOperateOperation::CreateWorkflow,
			$user->getId(),
			$documentType
		);
	}

	public static function canUserUpsert(array $documentType, int $userId): bool
	{
		return static::canUserRead($documentType, $userId);
	}

	public static function canUserDelete(array $documentType, int $userId): bool
	{
		return static::canUserRead($documentType, $userId);
	}

	public static function getAvailableVisibility(array $parameterDocumentType): array
	{
		[$moduleId, $entity, $documentType] = \CBPHelper::ParseDocumentId($parameterDocumentType);
		if (in_array(mb_strtoupper($moduleId), ['CRM', 'RPA']))
		{
			return ['GLOBAL', mb_strtoupper($moduleId), mb_strtoupper($moduleId) . '_' . mb_strtoupper($documentType)];
		}

		return ['GLOBAL'];
	}

	public static function getVisibilityShortNames(array $parameterDocumentType): array
	{
		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		// TODO: if the user is an admin then return all visibility names ?
		[$moduleId, $entity, $documentType] = \CBPHelper::ParseDocumentId($parameterDocumentType);
		$documentCaption = $documentService->getDocumentTypeCaption($parameterDocumentType);

		$names = [];
		$names['GLOBAL'] = Loc::getMessage(
			'BIZPROC_LIB_WF_TYPE_GLOBAL_FIELD_VISIBILITY_SHORT_GLOBAL'
		);

		switch (mb_strtoupper($moduleId))
		{
			case 'CRM':
			case 'RPA':
				$moduleVisibility = mb_strtoupper($moduleId);
				$documentVisibility = $documentCaption;
				break;
			default:
				$moduleVisibility = '';
				$documentVisibility = '';
				break;
		}

		if (!$moduleVisibility)
		{
			return $names;
		}

		$names[mb_strtoupper($moduleId)] = $moduleVisibility;
		$names[mb_strtoupper($moduleId) . '_' . mb_strtoupper($documentType)] = $documentVisibility;

		return $names;
	}

	abstract public static function getVisibilityFullNames(array $parameterDocumentType): array;

	public static function loadLanguageFile(): void
	{
		Loc::loadLanguageFile(__FILE__);
	}
}