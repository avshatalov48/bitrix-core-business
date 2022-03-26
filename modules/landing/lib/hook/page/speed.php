<?php

namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Assets;
use \Bitrix\Landing\Field;
use \Bitrix\Landing\Help;
use \Bitrix\Landing\Landing;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Speed extends \Bitrix\Landing\Hook\Page
{
	const LAZYLOAD_EXTENSION_NAME = 'landing_lazyload';

	protected $isNeedPublication = true;

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		$helpUrl = Help::getHelpUrl('SPEED');

		return [
			'ASSETS' => new Field\Text('ASSETS', []),
			'USE_LAZY' => new Field\Checkbox(
				'USE_LAZY',
				['title' => Loc::getMessage('LANDING_HOOK_SPEED_USE_LAZY_NEW')]
			),
			'USE_WEBPACK' => new Field\Checkbox(
				'USE_WEBPACK',
				[
					'title' => ($mess = Loc::getMessage('LANDING_HOOK_SPEED_USE_WEBPACK2'))
						? $mess
						: Loc::getMessage('LANDING_HOOK_SPEED_USE_WEBPACK'),
					'help' => $helpUrl
						? '<a href="' . $helpUrl . '" target="_blank">' .
						Loc::getMessage('LANDING_HOOK_SPEED_HELP') .
						'</a>'
						: '',
				]
			),
			'USE_WEBP' => new Field\Checkbox(
				'USE_WEBP',
				['title' => Loc::getMessage('LANDING_HOOK_SPEED_USE_WEBP')]
			),
		];
	}

	/**
	 * Hook title.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_SPEED_TTILE');
	}

	/**
	 * Add data to serialize array
	 * @param $field - name of hook field
	 * @param $data - array of data
	 * @return string
	 */
	public function addData($field, $data)
	{
		if (!is_array($data))
		{
			$data = [$data];
		}
		if (
			$this->fields[$field]
			&& ($hookData = $this->fields[$field]->getValue())
		)
		{
			$mergedData = array_unique(array_merge(unserialize($hookData, ['allowed_classes' => false]), $data));
		}
		else
		{
			$mergedData = $data;
		}

		return serialize($mergedData);
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

		if ($this->isPage())
		{
			return false;
		}

		return true;
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec(): void
	{
		if (Landing::getEditMode())
		{
			$this->disableWebpack();
		}
		else
		{
			$this->execWebpack();
			$this->execLazyLoad();
		}
	}

	protected function disableWebpack(): void
	{
		$assets = Assets\Manager::getInstance();
		$assets->setStandartMode();
	}

	protected function execWebpack(): void
	{
		$assets = Assets\Manager::getInstance();
		if ($this->fields['USE_WEBPACK']->getValue() !== 'N')
		{
			$assets->setWebpackMode();
		}
		else
		{
			$assets->setStandartMode();
		}
	}

	protected function execLazyLoad(): void
	{
		if ($this->fields['USE_LAZY']->getValue() !== 'N')
		{
			$assets = Assets\Manager::getInstance();
			$assets->addAsset(self::LAZYLOAD_EXTENSION_NAME, Assets\Location::LOCATION_BEFORE_ALL);
		}
	}
}
