<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');
\CBitrixComponent::includeComponentClass('bitrix:landing.filter');

class LandingSitesComponent extends LandingBaseComponent
{

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$b24 = \Bitrix\Landing\Manager::isB24();
			$this->checkParam('TYPE', '');
			$this->checkParam('PAGE_URL_SITE', '');
			$this->checkParam('PAGE_URL_SITE_EDIT', '');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');

			$filter = LandingFilterComponent::getFilter(
				LandingFilterComponent::TYPE_SITE
			);
			$filter['=TYPE'] = $this->arParams['TYPE'];
			$this->arResult['SITES'] = $this->getSites(array(
				'filter' => $filter
			));

			// detect preview of sites
			foreach ($this->arResult['SITES'] as &$item)
			{
				if (!$item['LANDING_ID_INDEX'])
				{
					$landing = $this->getLandings(array(
						'filter' => array(
							'SITE_ID' => $item['ID']
						),
						'order' => array(
							'ID' => 'ASC'
						),
						'limit' => 1
					));
					if ($landing)
					{
						$landing = array_pop($landing);
						$item['LANDING_ID_INDEX'] = $landing['ID'];
					}
				}
				$item['PUBLIC_URL'] = Site::getPublicUrl($item['ID']);
				if ($b24)
				{
					$item['PREVIEW'] = $item['PUBLIC_URL'] . '/preview.jpg';
				}
				elseif ($item['LANDING_ID_INDEX'])
				{
					$landing = Landing::createInstance($item['LANDING_ID_INDEX']);
					if ($landing->exist())
					{
						$item['PREVIEW'] = $landing->getPreview();
					}
				}
				else
				{
					$item['PREVIEW'] = '';
				}
			}
			unset($item);
		}

		parent::executeComponent();

		return $this->arResult;
	}
}