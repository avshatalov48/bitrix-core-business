<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Application;
use Bitrix\Main\UserTable;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Intl
 * @package Bitrix\Main\UserConsent
 */
class Intl
{
	/** @var string|null  */
	protected $languageId = null;

	/** @var array  */
	protected $data = [];

	/** @var array  */
	protected static $virtualLanguageMap = ['kz' => 'ru', 'by' => 'ru'];


	/**
	 * Constructor.
	 *
	 * @param string $languageId Language ID.
	 */
	public function __construct($languageId = null)
	{
		if ($languageId)
		{
			$this->load($languageId);
		}
	}

	/**
	 * Load.
	 *
	 * @param string $languageId Language ID.
	 * @return void
	 */
	public function load($languageId)
	{
		$this->data = array();
		$this->languageId = null;

		$list = self::getList();
		foreach ($list as $item)
		{
			if ($item['LANGUAGE_ID'] == $languageId)
			{
				$this->data = $item;
				$this->languageId = $languageId;
				break;
			}
		}
	}

	/**
	 * Return standard fields for current language.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->data['FIELDS'] ?? array();
	}

	/**
	 * Get text.
	 *
	 * @return string
	 */
	public function getText()
	{
		return $this->data['AGREEMENT_TEXT'] ?? '';
	}

	/**
	 * Get label text.
	 *
	 * @return string
	 */
	public function getLabelText()
	{
		return $this->data['LABEL_TEXT'] ?? '';
	}

	/**
	 * Get label text.
	 *
	 * @return string
	 */
	public function getNotifyText()
	{
		return $this->data['NOTIFY_TEXT'] ?? '';
	}

	/**
	 * Get phrase.
	 *
	 * @param string $code Phrase code.
	 * @return string
	 */
	public function getPhrase($code)
	{
		return $this->data['PHRASES'][$code] ?? '';
	}

	/**
	 * Get data value by code.
	 *
	 * @param string $code Data code.
	 * @return string
	 */
	public function getDataValue($code)
	{
		return $this->data[$code] ?? null;
	}


