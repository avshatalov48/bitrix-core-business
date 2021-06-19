<?php
namespace Bitrix\Forum\Internals;
use \Bitrix\Main;
trait EntityFabric
{
	protected static $repo = [];

	/**
	 * @param int $id
	 * @return static
	 */
	public static function getById($id)
	{
		if (!array_key_exists(__CLASS__, self::$repo))
		{
			self::$repo[__CLASS__] = [];
		}
		$id = intval($id);
		if (!array_key_exists($id, self::$repo[__CLASS__]))
		{
			try
			{
				self::$repo[__CLASS__][$id] = new static($id);
			}
			catch (Main\ObjectNotFoundException $exception)
			{
				return null;
			}
		}
		return self::$repo[__CLASS__][$id];
	}

	public function destroy()
	{
		if (array_key_exists(__CLASS__, self::$repo))
		{
			foreach (self::$repo[__CLASS__] as $key => $object)
			{
				if ($object === $this)
				{
					unset(self::$repo[__CLASS__][$key]);
					AddMessage2Log("Object: ".__CLASS__." $key were destroyed.");
					return;
				}
			}
		}
	}
	/**
	 * @param static|int $object
	 * @return static
	 */
	public static function getInstance($object)
	{
		if ($object instanceof static)
		{
			return $object;
		}
		return static::getById($object);
	}
}