<?
/**
 * usertypestr.php, Тип для пользовательских свойств - СТРОКА
 *
 * Содержит класс реализующий интерфейсы для типа "Строка".
 * @author Bitrix <support@bitrixsoft.com>
 * @version 1.0
 * @package usertype
 */

IncludeModuleLangFile(__FILE__);

/**
 * Данный класс используется для управления экземпляром значения
 * пользовательского свойсва.
 *
 * <p>Некоторые методы этого класса заканчиваются на "2".
 * Они приведены для демонстрации и двойкой исключаются из процесса обработки.</p>
 * @package usertype
 * @subpackage classes
 */
class CUserTypeString extends \Bitrix\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'string';

	/**
	 * Обработчик события OnUserTypeBuildList.
	 *
	 * <p>Эта функция регистрируется в качестве обработчика события OnUserTypeBuildList.
	 * Возвращает массив описывающий тип пользовательских свойств.</p>
	 * <p>Элементы массива:</p>
	 * <ul>
	 * <li>USER_TYPE_ID - уникальный идентификатор
	 * <li>CLASS_NAME - имя класса методы которого формируют поведение типа
	 * <li>DESCRIPTION - описание для показа в интерфейсе (выпадающий список и т.п.)
	 * <li>BASE_TYPE - базовый тип на котором будут основаны операции фильтра (int, double, string, date, datetime)
	 * </ul>
	 * @return array
	 * @static
	 */
	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_STRING_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
			"VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
			//Можно задать компонент для отображения значений свойства в публичной части.
			//"VIEW_COMPONENT_NAME" => "my:system.field.view",
			//"VIEW_COMPONENT_TEMPLATE" => "string",
			//и для редактирования
			//"EDIT_COMPONENT_NAME" => "my:system.field.view",
			//"EDIT_COMPONENT_TEMPLATE" => "string",
			// также можно задать callback для отображения значений
			// "VIEW_CALLBACK" => callable
			// и для редактирования
			// "EDIT_CALLBACK" => callable
		);
	}

	/**
	 * Эта функция вызывается при добавлении нового свойства.
	 *
	 * <p>Эта функция вызывается для конструирования SQL запроса
	 * создания колонки для хранения не множественных значений свойства.</p>
	 * <p>Значения множественных свойств хранятся не в строках, а столбиках (как в инфоблоках)
	 * и тип такого поля в БД всегда text.</p>
	 * @param array $arUserField Массив описывающий поле
	 * @return string
	 * @static
	 */
	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "text";
			case "oracle":
				return "varchar2(2000 char)";
			case "mssql":
				return "varchar(2000)";
		}
	}

	/**
	 * Эта функция вызывается перед сохранением метаданных свойства в БД.
	 *
	 * <p>Она должна "очистить" массив с настройками экземпляра типа свойства.
	 * Для того, чтобы случайно/намеренно никто не записал туда всякой фигни.</p>
	 * @param array $arUserField Массив описывающий поле. <b>Внимание!</b> это описание поля еще не сохранено в БД!
	 * @return array Массив который в дальнейшем будет сериализован и сохранен в БД.
	 * @static
	 */
	function PrepareSettings($arUserField)
	{
		$size = intval($arUserField["SETTINGS"]["SIZE"]);
		$rows = intval($arUserField["SETTINGS"]["ROWS"]);
		$min = intval($arUserField["SETTINGS"]["MIN_LENGTH"]);
		$max = intval($arUserField["SETTINGS"]["MAX_LENGTH"]);

		return array(
			"SIZE" =>  ($size <= 1? 20: ($size > 255? 225: $size)),
			"ROWS" =>  ($rows <= 1?  1: ($rows >  50?  50: $rows)),
			"REGEXP" => $arUserField["SETTINGS"]["REGEXP"],
			"MIN_LENGTH" => $min,
			"MAX_LENGTH" => $max,
			"DEFAULT_VALUE" => $arUserField["SETTINGS"]["DEFAULT_VALUE"],
		);
	}

	/**
	 * Эта функция вызывается при выводе формы настройки свойства.
	 *
	 * <p>Возвращает html для встраивания в 2-х колоночную таблицу.
	 * в форму usertype_edit.php</p>
	 * <p>т.е. tr td bla-bla /td td edit-edit-edit /td /tr </p>
	 * @param array $arUserField Массив описывающий поле. Для нового (еще не добавленного поля - <b>false</b>)
	 * @param array $arHtmlControl Массив управления из формы. Пока содержит только один элемент NAME (html безопасный)
	 * @return string HTML для вывода.
	 * @static
	 */
	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
			$value = htmlspecialcharsbx($GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"]);
		elseif(is_array($arUserField))
			$value = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		else
			$value = "";
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_DEFAULT_VALUE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[DEFAULT_VALUE]" size="20"  maxlength="225" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["SIZE"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["SIZE"]);
		else
			$value = 20;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_SIZE").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[SIZE]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["ROWS"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["ROWS"]);
		else
			$value = 1;
		if($value < 1) $value = 1;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_ROWS").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[ROWS]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MIN_LENGTH"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MIN_LENGTH"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_MIN_LEGTH").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MIN_LENGTH]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = intval($GLOBALS[$arHtmlControl["NAME"]]["MAX_LENGTH"]);
		elseif(is_array($arUserField))
			$value = intval($arUserField["SETTINGS"]["MAX_LENGTH"]);
		else
			$value = 0;
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_MAX_LENGTH").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[MAX_LENGTH]" size="20"  maxlength="20" value="'.$value.'">
			</td>
		</tr>
		';
		if($bVarsFromForm)
			$value = htmlspecialcharsbx($GLOBALS[$arHtmlControl["NAME"]]["REGEXP"]);
		elseif(is_array($arUserField))
			$value = htmlspecialcharsbx($arUserField["SETTINGS"]["REGEXP"]);
		else
			$value = "";
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_STRING_REGEXP").':</td>
			<td>
				<input type="text" name="'.$arHtmlControl["NAME"].'[REGEXP]" size="20"  maxlength="200" value="'.$value.'">
			</td>
		</tr>
		';
		return $result;
	}

	/**
	 * Эта функция вызывается при выводе формы редактирования значения свойства.
	 *
	 * <p>Возвращает html для встраивания в ячейку таблицы.
	 * в форму редактирования сущности (на вкладке "Доп. свойства")</p>
	 * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
	 * @return string HTML для вывода.
	 * @static
	 */
	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
			$arHtmlControl["VALUE"] = htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]);
		if($arUserField["SETTINGS"]["ROWS"] < 2)
		{
			$arHtmlControl["VALIGN"] = "middle";
			return '<input type="text" '.
				'name="'.$arHtmlControl["NAME"].'" '.
				'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
				($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
				'value="'.$arHtmlControl["VALUE"].'" '.
				($arUserField["EDIT_IN_LIST"]!="Y"? 'disabled="disabled" ': '').
				'>';
		}
		else
		{
			return '<textarea '.
				'name="'.$arHtmlControl["NAME"].'" '.
				'cols="'.$arUserField["SETTINGS"]["SIZE"].'" '.
				'rows="'.$arUserField["SETTINGS"]["ROWS"].'" '.
				($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
				($arUserField["EDIT_IN_LIST"]!="Y"? 'disabled="disabled" ': '').
				'>'.$arHtmlControl["VALUE"].'</textarea>';
		}
	}

	/**
	 * Эта функция вызывается при выводе формы редактирования значения <b>множественного</b> свойства.
	 *
	 * <p>Если класс не предоставляет такую функцию,
	 * то менеджер типов "соберет" требуемый html из вызовов GetEditFormHTML</p>
	 * <p>Возвращает html для встраивания в ячейку таблицы.
	 * в форму редактирования сущности (на вкладке "Доп. свойства")</p>
	 * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
	 * <p>Поле VALUE $arHtmlControl - массив.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
	 * @return string HTML для вывода.
	 * @static
	 */
/*
	function GetEditFormHTMLMulty($arUserField, $arHtmlControl)
	{
		if($arUserField["VALUE"]===false && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
			$arHtmlControl["VALUE"] = array(htmlspecialcharsbx($arUserField["SETTINGS"]["DEFAULT_VALUE"]));
		$result = array();
		foreach($arHtmlControl["VALUE"] as $value)
		{
			if($arUserField["SETTINGS"]["ROWS"] < 2)
				$result[] = '<input type="text" '.
					'name="'.$arHtmlControl["NAME"].'" '.
					'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
					($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
					'value="'.$value.'" '.
					($arUserField["EDIT_IN_LIST"]!="Y"? 'disabled="disabled" ': '').
					'>';
			else
				$result[] = '<textarea '.
					'name="'.$arHtmlControl["NAME"].'" '.
					'cols="'.$arUserField["SETTINGS"]["SIZE"].'" '.
					'rows="'.$arUserField["SETTINGS"]["ROWS"].'" '.
					($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
					($arUserField["EDIT_IN_LIST"]!="Y"? 'disabled="disabled" ': '').
					'>'.$value.'</textarea>';
		}
		return implode("<br>", $result);
	}
*/
	/**
	 * Эта функция вызывается при выводе фильтра на странице списка.
	 *
	 * <p>Возвращает html для встраивания в ячейку таблицы.</p>
	 * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
	 * @return string HTML для вывода.
	 * @static
	 */
	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		return '<input type="text" '.
			'name="'.$arHtmlControl["NAME"].'" '.
			'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
			'value="'.$arHtmlControl["VALUE"].'"'.
			'>';
	}

	function GetFilterData($arUserField, $arHtmlControl)
	{
		return array(
			"id" => $arHtmlControl["ID"],
			"name" => $arHtmlControl["NAME"],
			"filterable" => ""
		);
	}

	/**
	 * Эта функция вызывается при выводе значения свойства в списке элементов.
	 *
	 * <p>Возвращает html для встраивания в ячейку таблицы.</p>
	 * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
	 * @return string HTML для вывода.
	 * @static
	 */
	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		if(strlen($arHtmlControl["VALUE"])>0)
			return $arHtmlControl["VALUE"];
		else
			return '&nbsp;';
	}

	/**
	 * Эта функция вызывается при выводе значения <b>множественного</b> свойства в списке элементов.
	 *
	 * <p>Возвращает html для встраивания в ячейку таблицы.</p>
	 * <p>Если класс не предоставляет такую функцию,
	 * то менеджер типов "соберет" требуемый html из вызовов GetAdminListViewHTML</p>
	 * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
	 * <p>Поле VALUE $arHtmlControl - массив.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
	 * @return string HTML для вывода.
	 * @static
	 */
