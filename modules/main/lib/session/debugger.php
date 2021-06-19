<?php

namespace Bitrix\Main\Session;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Diag\Helper;
use Bitrix\Main\EventManager;

final class Debugger
{
	public const TO_FILE   = 0b001;
	public const TO_HEADER = 0b010;
	public const TO_ALL    = self::TO_FILE | self::TO_HEADER;

	protected $mode = 0;
	/** @var array */
	private $config = [];

	public function __construct(int $mode = 0)
	{
		$this->setMode($mode);

		EventManager::getInstance()->addEventHandlerCompatible('main', 'OnPageStart', function(){
			$this->logConfiguration($this->config);
		});
	}

	public function setMode(int $mode): void
	{
		$this->mode = $mode;
	}

	public function logConfiguration(array $config): void
	{
		$mode = $config['mode'] ?? 'unknown';
		$type = $config['handlers']['general']['type'] ?? 'unknown';

		$this->addHeader('Conf', "{$mode}:{$type}");
	}

	public function detectFirstUsage(): void
	{
		$firstUsage = null;
		$traceItems = Helper::getBackTrace(10, DEBUG_BACKTRACE_IGNORE_ARGS, 4);
		foreach ($traceItems as $item)
		{
			if (!empty($item['class']) && strpos($item['file'], 'lib/session/'))
			{
				continue;
			}

			$firstUsage = "{$item['file']}:{$item['line']}";
			break;
		}

		if ($firstUsage)
		{
			$this->addHeader("Usage", $firstUsage);
		}
	}

	public function logToFile($text)
	{
		if ($this->mode & self::TO_FILE)
		{
			$requestUri = Application::getInstance()->getContext()->getServer()->getRequestUri();
			AddMessage2Log($text . ' ' . $requestUri, 'main', 20);
		}
	}

	protected function addHeader(string $category, $value): void
	{
		if ($this->mode & self::TO_HEADER)
		{
			$response = Context::getCurrent()->getResponse();
			$response->addHeader("X-Session-{$category}", $value);
		}
	}

	public function storeConfig(array $sessionConfig): void
	{
		$this->config = $sessionConfig;
	}
}