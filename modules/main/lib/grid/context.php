<?

namespace Bitrix\Main\Grid;


/**
 * Class Context.
 * @package Bitrix\Main\Grid
 */
class Context
{
	/**
	 * @return \Bitrix\Main\HttpRequest
	 */
	protected static function getRequest()
	{
		return \Bitrix\Main\Context::getCurrent()->getRequest();
	}

	/**
	 * Checks whether the request from grid
	 * @return bool
	 */
	public static function isInternalRequest()
	{
		$request = self::getRequest();
		return (
			($request->get("internal") == true && $request->get("grid_id")) ||
			($request->getPost("internal") == true && $request->getPost("grid_id"))
		);
	}

	/**
	 * Checks that this is validate action request
	 * @return bool
	 */
	public static function isValidateRequest()
	{
		return static::isInternalRequest() &&
			self::getRequest()->get("grid_action") === "validate";
	}

	/**
	 * Checks that this is edit action request
	 *
	 * @return bool
	 */
	public static function isShowpageRequest()
	{
		return static::isInternalRequest() &&
			self::getRequest()->get("grid_action") === "showpage";
	}
}