/*
	function GetAdminListViewHTMLMulty($arUserField, $arHtmlControl)
	{
		return implode(", ", $arHtmlControl["VALUE"]);
	}
*/
	/**
	 * Эта функция вызывается при выводе значения свойства в списке элементов в режиме <b>редактирования</b>.
	 *
	 * <p>Возвращает html для встраивания в ячейку таблицы.</p>
	 * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
	 * @return string HTML для вывода.
	 * @static
	 */
	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		if($arUserField["SETTINGS"]["ROWS"] < 2)
			return '<input type="text" '.
				'name="'.$arHtmlControl["NAME"].'" '.
				'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
				($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
				'value="'.$arHtmlControl["VALUE"].'" '.
				'>';
		else
			return '<textarea '.
				'name="'.$arHtmlControl["NAME"].'" '.
				'cols="'.$arUserField["SETTINGS"]["SIZE"].'" '.
				'rows="'.$arUserField["SETTINGS"]["ROWS"].'" '.
				($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
				'>'.$arHtmlControl["VALUE"].'</textarea>';
	}

	/**
	 * Эта функция вызывается при выводе <b>множественного</b> свойства в списке элементов в режиме <b>редактирования</b>.
	 *
	 * <p>Возвращает html для встраивания в ячейку таблицы.</p>
	 * <p>Если класс не предоставляет такую функцию,
	 * то менеджер типов "соберет" требуемый html из вызовов GetAdminListEditHTML</p>
	 * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
	 * <p>Поле VALUE $arHtmlControl - массив.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
	 * @return string HTML для вывода.
	 * @static
	 */
/*
	function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
	{
		$result = array();
		foreach($arHtmlControl["VALUE"] as $value)
		{
			if($arUserField["SETTINGS"]["ROWS"] < 2)
				$result[] = '<input type="text" '.
					'name="'.$arHtmlControl["NAME"].'" '.
					'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
					($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
					'value="'.$value.'" '.
					'>';
			else
				$result[] = '<textarea '.
					'name="'.$arHtmlControl["NAME"].'" '.
					'cols="'.$arUserField["SETTINGS"]["SIZE"].'" '.
					'rows="'.$arUserField["SETTINGS"]["ROWS"].'" '.
					($arUserField["SETTINGS"]["MAX_LENGTH"]>0? 'maxlength="'.$arUserField["SETTINGS"]["MAX_LENGTH"].'" ': '').
				'>'.$value.'</textarea>';
		}
		return '&nbsp;'.implode("<br>", $result);
	}
*/
	/**
	 * Эта функция вызывается при выводе значений свойства в публичной части сайта.
	 *
	 * <p>Возвращает html.</p>
	 * <p>Если класс не предоставляет такую функцию,
	 * то менеджер типов вызовет компонент указанный в метаданных свойства или системный bitrix:system.field.view</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arAdditionalParameters Дополнительные параметры (например context).
	 * @return string HTML для вывода.
	 * @static
	 */

	public static function GetPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';
		$first = true;
		foreach ($value as $res)
		{
			if (!$first)
			{
				$html .= static::getHelper()->getMultipleValuesSeparator();
			}
			$first = false;

			$res = \Bitrix\Main\Text\HtmlFilter::encode($res);

			if($arUserField['SETTINGS']['ROWS'] > 1 && strlen($res) > 0)
			{
				$res = nl2br($res);
			}

			if (strlen($arUserField['PROPERTY_VALUE_LINK']) > 0)
			{
				$res = '<a href="'.htmlspecialcharsbx(str_replace('#VALUE#', urlencode($res), $arUserField['PROPERTY_VALUE_LINK'])).'">'.$res.'</a>';
			}

			$html .= static::getHelper()->wrapSingleField($res);
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}

	/**
	 * Эта функция вызывается при редактировании значений свойства в публичной части сайта.
	 *
	 * <p>Возвращает html.</p>
	 * <p>Если класс не предоставляет такую функцию,
	 * то менеджер типов вызовет компонент указанный в метаданных свойства или системный bitrix:system.field.edit</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param array $arAdditionalParameters Дополнительные параметры (например context или bVarsFromForm).
	 * @return string HTML для вывода.
	 * @static
	 */

	public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$value = static::getFieldValue($arUserField, $arAdditionalParameters);

		$html = '';

		foreach ($value as $res)
		{
			$attrList = array();

			if($arUserField["SETTINGS"]["MAX_LENGTH"] > 0)
			{
				$attrList['maxlength'] = intval($arUserField["SETTINGS"]["MAX_LENGTH"]);
			}

			if($arUserField["EDIT_IN_LIST"] != "Y")
			{
				$attrList['disabled'] = 'disabled';
			}

			if ($arUserField["SETTINGS"]["ROWS"] < 2)
			{
				if($arUserField["SETTINGS"]["SIZE"] > 0)
				{
					$attrList['size'] = intval($arUserField["SETTINGS"]["SIZE"]);
				}
			}
			else
			{
				$attrList['cols'] = intval($arUserField["SETTINGS"]["SIZE"]);
				$attrList['rows'] = intval($arUserField["SETTINGS"]["ROWS"]);
			}

			if(array_key_exists('attribute', $arAdditionalParameters))
			{
				$attrList = array_merge($attrList, $arAdditionalParameters['attribute']);
			}

			if(isset($attrList['class']) && is_array($attrList['class']))
			{
				$attrList['class'] = implode(' ', $attrList['class']);
			}

			$attrList['class'] = static::getHelper()->getCssClassName().(isset($attrList['class']) ? ' '.$attrList['class'] : '');

			$attrList['name'] = $fieldName;
			$attrList['tabindex'] = '0';

			if($arUserField["SETTINGS"]["ROWS"] < 2)
			{
				$attrList['type'] = 'text';
				$attrList['value'] = $res;

				$html .= static::getHelper()->wrapSingleField('<input '.static::buildTagAttributes($attrList).'/>');
			}
			else
			{
				$html .= static::getHelper()->wrapSingleField('<textarea '.static::buildTagAttributes($attrList).'>'.htmlspecialcharsbx($res).'</textarea>');
			}
		}

		if ($arUserField["MULTIPLE"] == "Y" && $arAdditionalParameters["SHOW_BUTTON"] != "N")
		{
			$html .= static::getHelper()->getCloneButton($fieldName);
		}

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}


