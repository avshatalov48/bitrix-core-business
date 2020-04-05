<?
/**
 * @global int $ID - Edited user id
 * @global \CUser $USER
 * @global CMain $APPLICATION
 * @global string $security_SYNC1 - First code
 * @global string $security_SYNC2  - Second code
 */

$securityWarningTmp = "";
$security_res = true;
if(
	$ID > 0
	&& CModule::IncludeModule("security")
	&& check_bitrix_sessid()
	&& $USER->CanDoOperation('security_edit_user_otp')
	&& $security_SYNC1
):
	try
	{
		$otp = \Bitrix\Security\Mfa\Otp::getByUser($ID);
		$otp->syncParameters($security_SYNC1, $security_SYNC2);
		$otp->save();
	}
	catch (\Bitrix\Security\Mfa\OtpException $e)
	{
		$APPLICATION->ThrowException($e->getMessage());
		$security_res = false;
	}

endif;
