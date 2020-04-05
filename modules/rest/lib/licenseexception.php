<?php
namespace Bitrix\Rest;


class LicenseException
	extends AccessException
{
	const MESSAGE = 'This feature is not enabled for the current license:';
	const CODE = 'WRONG_LICENSE';
}
