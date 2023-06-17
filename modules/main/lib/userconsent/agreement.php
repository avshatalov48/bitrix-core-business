<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserConsent\Internals\FieldTable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ORM;
use Bitrix\Main\Web\Uri;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Agreement
 * @package Bitrix\Main\UserConsent
 */
class Agreement
{
	const ACTIVE = 'Y';
	const NOT_ACTIVE = 'N';
	const TYPE_STANDARD = 'S';
	const TYPE_CUSTOM = 'C';

	/** @var integer|null $id Agreement ID. */
	protected $id = null;

	/** @var ErrorCollection $errors */
	protected $errors;

	/** @var array $data Agreement data. */
	protected $data = array(
		'ACTIVE' => self::ACTIVE,
		'TYPE' => self::TYPE_CUSTOM,
	);

	/** @var array $replace Replace data. */
	protected $replace = array();

	/** @var Intl $intl Intl. */
	protected $intl;

	/** @var DataProvider|null $dataProvider Data provider. */
	protected $dataProvider;

	private $isAgreementTextHtml;

	/**
	 * Get active agreement list.
	 *
	 * @return array
	 */
	public static function getActiveList()
	{
		$result = array();
		$list = Internals\AgreementTable::getList(array(
			'select' => array('ID', 'NAME'),
			'filter' => array('=ACTIVE' => 'Y'),
			'order' => array('ID' => 'DESC')
		));
		foreach ($list as $item)
		{
			$result[$item['ID']] = $item['NAME'];
		}

		return $result;
	}

	/**
	 * Get types.
	 *
	 * @return array
	 */
	public static function getTypeNames()
	{
		return array(
			self::TYPE_CUSTOM => Loc::getMessage('MAIN_USER_CONSENT_AGREEMENT_TYPE_N'),
			self::TYPE_STANDARD => Loc::getMessage('MAIN_USER_CONSENT_AGREEMENT_TYPE_S'),
		);
	}

	/**
	 * Construct.
	 *
	 * @param integer $id Agreement ID.
	 * @param array $replace Replace data.
	 */
	public function __construct($id, array $replace = array())
	{
		$this->errors = new ErrorCollection();
		$this->intl = new Intl();
		$this->load($id);
		$this->setReplace($replace);

		$this->isAgreementTextHtml = ($this->data['IS_AGREEMENT_TEXT_HTML'] == 'Y');
	}

	/**
	 * Return true if is used.
	 *
	 * @param integer $id Agreement ID.
	 * @return string
	 */
	public function load($id)
	{
		$this->id = null;
		if (!$id)
		{
			$this->errors->setError(new Error('Parameter `Agreement ID` required.'));
			return false;
		}

		$data = Internals\AgreementTable::getRowById($id);
		if (!$data)
		{
			$this->errors->setError(new Error("Agreement with id `$id` not found."));
			return false;
		}

		$this->data = $data;
		$this->id = $id;
		$this->intl->load($this->data['LANGUAGE_ID']);

		return true;
	}

	/**
	 * Set replace.
	 *
	 * @param array $replace Replace data.
	 * @return $this
	 */
	public function setReplace(array $replace)
	{
		$this->replace = $replace;
		return $this;
	}

	/**
	 * Get errors.
	 *
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errors->toArray();
	}

	/**
	 * Has errors.
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return !$this->errors->isEmpty();
	}

	/**
	 * Get agreement ID.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get agreement data.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set agreement data.
	 *
	 * @param array $data Data.
	 * @return void
	 */
	public function setData(array $data)
	{
		unset($data['ID']);
		$this->data = $data;

		$this->isAgreementTextHtml = ($this->data['IS_AGREEMENT_TEXT_HTML'] == 'Y');
	}

	/**
	 * Merge agreement data.
	 *
	 * @param array $data Data.
	 * @return void
	 */
	public function mergeData(array $data)
	{
		$this->setData($data + $this->data);
	}

	/**
	 * Save agreement data.
	 *
	 * @return void
	 */
	public function save()
	{
		$this->errors->clear();
		$data = $this->data;

		$fields = $data['FIELDS'];
		unset($data['FIELDS']);

		if(!$this->check())
		{
			return;
		}

		if ($this->isAgreementTextHtml)
		{
			(new \CBXSanitizer)->sanitizeHtml($data['AGREEMENT_TEXT']);
		}

		if($this->id)
		{
			$result = Internals\AgreementTable::update($this->id, $data);
		}
		else
		{
			$data['DATE_INSERT'] = new DateTime();
			$result = Internals\AgreementTable::add($data);
			$this->id = $result->getId();
		}

		if(!$result->isSuccess())
		{
			return;
		}

		Internals\FieldTable::setConsentFields($this->id, $fields);
	}

