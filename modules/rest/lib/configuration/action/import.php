<?php

namespace Bitrix\Rest\Configuration\Action;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Configuration\Notification;
use Bitrix\Rest\Configuration\Setting;
use Bitrix\Rest\Configuration\Helper;
use Bitrix\Rest\Configuration\Controller;
use Bitrix\Rest\Configuration\Manifest;
use Bitrix\Rest\Configuration\Structure;
use Bitrix\Rest\Configuration\DataProvider;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\AppLogTable;
use Bitrix\Main\IO\File;

/**
 * Class Import
 * @package Bitrix\Rest\Configuration\Action
 */
class Import extends Base
{
	public const ACTION = 'import';
	public const ERROR_MANIFEST_IS_NOT_AVAILABLE = 'ERROR_MANIFEST_IS_NOT_AVAILABLE';

	private const STEP_INIT_BACKGROUND = 'init_background';
	private const STEP_START = 'start';
	private const STEP_MANIFEST = 'manifest';
	private const STEP_CLEAN = 'clean';
	private const STEP_LOAD = 'load';
	private const STEP_FINISH = 'finish';

	private const STEPS_ORDER = [
		self::STEP_START,
		self::STEP_INIT_BACKGROUND,
		self::STEP_MANIFEST,
		self::STEP_CLEAN,
		self::STEP_LOAD,
		self::STEP_FINISH,
	];

	private const COUNT_ADD_FILES_BY_STEP = 200;

	private $manifestCode;

	/**
	 * Sets uses manifest
	 * @param $code
	 *
	 * @return bool
	 */
	public function setManifestCode($code): bool
	{
		$this->manifestCode = $code;

		return true;
	}

	/**
	 * Return uses manifest
	 * @return string
	 */
	public function getManifestCode()
	{
		return $this->manifestCode ?? '';
	}

