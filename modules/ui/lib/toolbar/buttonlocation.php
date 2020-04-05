<?

namespace Bitrix\UI\Toolbar;

abstract class ButtonLocation
{
	const AFTER_TITLE  = "after_title";
	const RIGHT        = "right";
	const AFTER_FILTER = "after_filter";
	/** @deprecated  */
	const FILTER_RIGHT = self::AFTER_FILTER;
}