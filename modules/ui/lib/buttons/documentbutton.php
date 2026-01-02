<?php

namespace Bitrix\UI\Buttons;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text;
use Bitrix\Main\Web\Json;

class DocumentButton extends Button
{
	private array $documentButtonConfig = [];

	/**
	 * @return array
	 */
	protected function getDefaultParameters(): array
	{
		return [
			'tag' => Tag::BUTTON,
			'color' => Color::LIGHT_BORDER,
			'dropdown' => true,
			'domId' => $this->getUniqId(),
			'text' => Loc::getMessage('UI_BUTTON_DOCUMENT_TEXT'),
			'dataset' => [
				'toolbar-collapsed-icon' => Icon::LIST,
			],
		];
	}

	protected function buildFromArray($params): void
	{
		if (isset($params['documentButtonConfig']) && is_array($params['documentButtonConfig']))
		{
			$this->setDocumentButtonConfig($params['documentButtonConfig']);
			unset($params['documentButtonConfig']);
		}
		parent::buildFromArray($params);
		if (!empty($params['domId']) && is_string($params['domId']))
		{
			$this->setDomId($params['domId']);
		}
	}

	public function setDocumentButtonConfig(array $config): void
	{
		$this->documentButtonConfig = $config;
	}

	private function getDocumentButtonConfig(): array
	{
		return $this->documentButtonConfig;
	}

	protected function listExtensions(): array
	{
		return [
			'documentpreview',
		];
	}

	protected function renderJavascript(): string
	{
		$id = $this->getDomId();
		$config = $this->getDocumentButtonConfig();
		if ($id === '' || empty($config))
		{
			return '';
		}

		$id = Text\HtmlFilter::encode($id);
		$params = Json::encode($config);

		return
			<<<JS
				if (BX.DocumentGenerator && BX.DocumentGenerator.Button)
				{
					const button = new BX.DocumentGenerator.Button('$id', $params);
					button.init();
				}
				else
				{
					console.warn('BX.DocumentGenerator.Button is not found');
				}
			JS
		;
	}

	public function render($jsInit = true): string
	{
		if (!Loader::includeModule('documentgenerator'))
		{
			return '';
		}

		return parent::render(true);
	}

	private function setDomId(string $id): void
	{
		$this->getAttributeCollection()['id'] = $id;
	}

	private function getDomId(): string
	{
		return (string)($this->getAttributeCollection()['id'] ?? '');
	}
}
