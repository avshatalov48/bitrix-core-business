<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\Main\UserField\File\ManualUploadRegistry;
use Bitrix\Main\UserField\File\UploadedFilesRegistry;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class FileType
 * @package Bitrix\Main\UserField\Types
 */
class FileType extends BaseType
{
	public const
		USER_TYPE_ID = 'file',
		RENDER_COMPONENT = 'bitrix:main.field.file';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_FILE_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_FILE,
		];
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\IntegerField('x'));
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$size = (int)($userField['SETTINGS']['SIZE'] ?? 0);
		$resultExtensions = [];

		$extensions = [];
		if (isset($userField['SETTINGS']['EXTENSIONS']))
		{
			if(is_array($userField['SETTINGS']['EXTENSIONS']))
			{
				$extensions = $userField['SETTINGS']['EXTENSIONS'];
			}
			else
			{
				$extensions = explode(',', $userField['SETTINGS']['EXTENSIONS']);
			}
		}

		foreach($extensions as $key => $extension)
		{
			if($extension === true)
			{
				$extension = trim($key);
			}
			else
			{
				$extension = trim($extension);
			}

			if(!empty($extension))
			{
				$resultExtensions[$extension] = true;
			}
		}

		$targetBlank = (($userField['SETTINGS']['TARGET_BLANK'] ?? 'Y') === 'N' ? 'N' : 'Y');

		return [
			'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			'LIST_WIDTH' => (int)($userField['SETTINGS']['LIST_WIDTH'] ?? 0),
			'LIST_HEIGHT' => (int)($userField['SETTINGS']['LIST_HEIGHT'] ?? 0),
			'MAX_SHOW_SIZE' => (int)($userField['SETTINGS']['MAX_SHOW_SIZE'] ?? 0),
			'MAX_ALLOWED_SIZE' => (int)($userField['SETTINGS']['MAX_ALLOWED_SIZE'] ?? 0),
			'EXTENSIONS' => $resultExtensions,
			'TARGET_BLANK' => $targetBlank
		];
	}

	public static function getFilterHtml(array $userField, ?array $additionalParameters): string
	{
		return '&nbsp;';
	}

	/**
	 * @param array $userField
	 * @param string|array $value
	 * @return array
	 */
	public static function checkFields(array $userField, $value): array
	{
		if(!is_array($value) && $value)
		{
			$fileInfo = \CFile::GetFileArray($value);
			if($fileInfo)
			{
				$value = \CFile::MakeFileArray($fileInfo['SRC']);
			}
		}

		$fieldName = HtmlFilter::encode(
			empty($userField['EDIT_FORM_LABEL'])
				? $userField['FIELD_NAME']
				: $userField['EDIT_FORM_LABEL']
		);

		if(
			is_array($value)
			&& (!isset($value['tmp_name']) && !isset($value['old_id']) && !isset($value['type']))
		)
		{
			return [
				[
					'id' => $userField['FIELD_NAME'],
					'text' => Loc::getMessage('USER_TYPE_FILE_VALUE_IS_MULTIPLE', [
						'#FIELD_NAME#' => $fieldName,
					]),
				],
			];
		}

		$msg = [];

		if(is_array($value))
		{
			if(
				$userField['SETTINGS']['MAX_ALLOWED_SIZE'] > 0
				&&
				$value['size'] > $userField['SETTINGS']['MAX_ALLOWED_SIZE']
			)
			{
				$msg[] = [
					'id' => $userField['FIELD_NAME'],
					'text' => Loc::getMessage('USER_TYPE_FILE_MAX_SIZE_ERROR',
						[
							'#FIELD_NAME#' => $fieldName,
							'#MAX_ALLOWED_SIZE#' => $userField['SETTINGS']['MAX_ALLOWED_SIZE']
						]
					),
				];
			}

			//Extention check
			if(
				is_array($userField['SETTINGS']['EXTENSIONS'])
				&&
				count($userField['SETTINGS']['EXTENSIONS'])
			)
			{
				foreach($userField['SETTINGS']['EXTENSIONS'] as $ext => $tmp_val)
				{
					$userField['SETTINGS']['EXTENSIONS'][$ext] = $ext;
				}
				$error = \CFile::CheckFile(
					$value,
					0,
					false,
					implode(',', $userField['SETTINGS']['EXTENSIONS'])
				);
			}
			else
			{
				$error = '';
			}

			if($error)
			{
				$msg[] = [
					'id' => $userField['FIELD_NAME'],
					'text' => $error,
				];
			}

			//For user without edit php permissions we allow only pictures upload
			global $USER;
			if(!is_object($USER) || !$USER->IsAdmin())
			{
				if(HasScriptExtension($value['name']))
				{
					$msg[] = [
						'id' => $userField['FIELD_NAME'],
						'text' => Loc::getMessage('FILE_BAD_TYPE') . ' (' . $value['name'] . ').'
					];
				}
			}
		}

		return $msg;
	}

	public static function onBeforeSave(array $userField, $value)
	{
		// old mechanism
		if(is_array($value))
		{
			return self::prepareUploadedFileViaOldMechanism($userField, $value);
		}

		if($value > 0)
		{
			$value = (int)$value;

			// new mechanism - mail.file.input
			$isValid = self::validateUploadedFileViaFileInputUtility($userField, $value);
			if ($isValid !== null)
			{
				return $isValid ? $value : false;
			}

			$isValid = self::validateUploadedFileViaCurrentUserFieldValue($userField, $value);
			if ($isValid !== null)
			{
				return $isValid ? $value : false;
			}

			$isValid = self::validateUploadedFileViaManualUploadRegistry($userField, $value);
			if ($isValid !== null)
			{
				return $isValid ? $value : false;
			}
		}

		return false;
	}

	private static function prepareUploadedFileViaOldMechanism(array $userField, array $value): mixed
	{
		$userFieldValues = (is_array($userField['VALUE']) ? $userField['VALUE'] : [$userField['VALUE']]);
		$valueHasOldId = !empty($value['old_id']);

		//Protect from user manipulation
		if($valueHasOldId)
		{
			$value['old_id'] = (is_array($value['old_id']) ? $value['old_id'] : [$value['old_id']]);
			foreach ($value['old_id'] as $key => $oldId)
			{
				if(!in_array($oldId, $userFieldValues))
				{
					unset($value['old_id'][$key]);
				}
			}

			if ($value['del'])
			{
				foreach ($value['old_id'] as $oldId)
				{
					\CFile::Delete($oldId);
				}
				$value['old_id'] = false;
			}
		}

		if($value['error'])
		{
			return (is_array($value['old_id']) ? $value['old_id'][0] : $value['old_id']);
		}

		$value['MODULE_ID'] = 'main';

		if ($valueHasOldId && is_array($value['old_id']))
		{
			foreach ($value['old_id'] as $oldId)
			{
				if (!isset($value['ID']) || !in_array($value['ID'], $value['old_id']))
				{
					\CFile::Delete($oldId);
				}
			}
		}

		if (!empty($value['name']))
		{
			if (
				$valueHasOldId
				&& is_array($value['old_id'])
				&& isset($value['ID'])
				&& in_array($value['ID'], $value['old_id'])
			)
			{
				return $value['ID'];
			}

			return \CFile::SaveFile($value, 'uf');
		}

		return false;
	}

	private static function validateUploadedFileViaFileInputUtility(array $userField, int $value): ?bool
	{
		$fileInputUtility = FileInputUtility::instance();
		if (!$fileInputUtility->isAccessible())
		{
			return null;
		}

		$controlId = $fileInputUtility->getUserFieldCid($userField);

		$delResult = $fileInputUtility->checkDeletedFiles($controlId);
		if (in_array($value, $delResult))
		{
			return false;
		}

		$checkResult = $fileInputUtility->checkFiles($controlId, [$value]);
		if (in_array($value, $checkResult))
		{
			return self::tryMakeFilePersistent($userField, $value);
		}

		return null;
	}

	private static function validateUploadedFileViaCurrentUserFieldValue(array $userField, int $value): ?bool
	{
		if (is_array($userField['VALUE']) && in_array($value, $userField['VALUE']))
		{
			return true;
		}

		if (!is_array($userField['VALUE']) && (int)$userField['VALUE'] === $value)
		{
			return true;
		}

		return null;
	}

	private static function validateUploadedFileViaManualUploadRegistry(array $userField, int $value): ?bool
	{
		$registry = ManualUploadRegistry::getInstance();
		if ($registry->isFileRegistered($userField, $value))
		{
			return true;
		}

		return null;
	}

	public static function onSearchIndex(array $userField): ?string
	{
		static $max_file_size = null;
		$result = '';

		if(is_array($userField['VALUE']))
		{
			$value = $userField['VALUE'];
		}
		else
		{
			$value = [$userField['VALUE']];
		}

		$value = array_filter($value, 'strlen');
		if(!empty($value))
		{
			$value = array_map([static::class, 'getFileContent'], $value);
			$result = implode('\r\n', $value);
		}

		return $result;
	}

	public static function getFileContent($fileId)
	{
		static $maxFileSize = null;

		$file = \CFile::MakeFileArray($fileId);
		if($file && isset($file['tmp_name']))
		{
			if(!isset($maxFileSize))
			{
				$optionInt =  \COption::GetOptionInt(
					'search',
					'max_file_size',
					0
				);
				$maxFileSize = $optionInt * 1024;
			}

			if($maxFileSize > 0 && $file['size'] > $maxFileSize)
			{
				return '';
			}

			$files = false;
			$events = GetModuleEvents(
				'search',
				'OnSearchGetFileContent',
				true
			);
			foreach($events as $event)
			{
				if($files = ExecuteModuleEventEx($event, [$file['tmp_name']]))
				{
					break;
				}
			}

			if(is_array($files))
			{
				return $files['CONTENT'];
			}
		}

		return '';
	}

	public static function getPublicEditMultiple(array $userField, ?array $additionalParameters = []): string
	{
		return parent::getPublicEdit($userField, $additionalParameters);
	}

	public static function canUseArrayValueForSingleField(): bool
	{
		return true;
	}

	private static function tryMakeFilePersistent(array $userField, int $fileId): bool
	{
		$uploaderContextGenerator = (new \Bitrix\Main\UserField\File\UploaderContextGenerator(FileInputUtility::instance(), $userField));
		$controlId = $uploaderContextGenerator->getControlId();

		$uploadedFilesRegistry = UploadedFilesRegistry::getInstance();
		$tempFileToken = $uploadedFilesRegistry->getTokenByFileId($controlId, $fileId);
		if ($tempFileToken) // if token found, assume file was uploaded via \Bitrix\Main\FileUploader\FieldFileUploaderController
		{
			$cid = $uploadedFilesRegistry->getCidByFileId($controlId, $fileId);

			if (!FileInputUtility::instance()->isCidRegistered($cid)) // cid not found, so $fileId cannot be made persistent. This case is not allowed to save due to possible data loss.
			{
				return false;
			}

			(new \Bitrix\Crm\Integration\UI\FileUploader(
				new \Bitrix\Main\FileUploader\FieldFileUploaderController($uploaderContextGenerator->getContextInEditMode($cid))
			))->makePersistentFiles([$tempFileToken]);

			$uploadedFilesRegistry->unregisterFile($controlId, $fileId);
		}

		return true;
	}
}
