<?php

namespace Bitrix\MessageService\Providers\Base;

use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\OptionManager;

class DemoManager implements Providers\DemoManager
{
	protected OptionManager $optionManager;

	public function __construct(OptionManager $optionManager)
	{
		$this->optionManager = $optionManager;
	}

	public function isDemo(): bool
	{
		return ($this->optionManager->getOption(self::IS_DEMO) === true);
	}

	public function disableDemo(): DemoManager
	{
		$this->optionManager->setOption(self::IS_DEMO, false);

		return $this;
	}

	public function enableDemo(): DemoManager
	{
		$this->optionManager->setOption(self::IS_DEMO, true);

		return $this;
	}
}