<?php

use Bitrix\Landing\Site as SiteCore;
use Bitrix\Landing\Landing as LandingCore;
use Bitrix\Landing\Site\Type;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Mainpage;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetKb extends LandingBlocksMainpageWidgetBase
{
	private const KB_AMOUNT = 15;

	private const WIDGET_CSS_VAR_PROPERTIES = [
		'COLOR_HEADERS_V2' => '--widget-color-h-v2',
	];

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('COLOR_BUTTON_V2', '#ffffff');
		$this->checkParam('COLOR_HEADERS_V2', '#ffffff');

		foreach (self::WIDGET_CSS_VAR_PROPERTIES as $property => $cssVar)
		{
			$this->addCssVarProperty($property, $cssVar);
		}

		$this->getData();

		parent::executeComponent();
	}

	protected function getData(): void
	{
		$useDemoData = false;
		if (Mainpage\Manager::isUseDemoData())
		{
			$data = $this->getDemoData();
		}
		else
		{
			$data = $this->getRealData();
			if (count($data) === 0)
			{
				$data = $this->getDemoData();
				$useDemoData = true;
			}
		}

		$this->arResult['USE_DEMO_DATA'] = $useDemoData;

		$isExistRealData = $this->checkExistRealData($data);
		if ($isExistRealData)
		{
			$this->arResult['IS_EXIST_REAL_DATA'] = true;
		}
		else
		{
			$this->arResult['IS_EXIST_REAL_DATA'] = false;
		}

		$sort = $this->arParams['SORT'] ?? null;
		if (isset($sort))
		{
			$this->arResult['KNOWLEDGE_BASES'] = $this->sortKnowledgeBases($data, $sort);
		}
		else
		{
			$this->arResult['KNOWLEDGE_BASES'] = $this->sortKnowledgeBases($data);
		}

		$this->checkParam('TITLE', Loc::getMessage('LANDING_WIDGET_KB_DEFAULT_TITLE'));

		$this->arResult['PHRASES'] = [
			'NAVIGATOR_BUTTON' => $this->getNavigatorButtonPhrases(),
		];

		if (count($this->arResult['KNOWLEDGE_BASES']) > 5)
		{
			$this->arResult['IS_SHOW_EXTEND_BUTTON'] = true;
		}
		else
		{
			$this->arResult['IS_SHOW_EXTEND_BUTTON'] = false;
		}
	}

	protected function getDemoData(): array
	{
		return [
			[
				'TITLE' => Loc::getMessage('LANDING_WIDGET_KB_DEMO_DATA_TITLE_1'),
				'PREVIEW' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/kb/1.jpg',
				'DATE_MODIFY' => '15.04.2023 14:32:45',
				'VIEWS' => '1231',
			],
			[
				'TITLE' => Loc::getMessage('LANDING_WIDGET_KB_DEMO_DATA_TITLE_2'),
				'PREVIEW' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/kb/2.jpg',
				'DATE_MODIFY' => '20.03.2023 18:20:10',
				'VIEWS' => '432',
			],
			[
				'TITLE' => Loc::getMessage('LANDING_WIDGET_KB_DEMO_DATA_TITLE_3'),
				'PREVIEW' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/kb/3.jpg',
				'DATE_MODIFY' => '05.05.2023 09:15:30',
				'VIEWS' => '511',
			],
			[
				'TITLE' => Loc::getMessage('LANDING_WIDGET_KB_DEMO_DATA_TITLE_4'),
				'PREVIEW' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/kb/4.jpg',
				'DATE_MODIFY' => '12.06.2023 16:48:55',
				'VIEWS' => '130',
			],
			[
				'TITLE' => Loc::getMessage('LANDING_WIDGET_KB_DEMO_DATA_TITLE_5'),
				'PREVIEW' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/kb/5.jpg',
				'DATE_MODIFY' => '12.06.2023 16:48:55',
				'VIEWS' => '693',
			],
			[
				'TITLE' => Loc::getMessage('LANDING_WIDGET_KB_DEMO_DATA_TITLE_6'),
				'PREVIEW' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/kb/6.jpg',
				'DATE_MODIFY' => '12.06.2023 16:48:55',
				'VIEWS' => '78',
			],
		];
	}

	protected function getRealData(): array
	{
		$knowledgeBasesData = [];
		$ids = [];
		$items = [];
		$filter['=SPECIAL'] = 'N';
		$filter['=TYPE'] = 'KNOWLEDGE';
		Type::setScope('KNOWLEDGE');
		$sites = SiteCore::getList([
			'select' => [
				'*',
				'DOMAIN_NAME' => 'DOMAIN.DOMAIN',
				'DOMAIN_PROVIDER' => 'DOMAIN.PROVIDER',
				'DOMAIN_PREV' => 'DOMAIN.PREV_DOMAIN'
			],
			'filter' => $filter,
			'limit' => self::KB_AMOUNT,
		]);
		while ($site = $sites->fetch())
		{
			$items[$site['ID']] = $site;
			$ids[] = $site['ID'];
		}

		$pictureFromCloud = Manager::isB24() && !Manager::isCloudDisable();
		$landingNull = Landing::createInstance(0);
		$siteUrls = SiteCore::getPublicUrl($ids);
		foreach ($items as $item)
		{
			$knowledgeBaseData = [];
			$knowledgeBaseData['TITLE'] = $item['TITLE'] ?? '';
			$knowledgeBaseData['DATE_MODIFY'] = $item['DATE_MODIFY'] ?? [];

			if (isset($item['ID']))
			{
				$landingRowRes = LandingCore::getList([
					'select' => [
						'ID', 'VIEWS'
					],
					'filter' => [
						'SITE_ID' => $item['ID'],
					],
					'limit' => 1
				]);
				if ($landingRow = $landingRowRes->fetch())
				{
					$knowledgeBaseData['VIEWS'] = $landingRow['VIEWS'];
				}
			}

			$item['PUBLIC_URL'] = '';
			$item['PREVIEW'] = '';
			if (isset($siteUrls[$item['ID']]))
			{
				$item['PUBLIC_URL'] = $siteUrls[$item['ID']];
			}
			if ($item['PUBLIC_URL'])
			{
				if ($item['DOMAIN_ID'] > 0 && $pictureFromCloud && $item['TYPE'] !== 'SMN')
				{
					$knowledgeBaseData['PREVIEW'] = $landingNull->getPreview($item['LANDING_ID_INDEX'], true);
				}
				elseif ($item['LANDING_ID_INDEX'])
				{
					$knowledgeBaseData['PREVIEW'] = $landingNull->getPreview($item['LANDING_ID_INDEX'], true);
				}
				else
				{
					$knowledgeBaseData['PREVIEW'] = Manager::getUrlFromFile('/bitrix/images/landing/nopreview.jpg');
				}
			}
			$knowledgeBaseData['PUBLIC_URL'] = $item['PUBLIC_URL'];
			$knowledgeBasesData[] = $knowledgeBaseData;
		}
		Type::setScope('MAINPAGE');

		return $knowledgeBasesData;
	}

	protected function sortKnowledgeBases($data, $sort = 'viewsHighToLow')
	{
		if ($sort === 'viewsLowToHigh' || $sort === 'viewsHighToLow')
		{
			$views = array_map(function($item) {
				return $item['VIEWS'] ?? 0;
			}, $data);
			if (count($views) === count($data))
			{
				switch ($sort)
				{
					case 'viewsLowToHigh':
						array_multisort($views, SORT_ASC, $data);
						break;
					case 'viewsHighToLow':
						array_multisort($views, SORT_DESC, $data);
						break;
				}
			}
		}
		else
		{
			$dateModify = array_map(function($item) {
				return $item['DATE_MODIFY'] ?? 0;
			}, $data);
			if (count($dateModify) === count($data))
			{
				switch ($sort)
				{
					case 'dateModifyLowToHigh':
						array_multisort($dateModify, SORT_ASC, $data);
						break;
					case 'dateModifyHighToLow':
						array_multisort($dateModify, SORT_DESC, $data);
						break;
				}
			}
		}

		return $data;
	}

	protected function checkExistRealData($data): bool
	{
		return is_array($data) && count($data) > 0;
	}
}
