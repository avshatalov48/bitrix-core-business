<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\FileInputUtility;
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
		return 'int(18)';
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$size = (int)$userField['SETTINGS']['SIZE'];
		$resultExtensions = [];

		if(is_array($userField['SETTINGS']['EXTENSIONS']))
		{
			$extensions = $userField['SETTINGS']['EXTENSIONS'];
		}
		else
		{
			$extensions = explode(',', $userField['SETTINGS']['EXTENSIONS']);
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

		$targetBlank = ($userField['SETTINGS']['TARGET_BLANK'] === 'N' ? 'N' : 'Y');

		return [
			'SIZE' => ($size <= 1 ? 20 : ($size > 255 ? 225 : $size)),
			'LIST_WIDTH' => (int)$userField['SETTINGS']['LIST_WIDTH'],
			'LIST_HEIGHT' => (int)$userField['SETTINGS']['LIST_HEIGHT'],
			'MAX_SHOW_SIZE' => (int)$userField['SETTINGS']['MAX_SHOW_SIZE'],
			'MAX_ALLOWED_SIZE' => (int)$userField['SETTINGS']['MAX_ALLOWED_SIZE'],
			'EXTENSIONS' => $resultExtensions,
			'TARGET_BLANK' => $targetBlank
		];
	}

	public static function getFilterHtml(array $userField, ?array $additionalParameters): string
	{
		return '&nbsp;';
	}

	public static function checkFields(array $userField, $value): array
	{
		$msg = [];

		if(!is_array($value))
		{
			if($value)
			{
				$fileInfo = \CFile::GetFileArray($value);
				if($fileInfo)
				{
					$value = \CFile::MakeFileArray($fileInfo['SRC']);
				}
			}
		}

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
							'#FIELD_NAME#' => HtmlFilter::encode(
								$userField['EDIT_FORM_LABEL'] <> ''
									? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
							),
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
			//Protect from user manipulation
			if(isset($value['old_id']) && $value['old_id'] > 0)
			{
				if(is_array($userField['VALUE']))
				{
					if(!in_array($value['old_id'], $userField['VALUE']))
					{
						unset($value['old_id']);
					}
				}
				else
				{
					if($userField['VALUE'] != $value['old_id'])
					{
						unset($value['old_id']);
					}
				}
			}

			if($value['del'] && $value['old_id'])
			{
				\CFile::Delete($value['old_id']);
				$value['old_id'] = false;
			}

			if($value['error'])
			{
				return $value['old_id'];
			}
			else
			{
				if($value['old_id'])
				{
					\CFile::Delete($value['old_id']);
				}
				$value['MODULE_ID'] = 'main';
				$id = \CFile::SaveFile($value, 'uf');

				return $id;
			}
		}
		// new mechanism - mail.file.input
		else
		{
			$fileInputUtility = FileInputUtility::instance();
			$controlId = $fileInputUtility->getUserFieldCid($userField);

			if($value > 0)
			{
				$checkResult = $fileInputUtility->checkFiles($controlId, [$value]);

				if(!in_array($value, $checkResult))
				{
					$value = false;
				}
			}

			if($value > 0)
			{
				$delResult = $fileInputUtility->checkDeletedFiles($controlId);
				if(in_array($value, $delResult))
				{
					$value = false;
				}
			}

			return $value;
		}
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
		if(count($value))
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
}