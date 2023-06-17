<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\Block\Designer;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Rights;

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingDesignBlockComponent extends LandingBaseComponent
{
	/**
	 * Handler on view landing.
	 * @return void
	 */
	protected function onLandingView()
	{
		$params = $this->arParams;
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('landing', 'onLandingView',
			function(\Bitrix\Main\Event $event) use ($params)
			{
				$result = new \Bitrix\Main\Entity\EventResult;
				$options['params'] = (array) ($params['PARAMS'] ?? []);
				$options['params']['type'] = $params['TYPE'];
				$result->modifyFields([
					'options' => $options
				]);
				return $result;
			}
		);
	}

	/**
	 * Handler on template epilog.
	 * @return void
	 */
	protected function onEpilog()
	{
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('main', 'OnEpilog',
			function()
			{
				Manager::initAssets($this->arParams['LANDING_ID']);
			}
		);
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
			$this->checkParam('SITE_ID', 0);
			$this->checkParam('LANDING_ID', 0);
			$this->checkParam('BLOCK_ID', 0);
			$this->checkParam('TYPE', '');

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			Hook::setEditMode();
			Landing::setEditMode();
			$designBlockId = $this->arParams['BLOCK_ID'];
			$landing = Landing::createInstance(
				$this->arParams['LANDING_ID']
			);
			$blockInstance = $landing->getBlockById($designBlockId);

			if ($landing->exist() && $blockInstance)
			{
				// disable optimisation
				if (\Bitrix\Landing\Manager::isB24())
				{
					$asset = \Bitrix\Main\Page\Asset::getInstance();
					$asset->disableOptimizeCss();
					$asset->disableOptimizeJs();
				}
				$rights = Rights::getOperationsForSite(
					$landing->getSiteId()
				);
				if (!in_array(Rights::ACCESS_TYPES['edit'], $rights))
				{
					$this->addError('ACCESS_DENIED', Loc::getMessage('LANDING_CMP_ACCESS_DENIED2'), true);
				}
				else
				{
					Designer::setLandingDesignBlockMode(true);
					$designer = new Designer($designBlockId);
					if ($designer->isReady())
					{
						$landingZero = \Bitrix\Landing\Landing::createInstance(
							0, ['skip_blocks' => true]
						);
						$landingZero->addBlockToCollection($designer->getBlock());
						$this->arResult['LANDING_ZERO'] = $landingZero;
						$this->arResult['LANDING'] = $landing;
						$this->arResult['DESIGNER'] = $designer;
						$this->arResult['BLOCK_INSTANCE'] = $blockInstance;
						$this->arResult['BLOCK_MANIFEST'] = Block::getManifestFile($blockInstance->getCode());
						$this->onLandingView();
						$this->onEpilog();
					}
					else
					{
						$this->addError('NOT_FOUND', Loc::getMessage('LANDING_CMP_PAGE_NOT_FOUND'), true);
					}
				}
			}
			else
			{
				$this->addError('NOT_FOUND', Loc::getMessage('LANDING_CMP_PAGE_NOT_FOUND'), true);
			}

			// some errors?
			$this->setErrors(
				$landing->getError()->getErrors()
			);
		}

		parent::executeComponent();
	}
}
