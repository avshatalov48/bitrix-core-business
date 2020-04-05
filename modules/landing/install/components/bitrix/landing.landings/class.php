<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Landing;
use Bitrix\Landing\Manager;
use \Bitrix\Main\Entity;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');
\CBitrixComponent::includeComponentClass('bitrix:landing.filter');

class LandingLandingsComponent extends LandingBaseComponent
{
	/**
	 * Copy some landing.
	 * @param int $id Landing id.
	 * @param array $additional Additional params.
	 * @return boolean
	 */
	protected function actionCopy($id, $additional = array())
	{
		$res = \Bitrix\Landing\PublicAction\Landing::copy(
			$id,
			isset($additional['siteId']) ? $additional['siteId'] : false
		);

		if ($res->getError()->isEmpty())
		{
			return true;
		}
		else
		{
			$this->setErrors(
				$res->getError()->getErrors()
			);
		}

		return false;
	}

	/**
	 * Get previews from folder.
	 * @param int $folderId Folder id.
	 * @return array
	 */
	protected function getFolderPreviews($folderId)
	{
		$b24 = \Bitrix\Landing\Manager::isB24();
		$previews = array();
		$pages = $this->getLandings(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'FOLDER_ID' => $folderId
			),
			'order' => array(
				'ID' => 'DESC'
			),
			'limit' => 4
		));
		foreach ($pages as $page)
		{
			$landing = Landing::createInstance($page['ID'], array(
				'blocks_limit' => 1
			));
			$previews[$page['ID']] = !$b24 ? $landing->getPreview() : '';
		}
		if ($b24 && isset($landing) && !empty($previews))
		{
			$publicUrls = $landing->getPublicUrl(array_keys($previews));
			foreach ($publicUrls as $id => $url)
			{
				$previews[$id] = $url . 'preview.jpg';
			}
		}
		return $previews;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init)
		{
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$b24 = \Bitrix\Landing\Manager::isB24();

			$this->checkParam('SITE_ID', 0);
			$this->checkParam('TYPE', '');
			$this->checkParam('ACTION_FOLDER', 'folderId');
			$this->checkParam('PAGE_URL_LANDING_EDIT', '');
			$this->checkParam('PAGE_URL_LANDING_VIEW', '');

			$filter = LandingFilterComponent::getFilter(
				LandingFilterComponent::TYPE_LANDING
			);
			$filter['SITE_ID'] = $this->arParams['SITE_ID'];
			if ($request->offsetExists($this->arParams['ACTION_FOLDER']))
			{
				$filter[] = array(
					'LOGIC' => 'OR',
					'FOLDER_ID' => $request->get($this->arParams['ACTION_FOLDER']),
					'ID' => $request->get($this->arParams['ACTION_FOLDER'])
				);
			}
			else
			{
				$filter['FOLDER_ID'] = false;
			}

			$this->arResult['SITES'] = $sites = $this->getSites();
			$this->arResult['LANDINGS'] = $this->getLandings(array(
				'select' => array(
					'*',
					'DATE_MODIFY_UNIX',
					'DATE_PUBLIC_UNIX'
				),
				'filter' => $filter,
				'runtime' => array(
					new Entity\ExpressionField('DATE_MODIFY_UNIX', 'UNIX_TIMESTAMP(DATE_MODIFY)'),
					new Entity\ExpressionField('DATE_PUBLIC_UNIX', 'UNIX_TIMESTAMP(DATE_PUBLIC)')
				)
			));

			// base data
			$firstItem = false;
			foreach ($this->arResult['LANDINGS'] as &$item)
			{
				if (isset($sites[$item['SITE_ID']]))
				{
					$item['IS_HOMEPAGE'] = $item['ID'] == $sites[$item['SITE_ID']]['LANDING_ID_INDEX'];
				}
				else
				{
					$item['IS_HOMEPAGE'] = false;
				}
				if (!$firstItem)
				{
					$firstItem = &$item;
				}
				$landing = Landing::createInstance($item['ID'], array(
					'blocks_limit' => 1
				));
				$item['PUBLIC_URL'] = '';
				$item['PREVIEW'] = $landing->getPreview();
				if ($item['FOLDER'] == 'Y')
				{
					$item['FOLDER_PREVIEW'] = $this->getFolderPreviews($item['ID']);
				}
			}

			// sort by homepage additional
			uasort($this->arResult['LANDINGS'], function($a, $b)
			{
				if ($a['IS_HOMEPAGE'] === $b['IS_HOMEPAGE'])
				{
					return 0;
				}
				return ($a['IS_HOMEPAGE'] < $b['IS_HOMEPAGE']) ? 1 : -1;
			});

			// public url
			if (isset($landing))
			{
				$publicUrls = $landing->getPublicUrl(array_keys($this->arResult['LANDINGS']));
				foreach ($publicUrls as $id => $url)
				{
					$this->arResult['LANDINGS'][$id]['PUBLIC_URL'] = $url;
				}
			}

		}

		parent::executeComponent();
	}
}