<?php

namespace Bitrix\Rest\Configuration\DataProvider\IO;

use Bitrix\Main\SystemException;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Rest\Configuration\DataProvider\ProviderBase;

class Base extends ProviderBase
{
	/**
	 * Base constructor.
	 *
	 * @param array $setting
	 */
	public function __construct(array $setting)
	{
		parent::__construct($setting);
	}

	/**
	 * @throws SystemException
	 */
	public function addContent(string $code, $content, $type = false): bool
	{
		throw new SystemException(
			'This method isn\'t supported',
			'ERROR_METHOD'
		);
	}

	/**
	 * Returns content from file
	 *
	 * @param string $path
	 * @param int $step
	 *
	 * @return array|null
	 */
	public function get(string $path, int $step): ?array
	{
		$result = null;
		if ($path)
		{
			$fileInfo = \CFile::MakeFileArray($path);
			if (is_array($fileInfo) && $fileInfo['tmp_name'] && File::isFileExists($fileInfo['tmp_name']))
			{
				preg_match( "/\/([a-zA-Z0-9_-]+)\.[a-zA-Z0-9_-]+$/", $path, $matches);
				$name = $matches[1] ?? null;

				$file = new File($fileInfo['tmp_name']);
				try
				{
					$result = [
						'DATA' => $file->getContents(),
						'FILE_NAME' => $name,
					];
				}
				catch (FileNotFoundException $exception)
				{
					$result = null;
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $files
	 *
	 * @throws SystemException
	 */
	public function addFiles(array $files): array
	{
		throw new SystemException(
			'This method isn\'t supported',
			'ERROR_METHOD'
		);
	}
}
