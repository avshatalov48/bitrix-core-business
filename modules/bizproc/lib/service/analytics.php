<?php
namespace Bitrix\Bizproc\Service;

use CBPRuntime;

class Analytics extends \CBPRuntimeService
{
	private $logFile;

	public function start(CBPRuntime $runtime = null)
	{
		parent::start($runtime);

		if (defined('ANALYTICS_FILENAME') && is_writable(ANALYTICS_FILENAME))
		{
			$this->logFile = ANALYTICS_FILENAME;
		}
	}

	/**
	 * @return bool
	 */
	public function isEnabled()
	{
		return (bool) $this->logFile;
	}

	public function write(array $documentId, $action, $tag)
	{
		$date = date('Y-m-d H:i:s');
		$host = $_SERVER["HTTP_HOST"];
		$module = 'bizproc:'. $documentId[0];

		$this->writeToFile(
			$this->prepareFileContent($date, $host, $module, $action, $tag, $documentId[1])
		);
	}

	private function prepareFileContent(...$params)
	{
		return implode("\t", $params);
	}

	private function writeToFile($content)
	{
		if (!$this->logFile)
		{
			return false;
		}

		if ($content <> '')
		{
			ignore_user_abort(true);
			if ($fp = @fopen($this->logFile, "ab"))
			{
				if (flock($fp, LOCK_EX))
				{
					@fwrite($fp, $content . PHP_EOL);
					@fflush($fp);
					@flock($fp, LOCK_UN);
					@fclose($fp);
				}
			}
			ignore_user_abort(false);
		}
	}
}