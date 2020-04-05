<?php

namespace Bitrix\Main;

interface Errorable
{
	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	public function getErrors();

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	public function getErrorByCode($code);
}