<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use \Bitrix\Main\UserConsent\Agreement;
use \Bitrix\Main\UserConsent\Internals\ComponentController;

class LandingUserConsentSelectorAjaxController extends ComponentController
{
	/**
	 * Gets available agreements.
	 * @return void
	 */
	protected function getAgreements()
	{
		$this->responseData['list'] = [];
		$list = Agreement::getActiveList();
		foreach ($list as $id => $name)
		{
			$this->responseData['list'][] =[
				'ID' => $id,
				'NAME' => $name
			];
		}
	}

	/**
	 * Gets available actions of controller.
	 * @return array
	 */
	protected function getActions()
	{
		return array(
			'getAgreements',
		);
	}

	/**
	 * Checks permissions.
	 * @return bool
	 */
	protected function checkPermissions()
	{
		return $GLOBALS['USER']->IsAdmin() ||
			   		(
						IsModuleInstalled('bitrix24') &&
						$GLOBALS['USER']->CanDoOperation('bitrix24_config')
					);
	}
}

$controller = new LandingUserConsentSelectorAjaxController();
$controller->exec();