	/**
	 * Get list of language settings.
	 *
	 * @return array
	 */
	public static function getList()
	{
		static $list = null;
		if (is_array($list))
		{
			return $list;
		}

		$user = UserTable::getList(array(
			'select' => array('EMAIL'),
			'filter' => array(
				'=ID' => array_slice(\CGroup::getGroupUser(1), 0, 200),
				'=ACTIVE' => 'Y'
			),
			'limit' => 1
		))->fetch();

		if ($user && $user['EMAIL'])
		{
			$email = $user['EMAIL'];
		}
		else
		{
			$email = Option::get('main', 'email_from', '');
		}

		$list = array();
		$intl = array(
			'ru' => array(
				'PHRASES' => array('COMPANY_NAME', 'IP_NAME'),
				'FIELDS' => array(
					array(
						'CODE' => 'COMPANY_NAME',
						'TYPE' => 'text',
						'TAB' => 'text',
					),
					array(
						'CODE' => 'COMPANY_ADDRESS',
						'TYPE' => 'text',
						'TAB' => 'text',
					),
					array(
						'CODE' => 'PURPOSES',
						'TYPE' => 'text',
						'SHOW_BY_CHECKBOX' => true,
						'TAB' => 'settings',
					),
					array(
						'CODE' => 'THIRD_PARTIES',
						'TYPE' => 'text',
						'SHOW_BY_CHECKBOX' => true,
						'TAB' => 'settings',
					),
					array(
						'CODE' => 'EMAIL',
						'TYPE' => 'string',
						'PLACEHOLDER' => $email,
						'DEFAULT_VALUE' => $email,
						'TAB' => 'settings',
					),
				),
			),
			'ua' => array(
				'PHRASES' => array(),
				'FIELDS' => array(
					array(
						'CODE' => 'COMPANY_NAME',
						'TYPE' => 'text',
						'TAB' => 'text',
					),
				),
			),
			'kz' => array(
				'PHRASES' => array('COMPANY_NAME', 'IP_NAME'),
				'FIELDS' => array(
					array(
						'CODE' => 'COMPANY_NAME',
						'TYPE' => 'text',
						'TAB' => 'text',
					),
					array(
						'CODE' => 'COMPANY_ADDRESS',
						'TYPE' => 'text',
						'TAB' => 'text',
					),
					array(
						'CODE' => 'PURPOSES',
						'TYPE' => 'text',
						'SHOW_BY_CHECKBOX' => true,
						'TAB' => 'settings',
					),
					array(
						'CODE' => 'THIRD_PARTIES',
						'TYPE' => 'text',
						'SHOW_BY_CHECKBOX' => true,
						'TAB' => 'settings',
					),
					array(
						'CODE' => 'EMAIL',
						'TYPE' => 'string',
						'PLACEHOLDER' => $email,
						'DEFAULT_VALUE' => $email,
						'TAB' => 'settings',
					),
				),
			),
			'by' => array(
				'PHRASES' => array('COMPANY_NAME', 'IP_NAME'),
				'FIELDS' => array(
					array(
						'CODE' => 'COMPANY_NAME',
						'TYPE' => 'text',
						'TAB' => 'text',
					),
					array(
						'CODE' => 'COMPANY_ADDRESS',
						'TYPE' => 'text',
						'TAB' => 'text',
					),
					array(
						'CODE' => 'PURPOSES',
						'TYPE' => 'text',
						'SHOW_BY_CHECKBOX' => true,
						'TAB' => 'settings',
					),
					array(
						'CODE' => 'THIRD_PARTIES',
						'TYPE' => 'text',
						'SHOW_BY_CHECKBOX' => true,
						'TAB' => 'settings',
					),
					array(
						'CODE' => 'EMAIL',
						'TYPE' => 'string',
						'PLACEHOLDER' => $email,
						'DEFAULT_VALUE' => $email,
						'TAB' => 'settings',
					),
				),
			)
		);

		$messageMap = array(
			'AGREEMENT_TEXT' => 'MAIN_USER_CONSENT_INTL_TEXT',
			'LABEL_TEXT' => 'MAIN_USER_CONSENT_INTL_LABEL',
			'FIELDS_HINT' => 'MAIN_USER_CONSENT_INTL_FIELDS_HINT',
			'DESCRIPTION' => 'MAIN_USER_CONSENT_INTL_DESCRIPTION',
			'NOTIFY_TEXT' => 'MAIN_USER_CONSENT_INTL_NOTIFY_TEXT',
		);
		$languages = self::getLanguages();
		foreach ($languages as $languageId => $languageName)
		{
			$item = self::getLanguageMessages($languageId, $messageMap);
			if (!$item['AGREEMENT_TEXT'])
			{
				continue;
			}

			$item['NAME'] = Loc::getMessage('MAIN_USER_CONSENT_INTL_NAME', array('%language_name%' => $languageName));
			$item['BASE_LANGUAGE_ID'] = self::$virtualLanguageMap[$languageId] ?? $languageId;
			$item['LANGUAGE_ID'] = $languageId;
			$item['LANGUAGE_NAME'] = $languageName;

			$item['PHRASES'] = array();
			if (isset($intl[$languageId]['PHRASES']))
			{
				$phraseMap = array();
				foreach ($intl[$languageId]['PHRASES'] as $phraseCode)
				{
					$phraseMap[$phraseCode] = "MAIN_USER_CONSENT_INTL_PHRASE_{$phraseCode}";
				}
				$item['PHRASES'] = self::getLanguageMessages($languageId, $phraseMap);
			}

			$item['FIELDS'] = array();
			if (isset($intl[$languageId]['FIELDS']))
			{
				foreach ($intl[$languageId]['FIELDS'] as $field)
				{
					$messageFieldsMap = array(
						'CAPTION' => "MAIN_USER_CONSENT_INTL_FIELD_{$field['CODE']}",
						'PLACEHOLDER' => "MAIN_USER_CONSENT_INTL_FIELD_{$field['CODE']}_HINT",
						'DEFAULT_VALUE' => "MAIN_USER_CONSENT_INTL_FIELD_{$field['CODE']}_DEFAULT",
					);

					$field = $field + self::getLanguageMessages($languageId, $messageFieldsMap);
					if ($field['TYPE'] == 'text' && $field['DEFAULT_VALUE'])
					{
						$field['PLACEHOLDER'] .= "\n" . Loc::getMessage('MAIN_USER_CONSENT_INTL_HINT_FIELD_DEFAULT');
						$field['PLACEHOLDER'] .= "\n" . $field['DEFAULT_VALUE'];
					}

					$item['FIELDS'][] = $field;
				}
			}

			$list[] = $item;
		}

		return $list;
	}

