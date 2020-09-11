<?php
namespace Bitrix\Landing\Note\Source;

abstract class Entity
{
	/**
	 * Length for landing title.
	 */
	const TITLE_LENGTH = 30;

	/**
	 * Returns Disk files for module entity.
	 * @param int $sourceId Source id (post id).
	 * @param string $entityType Entity type.
	 * @param string $module Module id.
	 * @return array
	 */
	protected static function getDiskFiles(int $sourceId, string $entityType, string $module): array
	{
		$files = [];

		if (\Bitrix\Main\Loader::includeModule('disk'))
		{
			$userId = \Bitrix\Landing\Manager::getUserId();
			$filePrefix = \Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX;
			$attachedModels = \Bitrix\Disk\AttachedObject::getModelList([
				'filter' => [
					'=ENTITY_ID' => $sourceId,
					'=ENTITY_TYPE' => $entityType,
					'=MODULE_ID' => $module
				]
			]);
			foreach ($attachedModels as $attachedModel)
			{
				if (!$attachedModel->canRead($userId))
				{
					continue;
				}
				$item = [
					'id' => $attachedModel->getId(),
					'object_id' => $attachedModel->getObjectId(),
					'file_id' => $attachedModel->getFileId(),
					'file_name' => $attachedModel->getName(),
					'prefix' => ''
				];
				$files[$attachedModel->getId()] = $item;
				$item['prefix'] = $filePrefix;
				$files[$filePrefix . $attachedModel->getObjectId()] = $item;
			}
		}

		return $files;
	}

	/**
	 * Returns prepared data for landing by entity (post) id.
	 * @param int $sourceId Source id.
	 * @return array|null
	 */
	abstract public static function getData(int $sourceId): ?array;
}