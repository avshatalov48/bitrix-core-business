<?php

namespace Bitrix\Mail\Helper;
/**
 * Class MailContact
 * @package Bitrix\Mail\Helper
 */
class MailContact
{
	const COLOR_GREEN       = '#9dcf01';
	const COLOR_BLUE        = '#2fc6f6';
	const COLOR_LIGHT_BLUE  = '#56d1e0';
	const COLOR_ORANGE      = '#ffa900';
	const COLOR_CYAN        = '#47e4c2';
	const COLOR_PINK        = '#ff5b55';
	const COLOR_PURPLE      = '#9985dd';
	const COLOR_GREY        = '#a8adb4';
	const COLOR_BROWN       = '#af7e00';
	const COLOR_RED         = '#F44336';
	const COLOR_DEEP_PURPLE = '#673AB7';
	const COLOR_INDIGO      = '#3F51B5';
	const COLOR_TEAL        = '#009688';
	const COLOR_LIGHT_GREEN = '#8BC34A';
	const COLOR_LIME        = '#CDDC39';
	const COLOR_YELLOW      = '#FFEB3B';
	const COLOR_AMBER       = '#FFC107';
	const COLOR_DEEP_ORANGE = '#FF5722';
	const COLOR_BLUE_GREY   = '#607D8B';

	/**
	 * @param $email
	 * @param $name
	 * @param null $lastName
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function getIconData($email, $name, $lastName = null)
	{
		return [
			'INITIALS' => static::getInitials($email, $name, $lastName),
			'COLOR' => static::getRandomColor(),
		];
	}

	/**
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public static function getRandomColor()
	{
		static $colors = null;
		if (is_null($colors))
		{
			$reflect = new \ReflectionClass(static::class);
			foreach ($reflect->getConstants() as $name => $value)
			{
				if (strncmp($name, 'COLOR', 5) === 0)
				{
					$colors[] = $value;
				}
			}
		}
		return $colors[rand(0, count($colors) - 1)];
	}

	/** return two symbols from name and last name, or 1 - from name or email
	 * @param $email
	 * @param $name
	 * @param null $lastName
	 * @return string
	 */
	public static function getInitials($email, $name = null, $lastName = null)
	{
		if ($lastName && mb_substr($lastName, 0, 1) && $name && mb_substr($name, 0, 1))
		{
			return mb_strtoupper(mb_substr($name, 0, 1).mb_substr($lastName, 0, 1));
		}

		$name = trim(preg_replace('/([0-9]|[-&\/\'#,+()~%.":*?<>{}])/m', '',$name));

		$name = explode(' ', $name);

		if (is_array($name) && isset($name[0]) && $name[0])
		{
			if (isset($name[1]) && $name[1])
			{
				return mb_strtoupper(mb_substr($name[0], 0, 1).mb_substr($name[1], 0, 1));
			}
			else
			{
				return mb_strtoupper(mb_substr($name[0], 0, 1));
			}
		}
		return mb_strtoupper(mb_substr($email, 0, 1));
	}

	/** returns array of fields and their values for adding to database
	 * @param $mailsField
	 * @param $userId
	 * @param $addedFrom
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function getContactsData($mailsField, $userId, $addedFrom)
	{
		if (!$mailsField)
		{
			return [];
		}
		$mails = explode(',', $mailsField);
		$contacts = [];
		foreach ($mails as $mail)
		{
			$mail = trim($mail);
			$address = new \Bitrix\Main\Mail\Address($mail);
			$emailToAdd = $nameToAdd = '';
			if ($address->validate())
			{
				$emailToAdd = $address->getEmail();
				$nameToAdd = trim($address->getName());
			}
			if ($emailToAdd)
			{
				$contacts[] = [
					'USER_ID' => intval($userId),
					'NAME' => $nameToAdd ? $nameToAdd : explode('@', $emailToAdd)[0],
					'ICON' => static::getIconData($emailToAdd, $nameToAdd),
					'EMAIL' => $emailToAdd,
					'ADDED_FROM' => $addedFrom,
				];
			}
		}
		return $contacts;
	}
}
