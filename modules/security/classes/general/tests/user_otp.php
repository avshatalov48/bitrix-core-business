<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage security
 * @copyright 2001-2013 Bitrix
 */

class CSecurityUserOtpTest extends CSecurityUserTest
{
	protected $internalName = 'UsersTest';
	protected $tests = [
		"checkOtp" => [
			"method" => "checkOtp",
		]
	];

	protected function checkOtp()
	{
		if (\Bitrix\Security\Mfa\Otp::isOtpEnabled())
		{
			$ids = [];

			$dbUser = $this->getAdminUserList();
			while ($user = $dbUser->fetch())
			{
				if ($user && (int)$user['ID'] > 0)
				{
					$userInfo = \Bitrix\Security\Mfa\Otp::getByUser($user['ID']);
					if (!$userInfo->isActivated())
					{
						$ids[] = $user['ID'];
					}
				}
			}

			if (count($ids))
			{
				$this->addUnformattedDetailError(
					'SECURITY_SITE_CHECKER_ADMIN_OTP_NOT_USED',
					CSecurityCriticalLevel::MIDDLE,
					parent::formatRecommendation($ids)
				);
			}
		}
		else
		{
			$this->addUnformattedDetailError('SECURITY_SITE_CHECKER_OTP_NOT_USED', CSecurityCriticalLevel::MIDDLE);
		}
	}

}