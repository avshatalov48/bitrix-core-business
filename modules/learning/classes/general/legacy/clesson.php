<?php
/**
 * Code in this class is for temporary backward compatibility only, don't relay on it!
 * @deprecated
 */
class CLesson
{
	/**
	 * simple & stupid stub
	 * @deprecated
	 */
	public static function GetList($arOrder = 'will be ignored', $arFilter = array())
	{
		// We must replace '...ID' => '...LESSON_ID', 
		// where '...' is some operation (such as '!', '<=', etc.)
		foreach ($arFilter as $key => $value)
		{
			// If key ends with 'ID'
			if ((mb_strlen($key) >= 2) && (mb_strtoupper(mb_substr($key, -2)) === 'ID'))
			{
				// And prefix before 'ID' doesn't contains letters
				if ( ! preg_match ("/[a-zA-Z_]+/", mb_substr($key, 0, -2)) )
				{
					$prefix = '';
					if (mb_strlen($key) > 2)
						$prefix = mb_substr($key, 0, -2);

					$arFields[$prefix . 'LESSON_ID'] = $arFilter[$key];
					unset ($arFilter[$key]);
				}
			}
		}

		return (CLearnLesson::GetList(array(), $arFilter));
	}
}