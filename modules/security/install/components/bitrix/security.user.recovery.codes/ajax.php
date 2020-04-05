<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Security\Mfa\RecoveryCodesTable;

class CIntranetUserOtpConnectedAjaxController extends \Bitrix\Main\Engine\Controller
{
	protected function getRecoveryCodes($isActiveOnly = false, $isRegenerationAllowed = false)
	{
		global $USER;

		$query = RecoveryCodesTable::query()
			->addSelect('CODE', 'VALUE')
			->addSelect('USED')
			->addSelect('USING_DATE')
			->addFilter('=USER_ID', $USER->getId())
		;
		if ($isActiveOnly)
			$query->addFilter('=USED', 'N');

		$codes = $query->exec()->fetchAll();
		if (is_array($codes) && !empty($codes))
		{
			return $codes;
		}
		elseif ($isRegenerationAllowed)
		{
			return $this->regenerateRecoveryCodes();
		}
		else
		{
			return array();
		}

	}

	public function regenerateRecoveryCodesAction()
	{
		global $USER;

		CUserOptions::SetOption('security', 'recovery_codes_generated', time());
		RecoveryCodesTable::regenerateCodes($USER->getId());
		return $this->getRecoveryCodes(false, false);
	}
}
