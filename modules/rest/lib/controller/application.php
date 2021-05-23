<?php

namespace Bitrix\Rest\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\Marketplace;
use Bitrix\Main\Engine\ActionFilter;

class Application extends Controller
{
	public function installAction($code, $version = false, $checkHash = false, $installHash = false, $from = null)
	{
		return Marketplace\Application::install($code, $version, $checkHash, $installHash, $from);
	}

	public function uninstallAction($code, $clean = 'N', $from = null)
	{
		return Marketplace\Application::uninstall($code, ($clean === 'Y'), $from);
	}

	public function reinstallAction($id)
	{
		return Marketplace\Application::reinstall($id);
	}

	public function setRightsAction($appId, $rights)
	{
		return Marketplace\Application::setRights($appId, $rights);
	}

	public function getRightsAction($appId)
	{
		return Marketplace\Application::getRights($appId);
	}

	/**
	 * Returns default pre-filters for action.
	 * @return array
	 */
	protected function getDefaultPreFilters()
	{
		$defaultPreFilters = parent::getDefaultPreFilters();
		$defaultPreFilters[] = new ActionFilter\Scope(ActionFilter\Scope::NOT_REST);

		return $defaultPreFilters;
	}
}