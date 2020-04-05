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
	 * Get text.
	 *
	 * @return string
	 */
	public function getText()
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

		return Text::replace($text, $replaceData, true);
	}

	/**
	 * Get label text.
	 *
	 * @return string
	 */
	public function getLabelText()
	{
		if ($this->isCustomType())
		{
			return $this->data['LABEL_TEXT'];
		}

		$label = $this->intl->getLabelText();
		return Text::replace($label, $this->replace);
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
			$providerCode = isset($this->data['DATA_PROVIDER']) ? $this->data['DATA_PROVIDER'] : null;
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
			$field['VALUE'] = isset($fieldValues[$fieldCode]) ? $fieldValues[$fieldCode] : '';
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
			$result[$fieldCode] = isset($fieldValues[$fieldCode]) ? $fieldValues[$fieldCode] : '';
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