	/**
	 * Runs action step.
	 *
	 * @return bool
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function run(): bool
	{
		$result = false;
		$data = $this->getStructureData();

		if ($this->check($data))
		{
			$this->setStatus(self::STATUS_PROCESS);
			$actionInfo = $this->getSetting()->get(Setting::SETTING_ACTION_INFO);
			if (!$actionInfo)
			{
				$actionInfo = [
					'setting' => [],
					'run' => self::STEP_START,
					'section' => [],
					'currentSection' => 0,
					'step' => 0,
				];
			}

			$this->setManifestCode($data['MANIFEST']['CODE']);
			switch ($actionInfo['run'])
			{
				case self::STEP_START:
					$actionInfo['setting'] = $this->doStart();
					$actionInfo['section'] = $actionInfo['setting']['section'];
					break;
				case self::STEP_INIT_BACKGROUND:
					$actionInfo['setting'] = $this->doInitBackground($actionInfo, $data);
					break;
				case self::STEP_MANIFEST:
					$actionInfo['setting'] = $this->doInitManifest(
						$actionInfo['setting']['next'],
						$actionInfo['step'],
						Helper::MODE_IMPORT
					);
					break;
				case self::STEP_CLEAN:
					$manifest = Manifest::get($this->getManifestCode());
					if ($data['MANIFEST']['SKIP_CLEARING'] === 'Y' || $manifest['SKIP_CLEARING'] === 'Y')
					{
						$actionInfo['setting']['finish'] = true;
					}
					else
					{
						$actionInfo['setting'] = $this->doClean(
							$actionInfo['section'][$actionInfo['currentSection']],
							$actionInfo['section']['next'],
							(int) $actionInfo['step']
						);
					}
					break;
				case self::STEP_LOAD:
					$actionInfo['setting']['result'] = true;
					$type = $actionInfo['section'][$actionInfo['currentSection']] ?? false;
					if ($type && isset($data[self::PROPERTY_STRUCTURE][$type][$actionInfo['step']]))
					{
						$path = $data[self::PROPERTY_STRUCTURE][$type][$actionInfo['step']];
						$http = DataProvider\Controller::getInstance()->get(DataProvider\Controller::CODE_IO);
						$content = $http->getContent($path, $actionInfo['step']);
						if ($content['ERROR_CODE'])
						{
							$this->getNotificationInstance()->add(
								Loc::getMessage(
									'REST_CONFIGURATION_IMPORT_ERROR_CONTENT',
									[
										'#STEP#' => $actionInfo['step'],
									]
								),
								implode(
									'_',
									[
										$content['ERROR_CODE'],
										$actionInfo['section'][$actionInfo['currentSection']],
										$actionInfo['step'],
									]
								),
								Notification::TYPE_EXCEPTION
							);
						}
						if ($content['COUNT'] === 0)
						{
							$content['COUNT'] = count($data[self::PROPERTY_STRUCTURE][$type]);
						}

						if ($content['SANITIZE'])
						{
							$this->getNotificationInstance()->add(
								Loc::getMessage(
									'REST_CONFIGURATION_IMPORT_ERROR_SANITIZE_SHORT',
									[
										'#STEP#' => $actionInfo['step']
									]
								),
								implode(
									'_',
									[
										'SANITIZE',
										$actionInfo['currentSection'],
										$actionInfo['step'],
									]
								),
								Notification::TYPE_NOTICE
							);
						}

						if (!is_null($content['DATA']))
						{
							$actionInfo['setting'] = $this->doLoad(
								$actionInfo['setting']['step'],
								$actionInfo['section'][$actionInfo['currentSection']],
								$content
							);
						}
					}
					$actionInfo['setting']['next'] = !$actionInfo['setting']['result'];

					break;
				case self::STEP_FINISH:
					$actionInfo['setting'] = $this->doFinish();
					break;
				default:
					$this->setStatus(self::STATUS_FINISH);
					$this->unregister();
					break;
			}

			$exception = $this->getNotificationInstance()->list(
				[
					'type' => Notification::TYPE_EXCEPTION,
				]
			);
			if (!empty($exception))
			{
				$this->setStatus(self::STATUS_ERROR);
				$this->unregister();
			}
			else
			{
				$actionInfo['step']++;
				if (
					!array_key_exists('next', $actionInfo['setting'])
					|| $actionInfo['setting']['next'] === false
				)
				{
					$actionInfo['currentSection']++;
					$actionInfo['step'] = 0;
				}
				if (
					(
						!array_key_exists('finish', $actionInfo['setting'])
						&& !isset($actionInfo['section'][$actionInfo['currentSection']])
					)
					|| 	$actionInfo['setting']['finish'] === true
				)
				{
					$actionInfo['setting']['finish'] = false;
					$actionInfo['currentSection'] = 0;
					$actionInfo['step'] = 0;
					$key = array_search($actionInfo['run'], self::STEPS_ORDER);
					if ($key !== false)
					{
						$key++;
					}
					$actionInfo['run'] = self::STEPS_ORDER[$key] ?? false;
				}
			}

			$result = $this->getSetting()->set(Setting::SETTING_ACTION_INFO, $actionInfo);
		}
		else
		{
			$this->setStatus(self::STATUS_ERROR);
		}

		return $result;
	}

	protected function check(array $data): bool
	{
		$result = false;
		if (!empty($data[self::PROPERTY_MANIFEST]['CODE']))
		{
			$result = true;
		}

		return $result;
	}

	/**
	 * @param string|null $next
	 * @param int $step
	 * @param string $type
	 *
	 * @return array
	 */
	public function doInitManifest(?string $next, int $step, string $type): array
	{
		$result = [
			'next' => false,
			'finish' => true,
		];
		$additionalOption = $this->getSetting()->get(Setting::SETTING_ACTION_ADDITIONAL_OPTION);
		$items = Manifest::callEventInit(
			$this->getManifestCode(),
			[
				'TYPE' => $type,
				'STEP' => $step,
				'NEXT' => $next,
				'ITEM_CODE' => '',
				'CONTEXT_USER' => $this->getContext(),
				'ADDITIONAL_OPTION' => $additionalOption,
			]
		);
		foreach ($items as $item)
		{
			$this->getNotificationInstance()->save($item);
			if ($item['NEXT'] !== false)
			{
				$result['next'] = $item['NEXT'];
				$result['finish'] = false;
			}
		}

		return $result;
	}

	/**
	 * @param $code
	 * @param $step
	 * @param $next
	 * @param bool $clearFull
	 *
	 * @return array
	 */
	public function doClean($code, $step, $next, bool $clearFull = false): array
	{
		$result = [
			'next' => false,
		];

		if ($code)
		{
			$additionalOption = $this->getSetting()->get(Setting::SETTING_ACTION_ADDITIONAL_OPTION);
			$data = Controller::callEventClear(
				[
					'CODE' => $code,
					'STEP' => $step,
					'NEXT' => $next,
					'CONTEXT' => $this->getContextEntity(),
					'CONTEXT_USER' => $this->getContext(),
					'CLEAR_FULL' => $clearFull,
					'PREFIX_NAME' => Loc::getMessage('REST_CONFIGURATION_INSTALL_CLEAR_PREFIX_NAME'),//todo: lang file
					'MANIFEST_CODE' => $this->getManifestCode(),
					'IMPORT_MANIFEST' => Manifest::get($this->getManifestCode()),
					'ADDITIONAL_OPTION' => $additionalOption,
				]
			);

			$this->getNotificationInstance()->save($data);

			if (isset($data['NEXT']))
			{
				$result['next'] = $data['NEXT'];
			}
		}

		return $result;
	}

