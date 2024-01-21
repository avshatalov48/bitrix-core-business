<?php

namespace Bitrix\Socialnetwork\Controller\Livefeed\Spaces;

use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use \Bitrix\Socialnetwork\Space\Toolbar;

class Composition extends Controller
{
	private int $userId;

	/**
	 * @throws BinderArgumentException
	 */
	public function getAutoWiredParameters(): array
	{
		return [
			new ExactParameter(
				Toolbar\Composition::class,
				'composition',
				fn ($className, $composition): Toolbar\Composition => new $className($this->userId, $composition),
			),
		];
	}

	/**
	 * @restMethod socialnetwork.api.livefeed.spaces.composition.setSettings
	 */
	public function setSettingsAction(Toolbar\Composition $composition, array $settings = []): ?array
	{
		$result = $composition->setSettings($settings);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return [
			'settings' => $composition->getSettings(false),
		];
	}

	/**
	 * @restMethod socialnetwork.api.livefeed.spaces.composition.getSettings
	 */
	public function getSettingsAction(Toolbar\Composition $composition): ?array
	{
		return [
			'settings' => $composition->getSettings(false),
		];
	}

	protected function init(): void
	{
		parent::init();
		$this->userId = CurrentUser::get()->getId();
	}
}