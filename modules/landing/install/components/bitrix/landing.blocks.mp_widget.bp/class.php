<?php

use Bitrix\Landing\Mainpage;
use Bitrix\Iblock\ElementTable;
use Bitrix\Lists\Api\Request\ServiceFactory\GetAverageIBlockTemplateDurationRequest;
use Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetBP extends LandingBlocksMainpageWidgetBase
{
	private const BP_AMOUNT = 15;

	private const WIDGET_CSS_VAR_PROPERTIES = [
		'COLOR_BG' => '--widget-color-bg',
		'COLOR_BG_BUTTON' => '--widget-color-bg-button',
		'COLOR_BG_BUTTON_V2' => '--widget-bp-v2-color-bg-button',
		'COLOR_BUTTON_TEXT' => '--widget-color-bg-button-text',
		'COLOR_BUTTON_TEXT_V2' => '--widget-bp-v2-color-bg-button-text',
	];

	/**
	 * The main executable method of the component.
	 * Initializes parameters and obtains data to display.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('COLOR_HEADERS', 'var(--primary)');
		$this->checkParam('COLOR_BG', '#f3fbfe');
		$this->checkParam('COLOR_BUTTON', '#bdc1c6');

		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}

		$this->getData();

		parent::executeComponent();
	}

	/**
	 * Gets the data to display in the widget.
	 * Determines whether to use demo data or real data.
	 * @return void
	 */
	protected function getData(): void
	{
		$this->arResult['SHOW_EMPTY_STATE'] = false;
		if (Mainpage\Manager::isUseDemoData())
		{
			$data = $this->getDemoData();
		}
		else
		{
			$data = $this->getRealData();
			if (count($data) === 0)
			{
				$this->arResult['SHOW_EMPTY_STATE'] = true;
			}
		}

		$this->arResult['BUSINESS_PROCESSES'] = $data;
		$sort = $this->arParams['SORT'] ?? null;

		if (isset($sort))
		{
			$sortedProcesses = $this->sortBusinessProcesses($data, $sort);
		}
		else
		{
			$sortedProcesses = $this->sortBusinessProcesses($data);
		}
		$this->arResult['BUSINESS_PROCESSES'] = array_slice($sortedProcesses, 0, self::BP_AMOUNT);

		$this->checkParam('TITLE', Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_TITLE'));
		$this->checkParam('BUTTON', Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_BUTTON'));
		$this->arResult['TITLE'] = $this->arParams['TITLE'];
		$this->arResult['BUTTON'] = $this->arParams['BUTTON'];
		$this->arResult['SUBTITLE'] = Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_TEXT');
		$this->arResult['SUBTITLE_SHORT'] = Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_TEXT_SHORT');

		$this->arResult['PHRASES'] = [];
		$this->arResult['PHRASES']['NAVIGATOR_BUTTON'] = $this->getNavigatorButtonPhrases();
		if (count($this->arResult['BUSINESS_PROCESSES']) > 5)
		{
			$this->arResult['IS_SHOW_EXTEND_BUTTON'] = true;
		}
		else
		{
			$this->arResult['IS_SHOW_EXTEND_BUTTON'] = false;
		}
	}

	/**
	 * Returns demo data for the widget.
	 * @return array Array with demo data.
	 */
	protected function getDemoData(): array
	{
		$averageTime = [3600, 7200, 86400, 172800, 259800];

		return [
			[
				'NAME' => Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_DEMO_DATA_NAME_1'),
				'COUNT' => '142',
				'AVERAGE_TIME' => $averageTime[0],
				'AVERAGE_TEXT' => $this->getPhraseWithDays($this->secondsToDays($averageTime[0])),
			],
			[
				'NAME' => Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_DEMO_DATA_NAME_2'),
				'COUNT' => '87',
				'AVERAGE_TIME' => $averageTime[1],
				'AVERAGE_TEXT' => $this->getPhraseWithDays($this->secondsToDays($averageTime[1])),
			],
			[
				'NAME' => Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_DEMO_DATA_NAME_3'),
				'COUNT' => '55',
				'AVERAGE_TIME' => $averageTime[2],
				'AVERAGE_TEXT' => $this->getPhraseWithDays($this->secondsToDays($averageTime[2])),
			],
			[
				'NAME' => Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_DEMO_DATA_NAME_4'),
				'COUNT' => '29',
				'AVERAGE_TIME' => $averageTime[3],
				'AVERAGE_TEXT' => $this->getPhraseWithDays($this->secondsToDays($averageTime[3])),
			],
			[
				'NAME' => Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_DEMO_DATA_NAME_5'),
				'COUNT' => '40',
				'AVERAGE_TIME' => $averageTime[4],
				'AVERAGE_TEXT' => $this->getPhraseWithDays($this->secondsToDays($averageTime[4])),
			],
		];
	}

	/**
	 * Returns real data about business processes.
	 * @return array An array with data about business processes.
	 * @throws \Bitrix\Iblock\ElementTable::getCount
	 */
	protected function getRealData(): array
	{
		global $USER;

		$businessProcessesData = [];
		if (Loader::includeModule('lists'))
		{
			$iBlockTypeId = Option::get('lists', 'livefeed_iblock_type_id', 'bitrix_processes');
			$currentUserId = (int)$USER->GetID();
			$service = ServiceFactory::getServiceByIBlockTypeId($iBlockTypeId, $currentUserId);
			if ($service)
			{
				$sefFolder = '/bitrix/components/bitrix/lists.element.creation_guide/?iBlockTypeId=bitrix_processes&iBlockId=';
				$checkPermissionResult = $service->checkIBlockTypePermission();
				$lists_perm = $checkPermissionResult->getPermission();
				if ($lists_perm > CListPermissions::ACCESS_DENIED && $checkPermissionResult->isSuccess())
				{
					$getCatalogResult = $service->getCatalog();
					if ($getCatalogResult->isSuccess())
					{
						$catalog = $getCatalogResult->getCatalog();
						foreach ($catalog as $iBlock)
						{
							$businessProcess = [];
							$businessProcess['ID'] = $iBlock['ID'];
							$businessProcess['NAME'] = $iBlock['NAME'];
							$businessProcess['URL'] = $sefFolder . $iBlock['ID'];
							$businessProcess['IS_SHOW_LIVE_FEED'] = CLists::getLiveFeed($iBlock['ID']);
							$businessProcess['AVERAGE_TIME'] = null;
							if (Loader::includeModule('bizproc'))
							{
								$averageTimeResult = $service->getAverageIBlockTemplateDuration(
									new GetAverageIBlockTemplateDurationRequest(
										$iBlock['ID'], CBPDocumentEventType::Create, false,
									)
								);
								if ($averageTimeResult->isSuccess())
								{
									$seconds = $averageTimeResult->getAverageDuration();
									$daysAmount = 0;
									if ($seconds > 0)
									{
										$daysAmount = $this->secondsToDays($seconds);
									}
									$businessProcess['AVERAGE_TEXT'] = $this->getPhraseWithDays($daysAmount);
								}
								else
								{
									$businessProcess['AVERAGE_TEXT'] = $this->getPhraseWithDays(0);
								}
							}
							$businessProcess['COUNT'] = ElementTable::getCount(
								CIBlockElement::getPublicElementsOrmFilter(['IBLOCK_ID' => $iBlock['ID']])
							);
							$businessProcessesData[] = $businessProcess;
						}
					}
				}
			}
		}

		return $businessProcessesData;
	}

	/**
	 * Sorts business processes in accordance with the specified criterion.
	 *
	 * @param array $data Array of business process data to sort.
	 * @param string $sort Sorting criterion.
	 *
	 * @return array Sorted array of data about business processes.
	 */
	protected function sortBusinessProcesses(array $data, string $sort = 'popularHighToLow'): array
	{
		$count = array_map(function($item) {
			return $item['COUNT'] ?? 0;
		}, $data);

		if (count($count) === count($data))
		{
			switch ($sort)
			{
				case 'popularHighToLow':
					array_multisort($count, SORT_ASC, $data);
					break;
				case 'popularLowToHigh':
					array_multisort($count, SORT_DESC, $data);
					break;
			}
		}

		return $data;
	}

	/**
	 * Converts seconds to days.
	 *
	 * @param int $seconds Number of seconds.
	 *
	 * @return int Number of days.
	 */
	protected function secondsToDays(int $seconds): int
	{
		return ceil($seconds / (60 * 60 * 24));
	}

	/**
	 * Returns a phrase with the number of days.
	 *
	 * @param int $days Number of days.
	 *
	 * @return string A phrase with the number of days.
	 */
	protected function getPhraseWithDays(int $days): string
	{
		if ($days === 0)
		{
			$phrase = Loc::getMessage('CLASS_BLOCK_MP_WIDGET_BP_DAYS_ERROR');
		}
		else
		{
			$phrase = Loc::getMessagePlural('CLASS_BLOCK_MP_WIDGET_BP_DAYS', $days, [
				'#DAYS#' => $days,
			]);
		}

		return $phrase;
	}
}
