<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\ArgumentException;

class NativeFileSessionHandler extends \SessionHandler //implements \SessionUpdateTimestampHandlerInterface
{
	public function __construct(array $options)
	{
		$savePath = $options['savePath'] ?? null;
		if ($savePath === null)
		{
			$savePath = ini_get('session.save_path');
		}

		$baseDir = $savePath;
		if ($count = substr_count($savePath, ';'))
		{
			if ($count > 2)
			{
				throw new ArgumentException('Invalid format for savePath', 'savePath');
			}
			$baseDir = ltrim(strrchr($savePath, ';'), ';');
		}

		if ($baseDir && !is_dir($baseDir) && !@mkdir($baseDir, 0777, true) && !is_dir($baseDir))
		{
//			throw new SystemException("Native file session handler was not able to create directory \"{$baseDir}\".");
		}

		ini_set('session.save_handler', 'files');
		ini_set('session.save_path', $savePath);
	}
}
