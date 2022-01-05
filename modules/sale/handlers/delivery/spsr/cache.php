<?php

namespace Sale\Handlers\Delivery\Spsr;

/**
 * Class Cache
 *
 * @package Sale\Handlers\Delivery\Spsr
 */
class Cache
{
	public static function cleanAll()
	{}

	public static function getSidResult($login, $pass)
	{}

	public static function setSid($sid, $login, $pass)
	{}

	public static function cleanSid()
	{}

	public static function getServiceTypes($login, $pass)
	{}

	public static function setServiceTypes(array $serviceTypes, $login, $pass)
	{}

	public static function cleanServiceTypes()
	{}

	public static function getCalcRes($request)
	{}

	public static function setCalcRes(array $calcRes, $request)
	{}

	public static function cleanCalcRes()
	{}
}