	/**
	 * Check.
	 *
	 * @return bool
	 */
	protected function check()
	{
		$data = $this->data;
		$data['DATE_INSERT'] = new DateTime();

		//$fields = $data['FIELDS'];
		unset($data['FIELDS']);

		$result = new ORM\Data\Result;
		Internals\AgreementTable::checkFields($result, $this->id, $data);
		if (!$result->isSuccess())
		{
			$this->errors->add($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * Return true if is exist.
	 *
	 * @return bool
	 */
	public function isExist()
	{
		return $this->id > 0;
	}

	/**
	 * Return true if is active.
	 *
	 * @return string
	 */
	public function isActive()
	{
		return ($this->data['ACTIVE'] == self::ACTIVE);
	}

	public function isAgreementTextHtml(): bool
	{
		return $this->isAgreementTextHtml;
	}

	/**
	 * Return true if is custom type.
	 *
	 * @return string
	 */
	protected function isCustomType()
	{
		return ($this->data['TYPE'] != self::TYPE_STANDARD);
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return trim($this->getTitleFromText($this->getText()));
	}

	protected static function getTitleFromText($text)
	{
		$text = trim($text);
		$maxLength = 50;
		$pos = min(
			mb_strpos($text, "\n")?: 50,
			mb_strpos($text, "<br>")?: 50,
			mb_strpos($text, ".")?: 50,
			$maxLength
		);

		return mb_substr($text, 0, $pos);
	}

	/**
	 * Get text.
	 *
	 * @param bool $cutTitle Cut title.
	 * @return string
	 */
	public function getText($cutTitle = false)
	{
		$text = $this->getContent($cutTitle);

		return ($this->isAgreementTextHtml ? strip_tags($text) : $text);
	}

	/**
	 * Get html.
	 * @return string
	 */
	public function getHtml()
	{
		$text = $this->getContent();

		$text = ($this->isAgreementTextHtml ? $text : nl2br($text));
		$sanitizer = new \CBXSanitizer;
		$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_MIDDLE);
		$sanitizer->allowAttributes([
			'target' => [
				'tag' => function ($tag)
				{
					return $tag === 'a';
				},
				'content' => function ($tag)
				{
					return true;
				},
			]
		]);

		return $sanitizer->sanitizeHtml($text);
	}

	private function getContent($cutTitle = false)
	{
		if ($this->isCustomType())
		{
			return $this->data['AGREEMENT_TEXT'];
		}

		$text = $this->intl->getText();

		$replaceData = array();
		$replaceData = $replaceData + $this->replace;
		$replaceData = $replaceData + $this->getDataProviderValues();
		$replaceData = $replaceData + $this->getReplaceFieldValues();

		$text = Text::replace($text, $replaceData, true);
		$text = trim($text);
		if ($cutTitle)
		{
			$title = self::getTitleFromText($text);
			if (mb_strlen($title) !== 50 && $title === mb_substr($text, 0, mb_strlen($title)))
			{
				$text = trim(mb_substr($text, mb_strlen($title)));
			}
		}

		return $text;
	}

	/**
	 * Get label text.
	 *
	 * @return string
	 */
	public function getLabelText()
	{
		return str_replace('%', '', $this->getLabel());
	}

	/**
	 * Get url.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return ($this->data['USE_URL'] === 'Y' && $this->data['URL'])
			? (new Uri($this->data['URL']))->getLocator()
			: null;
	}

	/**
	 * Get label with synbols '%' for link in label text.
	 *
	 * @return string
	 */
	public function getLabel()
	{
		$text = $this->isCustomType() ? $this->data['LABEL_TEXT'] : $this->intl->getLabelText();
		$text = Text::replace($text, $this->replace);

		if ($this->data['USE_URL'] !== 'Y' || !$this->data['URL'])
		{
			return str_replace('%', '', $text);
		}

		$text = trim(trim($text), "%");
		$text = explode('%', $text);
		$text = array_filter($text);

		/** @var array $text */
		switch (count($text))
		{
			case 0:
			case 1:
			$text = array_merge([''], $text, ['']);
				break;

			case 2:
				$text[] = '';
				break;

			case 3:
				break;

			default:
				$text = array_merge(
					array_slice($text, 0, 2),
					[implode('', array_slice($text, 2))]
				);
				break;
		}

		return implode('%', $text);
	}

	/**
	 * Get data provider fields.
	 *
	 * @return array
	 */
	protected function getDataProviderValues()
	{
		if (!$this->dataProvider)
		{
			$providerCode = $this->data['DATA_PROVIDER'] ?? null;
			$this->dataProvider = DataProvider::getByCode($providerCode);
		}

		if ($this->dataProvider)
		{
			return $this->dataProvider->getData();
		}

		return array();
	}

	/**
	 * Return fields.
	 *
	 * @return array
	 */
	public function getFields()
	{
		$result = array();
		$fields = $this->intl->getFields();
		$fieldValues = FieldTable::getConsentFields($this->id);
		foreach ($fields as $field)
		{
			$fieldCode = $field['CODE'];
			$field['VALUE'] = $fieldValues[$fieldCode] ?? '';
			$result[$fieldCode] = $field;
		}

		return $result;
	}

	/**
	 * Return field values.
	 *
	 * @return array
	 */
	public function getFieldValues()
	{
		$result = array();
		$fields = $this->intl->getFields();
		$fieldValues = FieldTable::getConsentFields($this->id);
		foreach ($fields as $field)
		{
			$fieldCode = $field['CODE'];
			$result[$fieldCode] = $fieldValues[$fieldCode] ?? '';
		}

		return $result;
	}

	protected function getReplaceFieldValues()
	{
		$result = $this->getFieldValues();
		$fields = $this->intl->getFields();

		// set default values to result
		foreach ($fields as $field)
		{
			if (!isset($field['DEFAULT_VALUE']) || !$field['DEFAULT_VALUE'])
			{
				continue;
			}

			if (isset($result[$field['CODE']]) && $result[$field['CODE']])
			{
				continue;
			}

			$result[$field['CODE']] = $field['DEFAULT_VALUE'];
		}

		// set values to result
		foreach ($fields as $field)
		{
			if ($field['TYPE'] != 'enum' || empty($field['TEXT']))
			{
				continue;
			}

			$fieldCode = $field['CODE'];

			$valueAsText = null;
			foreach ($field['ITEMS'] as $item)
			{
				// detect text item
				if (isset($item['IS_TEXT']) && $item['IS_TEXT'])
				{
					continue;
				}

				if ($result[$fieldCode] == $item['CODE'])
				{
					$valueAsText = $item['NAME'];
				}
			}

			if (!$valueAsText)
			{
				continue;
			}

			$result[$field['TEXT']] = Text::formatArrayToText(array($valueAsText));
		}

		return $result;
	}
}