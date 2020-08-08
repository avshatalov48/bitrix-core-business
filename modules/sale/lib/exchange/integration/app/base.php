<?php


namespace Bitrix\Sale\Exchange\Integration\App;


abstract class Base
{
	abstract public function getCode();

	abstract public function getClientId();

	abstract public function getClientSecret();

	abstract public function getAppUrl();
}