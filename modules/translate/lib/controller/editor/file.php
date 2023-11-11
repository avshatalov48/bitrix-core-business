<?php
namespace Bitrix\Translate\Controller\Editor;

use Bitrix\Main;
use Bitrix\Translate;


class File
	extends Translate\Controller\Controller
{
	const ACTION_SAVE = 'save';
	const ACTION_SAVE_SOURCE = 'saveSource';
	const ACTION_CLEAN_ETHALON = 'cleanEthalon';
	const ACTION_CANCEL = 'cancel';
	const ACTION_WIPE_EMPTY = 'wipeEmpty';


	/**
	 * Configures actions.
	 *
	 * @return array
	 */
	public function configureActions()
	{
		$configureActions = parent::configureActions();
		$permissionWrite = new Translate\Controller\CheckPermission(Translate\Permission::WRITE);
		$filterHttpMethod = new Main\Engine\ActionFilter\HttpMethod([Main\Engine\ActionFilter\HttpMethod::METHOD_POST]);

		$configureActions[self::ACTION_SAVE] = [
			'class' => Translate\Controller\Editor\SaveFile::class,
			'-prefilters' => [
				Main\Engine\ActionFilter\HttpMethod::class,
			],
			'+prefilters' => [
				$permissionWrite,
				$filterHttpMethod
			],
		];

		$configureActions[self::ACTION_SAVE_SOURCE] = [
			'class' => Translate\Controller\Editor\SaveSource::class,
			'-prefilters' => [
				Main\Engine\ActionFilter\HttpMethod::class,
			],
			'+prefilters' => [
				$permissionWrite,
				new Translate\Controller\CheckPermission(Translate\Permission::SOURCE),
				$filterHttpMethod
			],
		];

		$configureActions[self::ACTION_CLEAN_ETHALON] = [
			'class' => Translate\Controller\Editor\CleanEthalon::class,
			'+prefilters' => [
				$permissionWrite,
			],
		];

		$configureActions[self::ACTION_WIPE_EMPTY] = [
			'class' => Translate\Controller\Editor\WipeEmpty::class,
			'+prefilters' => [
				$permissionWrite,
			],
		];

		$configureActions[self::ACTION_CANCEL] = [
			'+prefilters' => [
				$permissionWrite
			],
		];

		return $configureActions;
	}


	/**
	 * @return array
	 */
	public function cancelAction(): array
	{
		return [
			'STATUS' => Translate\Controller\STATUS_COMPLETED
		];
	}
}
