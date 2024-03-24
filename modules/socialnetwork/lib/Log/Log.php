<?php

namespace Bitrix\Socialnetwork\Log;

use Bitrix\Main\Loader;
use Exception;

class Log
{
	public const DEFAULT_MARKER = 'DEBUG_TASKS';

	private $marker;
	private $currentPortal = '';
	private $portals = [];

	public function __construct(string $marker = self::DEFAULT_MARKER)
	{
		$this->marker = $marker;
	}

	/**
	 * @param $data
	 * @return $this
	 */
	public function collect($data): self
	{
		try
		{
			if (!Loader::includeModule('intranet'))
			{
				return $this;
			}

			$this->currentPortal = \CIntranetUtils::getHostName();

			$this->checkPortal() && $this->save($data);
		}
		catch (Exception)
		{
			return $this;
		}
		return $this;
	}

	private function checkPortal(): bool
	{
		if (!$this->currentPortal)
		{
			return true;
		}

		if (empty($this->portals))
		{
			return true;
		}

		return in_array($this->currentPortal, $this->portals);
	}

	private function save($data): void
	{
		if (!is_scalar($data))
		{
			$data = var_export($data, true);
		}

		$message = [$this->marker];
		$message[] = $data;
		$message = implode("\n", $message);

		AddMessage2Log($message, 'socialnetwork');
	}
}
