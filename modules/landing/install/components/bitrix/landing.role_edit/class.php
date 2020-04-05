<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Role;

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingRoleEditComponent extends LandingBaseFormComponent
{
	/**
	 * Class of current element.
	 * @var string
	 */
	protected $class = 'Role';

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'TITLE'
		);
	}

	/**
	 * Action for switch mode.
	 * @return bool
	 */
	protected function actionMode()
	{
		if (\Bitrix\Landing\Rights::isAdmin())
		{
			\Bitrix\Landing\Rights::switchMode();
			return true;
		}
		$this->addError(
			'ACCESS_DENIED'
		);
		return false;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		$init = $this->init();

		if ($init && !Rights::isAdmin())
		{
			$init = false;
			$this->addError(
				'ACCESS_DENIED',
				'',
				true
			);
		}

		if ($init)
		{
			$this->checkParam('ROLE_EDIT', 0);
			$this->checkParam('PAGE_URL_ROLES', '');
			$this->checkParam('TYPE', '');

			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			$this->id = $this->arParams['ROLE_EDIT'];
			$this->redirectAfterSave = true;
			$this->successSavePage = $this->arParams['PAGE_URL_ROLES'];
			$this->arResult['EXTENDED'] = Rights::isExtendedMode();

			if (!$this->arResult['EXTENDED'])
			{
				// redraw presets title
				$currentRole = $this->getRow();
				if (
					$currentRole['ID']['CURRENT'] &&
					!$currentRole['TITLE']['CURRENT']
				)
				{
					$rolesOriginal = Role::fetchAll();
					foreach ($rolesOriginal as $role)
					{
						if (
							$role['XML_ID']  != '' &&
							$role['XML_ID'] == $currentRole['XML_ID']['CURRENT']
						)
						{
							$currentRole['TITLE']['CURRENT'] = \htmlspecialcharsbx($role['TITLE']);
							$currentRole['TITLE']['~CURRENT'] = $role['TITLE'];
						}
					}
				}
				$this->arResult['ROLE'] = $currentRole;
				$this->arResult['RIGHTS'] = Role::getRights($this->id);
				$this->arResult['TASKS'] = Rights::getAccessTasks();
				$this->arResult['TASK_DENIED_CODE'] = Rights::ACCESS_TYPES['denied'];
				$this->arResult['ADDITIONAL'] = Rights::getAdditionalRightsLabels();
				$this->arResult['SITES'] = $this->getSites([
					'filter' => [
						'=DELETED' => ['Y', 'N']
					]
				]);
				// after save/update
				$callback = function(\Bitrix\Main\Event $event)
				{
					$primary = $event->getParameter('primary');
					static $firstCall = true;

					if (!$firstCall)
					{
						return;
					}

					if ($primary)
					{
						$firstCall = false;
						$rights = [];
						// prepare rights
						foreach ($this->request('RIGHTS') as $sId => $codes)
						{
							$rights[$sId] = [];
							if (is_array($codes))
							{
								foreach ($codes as $code)
								{
									if (trim($code))
									{
										$rights[$sId][] = $code;
									}
								}
							}
							if (empty($rights[$sId]))
							{
								$rights[$sId][] = $this->arResult['TASK_DENIED_CODE'];
							}
						}
						// set rights
						Role::setRights(
							$primary['ID'],
							$rights,
							$this->request('ADDITIONAL')
						);
					}
				};
				Role::callback('OnAfterAdd', $callback);
				Role::callback('OnAfterUpdate', $callback);
			}
		}

		parent::executeComponent();
	}
}