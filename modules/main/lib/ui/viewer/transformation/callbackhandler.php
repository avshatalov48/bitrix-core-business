<?php

namespace Bitrix\Main\UI\Viewer\Transformation;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Viewer\FilePreviewTable;
use Bitrix\Main\Web\MimeType;
use Bitrix\Transformer;

if (Loader::includeModule('transformer'))
{
	final class CallbackHandler implements Transformer\InterfaceCallback
	{
		/**
		 * Function to process results after transformation.
		 *
		 * @param int $status Status of the command.
		 * @param string $command Name of the command.
		 * @param array $params Input parameters of the command.
		 * @param array $result Result of the command from controller
		 *      Here keys are identifiers to result information. If result is file it will be in 'files' array.
		 *      'files' - array of the files, where key is extension, and value is absolute path to the result file.
		 *
		 * This method should return true on success or string on error.
		 *
		 * @return bool|string
		 * @throws \Bitrix\Main\ArgumentException
		 * @throws \Bitrix\Main\ObjectPropertyException
		 * @throws \Bitrix\Main\SystemException
		 */
		public static function call($status, $command, $params, $result = array())
		{
			if (isset($params['fileId']) && $params['fileId'] > 0)
			{
				Transformer\FileTransformer::clearInfoCache($params['fileId']);
			}
			if ($status == Transformer\Command::STATUS_ERROR && isset($params['id']))
			{
				return true;
			}

			if (!isset($params['id']) || !isset($params['fileId']) || !isset($result['files']))
			{
				return 'wrong parameters';
			}

			if ($status != Transformer\Command::STATUS_UPLOAD)
			{
				return 'wrong command status';
			}

			$previewLogId = $previewId = $previewImageId = 0;
			$previewLog = FilePreviewTable::getList([
				'select' => ['ID'],
				'filter' => ['FILE_ID' => $params['fileId']],
				'limit' => 1,
			])->fetch();

			if (isset($previewLog['ID']))
			{
				$previewLogId = $previewLog['ID'];
			}

			foreach ($result['files'] as $ext => $filePath)
			{
				if ($ext === 'jpg')
				{
					$previewImageId = self::saveFile($filePath, 'image/jpeg');
					if ($previewImageId)
					{
						if ($previewLogId)
						{
							FilePreviewTable::update($previewLogId, [
								'PREVIEW_IMAGE_ID' => $previewImageId,
							]);
						}
						else
						{
							$resultAdd = FilePreviewTable::add([
								'FILE_ID' => $params['fileId'],
								'PREVIEW_IMAGE_ID' => $previewImageId,
							]);

							if ($resultAdd->getId())
							{
								$previewLogId = $resultAdd->getId();
							}
						}
					}
				}
				else
				{
					$previewId = self::saveFile($filePath, MimeType::getByFileExtension($ext));
					if ($previewLogId)
					{
						FilePreviewTable::update($previewLogId, [
							'PREVIEW_ID' => $previewId,
						]);
					}
					else
					{
						FilePreviewTable::add([
							'FILE_ID' => $params['fileId'],
							'PREVIEW_ID' => $previewId,
						]);
					}

					self::sendNotifyAboutTransformation($params['fileId']);
				}
			}

			(new Event('main', 'onFileTransformationComplete', [
				'fileId' => $params['fileId'],
			]))->send();

			return true;
		}

		protected static function sendNotifyAboutTransformation($fileId)
		{
			if (Loader::includeModule('pull'))
			{
				\CPullWatch::addToStack(
					TransformerManager::getPullTag($fileId),
					[
						'module_id' => 'main',
						'command' => 'transformationComplete',
						'params' => [
							'fileId' => $fileId,
						],
					]
				);
			}
		}

		protected static function saveFile($file, $type)
		{
			$fileArray = \CFile::makeFileArray($file, $type);
			$fileArray['MODULE_ID'] = 'main';
			$fileId = \CFile::saveFile($fileArray, 'main_preview');
			if ($fileId)
			{
				return $fileId;
			}

			return false;
		}

		public static function existSavedFile($fileId)
		{
			$previewRow = FilePreviewTable::getList([
				'select' => ['ID', 'PID' => 'PREVIEW.ID'],
				'filter' => [
					'=FILE_ID' => $fileId,
				],
				'limit' => 1,
			])->fetch();

			return !empty($previewRow['PID']);
		}
	}
}