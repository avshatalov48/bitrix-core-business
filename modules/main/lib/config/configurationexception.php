<?php
namespace Bitrix\Main\Config;

/**
 * Exception is thrown when a configuration error has occurred (i.e. system is frustrated).
 */
class ConfigurationException
	extends \Bitrix\Main\SystemException
{
	public function __construct($message = "", \Throwable $previous = null)
	{
		parent::__construct($message, 180, '', '', $previous);
	}
}
