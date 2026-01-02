<?php

namespace Bitrix\Im\V2;

use Bitrix\Im\V2\Application\Config;
use Bitrix\Main\Web\Json;

class Application
{
	public const MESSENGER_APP_NAME = 'messenger';
	public const QUICK_ACCESS_APP_NAME = 'quickAccess';

	private Config $config;

	public function __construct(?Config $config = null)
	{
		$this->config = $config ?? new Config();
	}

	public function getTemplate(string $appName): string
	{
		$preparedConfig = Json::encode($this->config);
		$then = $this->getThen($appName);

		return "
			BX.ready(function() {
				BX.Messenger.v2.Application.Launch('{$appName}', {$preparedConfig})
					.then((application) => {
						{$then}
					});
			});
		";
	}

	public function getConfig(): Config
	{
		return $this->config;
	}

	public function isAirDesignEnabled(): bool
	{
		return (
			\Bitrix\Main\Loader::includeModule('intranet')
			&& class_exists(\Bitrix\Intranet\Integration\Templates\Air\AirTemplate::class)
			&& method_exists(\Bitrix\Intranet\Integration\Templates\Air\AirTemplate::class, 'isEnabled')
			&& \Bitrix\Intranet\Integration\Templates\Air\AirTemplate::isEnabled()
		);
	}

	public function shouldHideQuickAccess(): bool
	{
		return defined('BX_IM_FULLSCREEN') && BX_IM_FULLSCREEN;
	}

	protected function getThen(string $appName): string
	{
		return $appName === self::MESSENGER_APP_NAME ? "application.initComponent('body')" : '';
	}
}