	/**
	 * Get languages.
	 *
	 * @return array
	 */
	public static function getLanguages()
	{
		static $list = null;
		if (is_array($list))
		{
			return $list;
		}

		$list = array();

		// set virtual languages
		foreach (self::$virtualLanguageMap as $virtualLanguageId => $languageId)
		{
			$languageName = Loc::getMessage('MAIN_USER_CONSENT_INTL_LANG_NAME_'.mb_strtoupper($virtualLanguageId));
			if (!$languageName)
			{
				$languageName = $virtualLanguageId;
			}
			$list[$virtualLanguageId] = $languageName;
		}

		// read lang dirs
		$dirLanguages = array();
		$langDir = Application::getDocumentRoot() . '/bitrix/modules/main/lang/';
		$dir = new IO\Directory($langDir);
		if (!$dir->isExists())
		{
			return $list;
		}

		foreach($dir->getChildren() as $childDir)
		{
			if (!$childDir->isDirectory())
			{
				continue;
			}

			$dirLanguages[] = $childDir->getName();
		}

		if (empty($dirLanguages))
		{
			return $list;
		}

		// set languages from DB by dir languages
		$listDb = LanguageTable::getList(array(
			'select' => array('LID', 'NAME'),
			'filter' => array(
				'=LID' => $dirLanguages,
				'=ACTIVE' => 'Y'
			),
			'order' => array('SORT' => 'ASC')
		));
		while ($item = $listDb->fetch())
		{
			$list[$item['LID']] = $item['NAME'];
		}

		return $list;
	}

	/**
	 * Get provider fields.
	 *
	 * @param string $languageId Language ID.
	 * @param array $map Message key map.
	 * @return array
	 */
	public static function getLanguageMessages($languageId, array $map = array())
	{
		// rewrite $languageId by real value from virtual language
		$virtualLanguageId = null;
		if (isset(self::$virtualLanguageMap[$languageId]))
		{
			$virtualLanguageId = $languageId;
			$languageId = self::$virtualLanguageMap[$virtualLanguageId];
		}

		// load messages
		$messages = Loc::loadLanguageFile(__FILE__, $languageId, true);
		// set map by all message codes
		if (empty($map))
		{
			$keys = array_keys($messages);
			foreach ($keys as $key)
			{
				$map[$key] = $key;
			}
		}

		// append postfix to message codes from virtual language
		if ($virtualLanguageId)
		{
			$postfix = '_'.mb_strtoupper($virtualLanguageId);
			foreach ($map as $itemKey => $messageKey)
			{
				if (str_ends_with($itemKey, $postfix))
				{
					$oldItemKey = $itemKey;
					$itemKey = substr($itemKey, 0, -strlen($postfix));
					unset($map[$oldItemKey]);
				}

				if (!str_ends_with($messageKey, $postfix))
				{
					if (isset($messages[$messageKey . $postfix]))
					{
						$messageKey .= $postfix;
					}
				}

				$map[$itemKey] = $messageKey;
			}
		}

		// set messages by map
		$item = array();
		foreach ($map as $itemKey => $messageKey)
		{
			$message = $messages[$messageKey] ?? '';
			$message = trim($message);
			$message = $message == '-' ? '' : $message;

			$item[$itemKey] = $message;
		}

		return $item;
	}
}