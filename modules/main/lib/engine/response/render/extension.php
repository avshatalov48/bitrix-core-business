<?php

namespace Bitrix\Main\Engine\Response\Render;

use Bitrix\Main\Engine\Response\Render\Exception\InvalidConfigExtensionException;
use Bitrix\Main\Web\Json;
use CUtil;

final class Extension extends Base
{
	public function __construct(
		private string $extension,
		private array $params = [],
		bool $withSiteTemplate = true,
	)
	{
		parent::__construct($withSiteTemplate);
	}

	protected function renderContent(): void
	{
		$html = null;
		$controllerEntryPoint = $this->getControllerEntryPoint();

		if ($controllerEntryPoint)
		{
			$containerId = uniqid('render_container_');
			$selector = CUtil::JSEscape('#' . $containerId);

			$jsonParams = Json::encode($this->params);

			$html = join('', [
				"<div id='{$containerId}'></div>",
				"<script>BX.ready(() => { {$controllerEntryPoint}('{$selector}', {$jsonParams}) });</script>",
			]);
		}
		else
		{
			throw new InvalidConfigExtensionException(
				$this->extension,
				'`controllerEntrypoint` is not defined in extension config',
			);
		}

		\Bitrix\Main\UI\Extension::load($this->extension);

		echo $html;
	}

	private function getControllerEntryPoint(): ?string
	{
		$config = \Bitrix\Main\UI\Extension::getConfig($this->extension);
		if (isset($config['controllerEntrypoint']))
		{
			if (!is_string($config['controllerEntrypoint']))
			{
				throw new InvalidConfigExtensionException(
					$this->extension,
					'`controllerEntrypoint` in config must be a string with JS function name',
				);
			}

			return $config['controllerEntrypoint'];
		}

		return null;
	}
}
