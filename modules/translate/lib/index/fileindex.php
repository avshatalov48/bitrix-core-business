<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate\Index;

/**
 * @see \Bitrix\Main\ORM\Objectify\EntityObject
 */
class FileIndex
	extends Index\Internals\EO_FileIndex
{
	/**
	 * Constructs instance by io\file.
	 *
	 * @param Main\IO\File $file Language file.
	 *
	 * @return Index\FileIndex
	 * @throws Main\ArgumentException
	 */
	public static function createByFile(Main\IO\File $file)
	{

		if (
			!$file instanceof Main\IO\File ||
			!$file->isFile() ||
			!$file->isExists() ||
			($file->getExtension() != 'php')
		)
		{
			throw new Main\ArgumentException();
		}

		$file = (new static())
			->setPath($file->getPath());

		return $file;
	}

}
