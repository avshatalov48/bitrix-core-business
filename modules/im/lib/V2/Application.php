<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\V2\Application\Config;
use Bitrix\Main\Web\Json;

class Application
{
	private bool $isDesktop;
	private string $name;
	private Config $config;

	public function __construct(bool $isDesktop)
	{
		$this->isDesktop = $isDesktop;
		$this->name = $isDesktop ? 'messenger' : 'quickAccess';
		$this->initConfig();
	}

	public function getTemplate(): string
	{
		$preparedConfig = Json::encode($this->config);
		$then = $this->getThen();

		return "
			BX.ready(function() {
				BX.Messenger.v2.Application.Launch('{$this->name}', {$preparedConfig})
					.then((application) => {
						{$then}
					});
			});
		";
	}

	protected function getThen(): string
	{
		return $this->isDesktop ? "application.initComponent('body')" : '';
	}

	protected function initConfig(): void
	{
		$this->config = new Config();
		$this->config->setDesktopFlag($this->isDesktop);
	}
}