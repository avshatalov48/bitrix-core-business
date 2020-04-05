<?php
namespace Bitrix\Rest;


class AuthTypeException extends AccessException
{
	const MESSAGE = 'Current authorization type is denied for this method';
	const CODE = 'WRONG_AUTH_TYPE';
}
