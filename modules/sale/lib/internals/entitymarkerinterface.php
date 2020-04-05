<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */

interface IEntityMarker
{
	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function getErrorEntity($value);

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function canAutoFixError($value);
	
	/**
	 * @return array
	 */
	public function getAutoFixErrorsList();

	/**
	 * @param $code
	 *
	 * @return \Bitrix\Sale\Result
	 */
	public function tryFixError($code);

	/**
	 * @return bool
	 */
	public function canMarked();

	/**
	 * @return string
	 */
	public function getMarkField();
}