/**
	 * Можно Зарегистрировать обработчик события onBeforeGetPublicView и настроить отображение
	 * путём манипуляции с метаданными пользовательского свойства.
	 \Bitrix\Main\EventManager::getInstance()->addEventHandler(
			'main',
			"onBeforeGetPublicView",
			array("CUserTypeString", "onBeforeGetPublicView")
	);
	 * Аналогично можно сделать и для редактирования: onBeforeGetPublicEdit (EDIT_COMPONENT_NAME и EDIT_COMPONENT_TEMPLATE)
	 */
/*
	public static function onBeforeGetPublicView($event)
	{
		$params = $event->getParameters();
		$arUserField = &$params[0];
		$arAdditionalParameters = &$params[1];
		if ($arUserField["USER_TYPE_ID"] == "string")
		{
			$arUserField["VIEW_COMPONENT_NAME"] = "my:system.field.view";
			$arUserField["VIEW_COMPONENT_TEMPLATE"] = "string";
		}
	}
*/

	/**
	 * Можно Зарегистрировать обработчик события onGetPublicView и показать свойство так, как вам нужно.
	 \Bitrix\Main\EventManager::getInstance()->addEventHandler(
			'main',
			"onGetPublicView",
			array("CUserTypeString", "onGetPublicView")
	);
	 * Аналогично можно сделать и для редактирования: onGetPublicEdit
	 */
