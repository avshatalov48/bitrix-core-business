<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Diag;

class FileLogger extends Logger
{
	protected $fileName;
	protected $maxSize = 1048576;

	/**
	 * @param string $fileName Absulute path.
	 * @param int|null $maxSize Maximum size of the log file.
	 */
	public function __construct(string $fileName, int $maxSize = null)
	{
		$this->fileName = $fileName;

		if ($maxSize !== null)
		{
			$this->maxSize = $maxSize;
		}
	}

	protected function logMessage(string $level, string $message)
	{
		$current = ignore_user_abort(true);

		if ($fp = fopen($this->fileName, 'ab'))
		{
			if (flock($fp, LOCK_EX))
			{
				// need it for filesize()
				clearstatcache();
				$logSize = filesize($this->fileName);

				if ($this->maxSize > 0 && $logSize > $this->maxSize)
				{
					$this->rotateLog();
					ftruncate($fp, 0);
				}

				fwrite($fp, $message);
				fflush($fp);
				flock($fp, LOCK_UN);
			}
			fclose($fp);
		}

		ignore_user_abort($current);
	}

	protected function rotateLog()
	{
		$historyName = $this->fileName . '.old';

		copy($this->fileName, $historyName);
	}
}
