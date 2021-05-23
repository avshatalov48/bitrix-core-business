<?
namespace Bitrix\UI\EntitySelector;

class TextNodeType
{
	public const TEXT = 'text';
	public const HTML = 'html';

	public static function isValid($type): bool
	{
		return is_string($type) && ($type === self::TEXT || $type === self::HTML);
	}
}