/*
	public static function onGetPublicView($event)
	{
		$params = $event->getParameters();
		$arUserField = $params[0];
		$arAdditionalParameters = $params[1];
		if ($arUserField["USER_TYPE_ID"] == "string")
		{
			$html = "demo string";
			return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, $html);
		}
	}
*/

	/**
	 * Можно Зарегистрировать обработчик события onAfterGetPublicView и модифицировать html перед его показом.
	 \Bitrix\Main\EventManager::getInstance()->addEventHandler(
			'main',
			"onAfterGetPublicView",
			array("CUserTypeString", "onAfterGetPublicView")
	);
	 * Аналогично можно сделать и для редактирования: onAfterGetPublicEdit
	 */
/*
	public static function onAfterGetPublicView($event)
	{
		$params = $event->getParameters();
		$arUserField = $params[0];
		$arAdditionalParameters = $params[1];
		$html = &$params[2];
		if ($arUserField["USER_TYPE_ID"] == "string")
		{
			$html .= "!";
		}
	}
*/

	/**
	 * Эта функция валидатор.
	 *
	 * <p>Вызывается из метода CheckFields объекта $USER_FIELD_MANAGER.</p>
	 * <p>Который в свою очередь может быть вызван из меторов Add/Update сущности владельца свойств.</p>
	 * <p>Выполняется 2 проверки:</p>
	 * <ul>
	 * <li>на минимальную длину (если в настройках минимальная длина больше 0).
	 * <li>на регулярное выражение (если задано в настройках).
	 * </ul>
	 * @param array $arUserField Массив описывающий поле.
	 * @param string $value значение для проверки на валидность
	 * @return array массив массивов ("id","text") ошибок.
	 * @static
	 */
	function CheckFields($arUserField, $value)
	{
		$aMsg = array();
		if($value <> '' && strlen($value) < $arUserField["SETTINGS"]["MIN_LENGTH"])
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => GetMessage("USER_TYPE_STRING_MIN_LEGTH_ERROR",
					array(
						"#FIELD_NAME#"=>($arUserField["EDIT_FORM_LABEL"] <> ''? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"]),
						"#MIN_LENGTH#"=>$arUserField["SETTINGS"]["MIN_LENGTH"]
					)
				),
			);
		}
		if($arUserField["SETTINGS"]["MAX_LENGTH"] > 0 && strlen($value) > $arUserField["SETTINGS"]["MAX_LENGTH"])
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => GetMessage("USER_TYPE_STRING_MAX_LEGTH_ERROR",
					array(
						"#FIELD_NAME#"=>($arUserField["EDIT_FORM_LABEL"] <> ''? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"]),
						"#MAX_LENGTH#"=>$arUserField["SETTINGS"]["MAX_LENGTH"]
					)
				),
			);
		}
		if($arUserField["SETTINGS"]["REGEXP"] <> '' && !preg_match($arUserField["SETTINGS"]["REGEXP"], $value))
		{
			$aMsg[] = array(
				"id" => $arUserField["FIELD_NAME"],
				"text" => (strlen($arUserField["ERROR_MESSAGE"])>0?
						$arUserField["ERROR_MESSAGE"]:
						GetMessage("USER_TYPE_STRING_REGEXP_ERROR",
						array(
							"#FIELD_NAME#"=>($arUserField["EDIT_FORM_LABEL"] <> ''? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"]),
						)
					)
				),
			);
		}
		return $aMsg;
	}

	/**
	 * Эта функция должна вернуть представление значения поля для поиска.
	 *
	 * <p>Вызывается из метода OnSearchIndex объекта $USER_FIELD_MANAGER.</p>
	 * <p>Который в свою очередь вызывается и функции обновления поискового индекса сущности.</p>
	 * <p>Для множественных значений поле VALUE - массив.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @return string посковое содержимое.
	 * @static
	 */
	function OnSearchIndex($arUserField)
	{
		if(is_array($arUserField["VALUE"]))
			return implode("\r\n", $arUserField["VALUE"]);
		else
			return $arUserField["VALUE"];
	}

	/**
	 * Эта функция вызывается перед сохранением значений в БД.
	 *
	 * <p>Вызывается из метода Update объекта $USER_FIELD_MANAGER.</p>
	 * <p>Для множественных значений функция вызывается несколько раз.</p>
	 * @param array $arUserField Массив описывающий поле.
	 * @param mixed $value Значение.
	 * @return string значение для вставки в БД.
	 * @static
	 */
/*
	function OnBeforeSave($arUserField, $value)
	{
		if(strlen($value)>0)
			return "".round(doubleval($value), $arUserField["SETTINGS"]["PRECISION"]);
	}
*/
}
?>
