<?php
namespace Bitrix\Landing\Hook;

abstract class Page
{
	/**
	 * If true, hook work in edit mode (form settings).
	 * @var boolean
	 */
	protected $editMode = false;

	/**
	 * Current Hook fields.
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Current Hook fields (for show in page context).
	 * @var array
	 */
	protected $fieldsPage = array();

	/**
	 * This hook is instance for page.
	 * @var bool
	 */
	protected $isPage = true;

	/**
	 * Class constructor.
	 * @param boolean $editMode Edit mode if true.
	 * @param boolean $isPage Instance of page.
	 */
	public function __construct($editMode = false, $isPage = true)
	{
		if ($editMode)
		{
			$this->editMode = true;
		}
		if (!$isPage)
		{
			$this->isPage = false;
		}
		$this->fields = $this->getMap();
	}

	/**
	 * This hook is instance for page?
	 * @return bool
	 */
	public function isPage()
	{
		return $this->isPage;
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return '';
	}

	/**
	 * Description of Hook, if you want.
	 * @return string
	 */
	public function getDescription()
	{
		return '';
	}

	/**
	 * Edit mode or not.
	 * @return boolean
	 */
	protected function isEditMode()
	{
		return $this->editMode === true;
	}

	/**
	 * Get sort of block (execute order).
	 * @return int
	 */
	public function getSort()
	{
		return 100;
	}

	/**
	 * Enable only in high plan or not.
	 * @return boolean
	 */
	public function isFree()
	{
		return true;
	}

	/**
	 * Get code of hook.
	 * @return string
	 */
	public function getCode()
	{
		$class = new \ReflectionClass($this);
		return strtoupper($class->getShortName());
	}

	/**
	 * Set data to the fields current hook.
	 * @param array $data Data array.
	 * @return void
	 */
	public function setData(array $data)
	{
		foreach ($data as $key => $value)
		{
			if (isset($this->fields[$key]))
			{
				$this->fields[$key]->setValue($value);
			}
		}
	}

	/**
	 * Get fields of current Page Hook in Page context.
	 * @return array
	 */
	public function getPageFields()
	{
		if (empty($this->fieldsPage))
		{
			foreach ($this->fields as $field)
			{
				$code = $field->getCode();
				$code = $this->getCode() . '_' . $code;
				$field->setCode($code);

				$this->fieldsPage[$code] = $field;
			}
		}

		return $this->fieldsPage;
	}

	/**
	 * Get fields of current Page Hook.
	 * @return array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Exec or not hook in edit mode.
	 * @return true
	 */
	public function enabledInEditMode()
	{
		return true;
	}

	/**
	 * Get unique hash from hook fields.
	 * @return string
	 */
	public function fieldsHash()
	{
		$hash = '';
		$hash .= implode('.', array_keys($this->fields));
		$hash .= implode('.', array_values($this->fields));
		$hash = md5($hash);

		return $hash;
	}

	/**
	 * Exist or not data in this hook.
	 * @return boolean
	 */
	public function dataExist()
	{
		return implode('', array_values($this->fields)) != '';
	}

	/**
	 * Active or not the hook.
	 * @return bool
	 */
	public function active()
	{
		return true;
	}

	/**
	 * Map of the fields.
	 * @return array
	 */
	abstract protected function getMap();

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	abstract public function enabled();

	/**
	 * Exec hook.
	 * @return void
	 */
	abstract public function exec();
}