	/**
	 * @param $step
	 * @param $code
	 * @param $content
	 *
	 * @return array
	 */
	public function doLoad($step, $code, $content): array
	{
		$result = [
			'result' => true,
		];

		if ($content['COUNT'] > $step)
		{
			$result['result'] = false;
		}

		if (!is_null($content['DATA']))
		{
			$ratio = $this->getSetting()->get(Setting::SETTING_RATIO);
			$additionalOption = $this->getSetting()->get(Setting::SETTING_ACTION_ADDITIONAL_OPTION);
			$dataList = Controller::callEventImport(
				[
					'CODE' => $code,
					'CONTENT' => $content,
					'RATIO' => $ratio,
					'CONTEXT' => $this->getContextEntity(),
					'CONTEXT_USER' => $this->getContext(),
					'MANIFEST_CODE' => $this->getManifestCode(),
					'IMPORT_MANIFEST' => Manifest::get($this->getManifestCode()),
					'ADDITIONAL_OPTION' => $additionalOption,
				]
			);

			foreach ($dataList as $data)
			{
				if (is_array($data['RATIO']))
				{
					if (!$ratio[$code])
					{
						$ratio[$code] = [];
					}
					foreach ($data['RATIO'] as $old => $new)
					{
						$ratio[$code][$old] = $new;
					}
				}

				$this->getNotificationInstance()->save($data);
			}

			$this->getSetting()->set(Setting::SETTING_RATIO, $ratio);
		}

		return $result;
	}

	/**
	 *
	 * @param $actionInfo
	 * @param $data
	 *
	 * @return array
	 */
	private function doInitBackground($actionInfo, $data): array
	{
		$isEnd = false;
		$next = $actionInfo['step'];
		$structure = new Structure($this->setting->getContext());

		if ($data[self::PROPERTY_FILES] && $data[self::PROPERTY_STRUCTURE][Helper::STRUCTURE_FILES_NAME])
		{
			$chunkList = array_chunk($data[self::PROPERTY_FILES], self::COUNT_ADD_FILES_BY_STEP);
			if (!empty($chunkList[$next]))
			{
				$structure->addFileList(
					$chunkList[$next],
					$data[self::PROPERTY_STRUCTURE][Helper::STRUCTURE_FILES_NAME]
				);
			}
			else
			{
				$isEnd = true;
			}
		}
		else
		{
			$isEnd = true;
		}

		if ($isEnd)
		{
			if ((int)$actionInfo['next'] === 0)
			{
				$fileName = Helper::STRUCTURE_SMALL_FILES_NAME . Helper::CONFIGURATION_FILE_EXTENSION;

				foreach ($data[self::PROPERTY_STRUCTURE] as $path)
				{
					if (is_string($path) && mb_strpos($path, $fileName) !== false)
					{
						try
						{
							$content = File::getFileContents($path);
							$structure->unpackSmallFiles($content);
						}
						catch (\Exception $e)
						{
						}
						break;
					}
				}
			}
			elseif ((int)$actionInfo['next'] > 0)
			{
				//one more step will load small files
				$isEnd = false;
				$next = 0;
			}
		}


		return [
			'next' => $next,
			'finish' => $isEnd,
		];
	}

	/**
	 * @param mixed|null $app
	 * @param string $mode
	 * @param array $option
	 *
	 * @return string[]
	 */
	public function doStart($app = null, string $mode = Helper::MODE_IMPORT, array $option = []): array
	{
		$result = [
			'finish' => true,
		];

		$section = Controller::getEntityCodeList();
		$result['section'] = array_values($section);

		if (!is_array($app) && $app !== null)
		{
			$app = \Bitrix\Rest\AppTable::getByClientId($app);
		}

		if (is_array($app))
		{
			$this->getSetting()->set(Setting::SETTING_APP_INFO, $app);
		}

		if (!empty($option['UNINSTALL_APP_ON_FINISH']))
		{
			$this->getSetting()->set(Setting::SETTING_UNINSTALL_APP_CODE, $option['UNINSTALL_APP_ON_FINISH']);
		}

		return $result;
	}

