<?php

namespace Bitrix\Translate;

use Bitrix\Main;

interface IErrorable extends Main\Errorable
{
	/**
	 * Adds error to error collection.
	 *
	 * @param Main\Error $error Error.
	 *
	 * @return $this
	 */
	public function addError(Main\Error $error);

	/**
	 * Adds list of errors to error collection.
	 *
	 * @param Main\Error[] $errors Errors.
	 *
	 * @return $this
	 */
	public function addErrors(array $errors);


	/**
	 * Getting array of errors.
	 *
	 * @return boolean
	 */
	public function hasErrors();
}