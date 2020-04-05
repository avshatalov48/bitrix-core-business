<?
use Bitrix\Rest\Event\Sender;

/** @deprecated
 * use \Bitrix\Rest\Event\Callback
 */
class CRestEventCallback extends \Bitrix\Rest\Event\Callback
{
	public static function __callStatic($name, $arguments)
	{
		$event = Sender::parseEventName($name);

		Sender::unbind($event['MODULE_ID'], $event['EVENT']);
		Sender::bind($event['MODULE_ID'], $event['EVENT']);

		parent::__callStatic($name, $arguments);
	}
}

/** @deprecated
 * use \Bitrix\Rest\Event\Session
 */
class CRestEventSession
{
	public static function Get()
	{
		return \Bitrix\Rest\Event\Session::get();
	}

	public static function Set($session)
	{
		\Bitrix\Rest\Event\Session::set($session);
	}
}

?>