	/**
	 * @param string $mode
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function doFinish(string $mode = Helper::MODE_IMPORT)
	{
		$result = [
			'result' => true,
			'finish' => true,
			'createItemList' => [],
			'additional' => [],
		];

		$app = $this->getSetting()->get(Setting::SETTING_APP_INFO);
		if (!empty($app['ID']))
		{
			if ($app['INSTALLED'] == AppTable::NOT_INSTALLED)
			{
				AppTable::setSkipRemoteUpdate(true);
				$updateResult = AppTable::update(
					$app['ID'],
					[
						'INSTALLED' => AppTable::INSTALLED
					]
				);
				AppTable::setSkipRemoteUpdate(false);
				AppTable::install($app['ID']);
				AppLogTable::log($app['ID'], AppLogTable::ACTION_TYPE_INSTALL);
				$result['result'] = $updateResult->isSuccess();
			}
			else
			{
				$result['result'] = true;
			}
			Helper::getInstance()->setBasicApp($this->getManifestCode(), $app['CODE']);
		}
		else
		{
			Helper::getInstance()->deleteBasicApp($this->getManifestCode());
		}

		$uninstallAppCode = $this->getSetting()->get(Setting::SETTING_UNINSTALL_APP_CODE);
		if (!empty($uninstallAppCode))
		{
			$res = AppTable::getList(
				[
					'filter' => array(
						"=CODE" => $uninstallAppCode,
						"!=STATUS" => AppTable::STATUS_LOCAL
					),
				]
			);
			if ($appInfo = $res->fetch())
			{
				$clean = true;
				$checkResult = AppTable::checkUninstallAvailability($appInfo['ID'], $clean);
				if ($checkResult->isEmpty())
				{
					$result['result'] = true;
					AppTable::uninstall($appInfo['ID'], $clean);
					$appFields = [
						'ACTIVE' => 'N',
						'INSTALLED' => 'N',
					];
					AppTable::update($appInfo['ID'], $appFields);
					AppLogTable::log($appInfo['ID'], AppLogTable::ACTION_TYPE_UNINSTALL);
				}
			}
		}

		$ratio = $this->getSetting()->get(Setting::SETTING_RATIO);
		$app = $this->getSetting()->get(Setting::SETTING_APP_INFO);
		$userId = $this->getSetting()->get(Setting::SETTING_USER_ID) ?? 0;
		$additionalOption = $this->getSetting()->get(Setting::SETTING_ACTION_ADDITIONAL_OPTION);
		$eventResult = Controller::callEventFinish(
			[
				'TYPE' => 'IMPORT',
				'CONTEXT' => $this->getContextEntity(),
				'CONTEXT_USER' => $this->getContext(),
				'RATIO' => $ratio,
				'MANIFEST_CODE' => $this->getManifestCode(),
				'IMPORT_MANIFEST' => Manifest::get($this->getManifestCode()) ?? [],
				'APP_ID' => ($app['ID'] > 0) ? $app['ID'] : 0,
				'USER_ID' => $userId,
				'ADDITIONAL_OPTION' => $additionalOption,
			]
		);

		foreach ($eventResult as $data)
		{
			if (is_array($data['CREATE_DOM_LIST']))
			{
				$result['createItemList'] = array_merge($result['createItemList'], $data['CREATE_DOM_LIST']);
			}

			if (is_array($data['ADDITIONAL']))
			{
				$result['additional'] = array_merge($result['additional'], $data['ADDITIONAL']);
			}
		}
		$result['finish'] = $result['result'];

		return $result;
	}

	protected function checkRegister($data): array
	{
		$result = [];

		if (
			!isset($data['MANIFEST']['CODE'])
			|| !Manifest::isRestImportAvailable($data['MANIFEST']['CODE'])
		)
		{
			$result = [
				'error' => static::ERROR_MANIFEST_IS_NOT_AVAILABLE,
				'error_description' => 'Manifest is not available.',
			];
		}

		return $result;
	}

	/**
	 * Returns information about current action
	 *
	 * @return array
	 */
	public function get(): array
	{
		$status = $this->getStatus();
		$result = parent::get();
		$actionInfo = $this->getSetting()->get(Setting::SETTING_ACTION_INFO);
		if ($status === self::STATUS_PROCESS || $status === self::STATUS_ERROR)
		{
			if ($actionInfo)
			{
				$result['progress'] = [
					'action' => $actionInfo['run'],
					'step' => $actionInfo['step'],
				];
				if (
					!empty($actionInfo['section'][$actionInfo['currentSection']])
					&& !empty($actionInfo['run'])
					&& $actionInfo['run'] !== self::STEP_FINISH
				)
				{
					$result['progress']['section'] = $actionInfo['section'][$actionInfo['currentSection']];
				}
			}
		}
		elseif ($status === self::STATUS_FINISH)
		{
			$result['additional'] = $actionInfo['setting']['additional'] ?? [];
			$result['createItemList'] = $actionInfo['setting']['createItemList'] ?? [];
		}

		return $result;
	}
}
