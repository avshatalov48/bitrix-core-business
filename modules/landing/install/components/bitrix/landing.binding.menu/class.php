<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Landing\Binding;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');
\CBitrixComponent::includeComponentClass('bitrix:landing.filter');

class LandingBindingMenuComponent extends LandingBaseComponent
{
	/**
	 * Bind entity to specific menu.
	 * @param int $id Entity id.
	 * @return boolean
	 */
	protected function actionBind($id)
	{
		$binding = new Binding\Menu($this->arParams['MENU_ID']);
		if ($this->arParams['SITE_ID'])
		{
			$binding->bindLanding($id);
		}
		elseif (!$binding->isForbiddenBindingAction())
		{
			$binding->bindSite($id);
		}
		localRedirect($this->getUri(['success' => 'Y']));
		return true;
	}

	/**
	 * Unbind entity from specific menu.
	 * @param int $id Entity id.
	 * @return boolean
	 */
	protected function actionUnbind($id)
	{
		if (mb_strpos($id, '_') !== false)
		{
			$binding = new Binding\Menu($this->arParams['MENU_ID']);
			list($type, $id) = explode('_', $id);
			if ($type == Binding\Entity::ENTITY_TYPE_SITE)
			{
				if (!$binding->isForbiddenBindingAction())
				{
					$binding->unbindSite($id);
				}
				else
				{
					return false;
				}
			}
			else if ($type == Binding\Entity::ENTITY_TYPE_LANDING)
			{
				$binding->unbindLanding($id);
			}
		}
		return true;
	}

	/**
	 * Excludes binded entities from data.
	 * @return void
	 */
	protected function excludeBindings()
	{
		$excludedIDs = [];
		$neededType = ($this->arParams['SITE_ID'] > 0)
						? Binding\Entity::ENTITY_TYPE_LANDING
						: Binding\Entity::ENTITY_TYPE_SITE;
		$bindings = Binding\Menu::getList($this->arParams['MENU_ID']);
		foreach ($bindings as $binding)
		{
			if ($binding['ENTITY_TYPE'] == $neededType)
			{
				$excludedIDs[] = $binding['ENTITY_ID'];
			}
		}

		if ($excludedIDs)
		{
			LandingFilterComponent::setExternalFilter(
				'!ID',
				$excludedIDs
			);
		}
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if (!$this->init())
		{
			return;
		}

		$this->checkParam('SITE_ID', 0);
		$this->checkParam('TYPE', '');
		$this->checkParam('MENU_ID', '');
		$this->checkParam('PATH_AFTER_CREATE', '');
		$this->checkParam('MODE', 'LIST');

		if (!$this->arParams['MENU_ID'])
		{
			$this->addError('MENU_ID', Loc::getMessage('LANDING_CMP_NOT_MENU_ID'));
		}
		else
		{
			$this->arResult['SUCCESS'] = $this->request('success') == 'Y';
			$this->excludeBindings();
			// urls
			$this->arResult['ACTION_URL'] = $this->getUri([
				'action' => 'bind',
				'sessid' => bitrix_sessid(),
				'param' => '__id__'
			]);
			$this->arResult['LANDING_URL'] = $this->getUri([
				'siteId' => '__id__'
			]);
		}

		if ($this->arParams['MODE'] == 'CREATE')
		{
			$this->template = 'create';
		}

		parent::executeComponent();
	}
}
