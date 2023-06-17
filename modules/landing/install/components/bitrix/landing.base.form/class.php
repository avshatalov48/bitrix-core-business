<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingBaseFormComponent extends LandingBaseComponent
{
	/**
	 * Current element id.
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Class of current element.
	 * @var string
	 */
	protected $class = null;

	/**
	 * Page after success save.
	 * @var string
	 */
	protected $successSavePage = '';

	/**
	 * Redirect on $successSavePage after save.
	 * @var bool
	 */
	protected $redirectAfterSave = false;

	/**
	 * POST key.
	 * @var string
	 */
	protected $postCode = 'fields';

	/**
	 * POST fields.
	 * @var null
	 */
	protected $postFields = null;

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap()
	{
		return array();
	}

	/**
	 * Get some var from request.
	 * @param string $var Code of var.
	 * @param bool $strict Strict check of var.
	 * @return mixed
	 */
	public function request($var, $strict = false)
	{
		if ($this->postFields === null)
		{
			$this->postFields = parent::request($this->postCode);
			if ($this->postFields === '')
			{
				$this->postFields = null;
			}
		}

		if (is_array($this->postFields))
		{
			if ($strict)
			{
				if (array_key_exists($var, $this->postFields))
				{
					return $this->postFields[$var];
				}
				return false;
			}
			return (isset($this->postFields[$var]) ? $this->postFields[$var] : '');
		}
		else
		{
			return parent::request($var);
		}
	}

	/**
	 * Current form is saving now.
	 * @return boolean
	 */
	protected function isSaving()
	{
		static $result = null;

		if ($result === null)
		{
			$result = false;
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$server = $context->getServer();
			if (
				$server->getRequestMethod() == 'POST' &&
				$this->request('SAVE_FORM') == 'Y'
			)
			{
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Fill or not row data from Request.
	 * @return boolean
	 */
	protected function fillFromRequest()
	{
		return $this->isSaving();
	}

	/**
	 * Allowed or not additional fields for this form.
	 * @return boolean
	 */
	protected function additionalFieldsAllowed()
	{
		return false;
	}

	/**
	 * Get additional fields, which must be un-wafed.
	 * @return array
	 */
	protected function getAdditionalFieldsRaw()
	{
		return [
			'HEADBLOCK_CODE', 'HEADBLOCK_CSS_CODE',
			'METAGOOGLEVERIFICATION_META',
			'METAYANDEXVERIFICATION_META'
		];
	}

	/**
	 * Get additional fields values.
	 * @return array
	 */
	protected function getAdditionalFieldsValue()
	{
		$additionalFieldsParent = $this->request('ADDITIONAL_FIELDS_PARENT');
		$additionalFields = $this->request('ADDITIONAL_FIELDS');

		// bugfix for security waf
		if (is_array($additionalFields))
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$request = $context->getRequest();
			$postList = $request->getPostList()->getRaw($this->postCode);
			if (isset($postList['ADDITIONAL_FIELDS']))
			{
				$postList = $postList['ADDITIONAL_FIELDS'];
				foreach ($this->getAdditionalFieldsRaw() as $code)
				{
					if (isset($postList[$code]))
					{
						$additionalFields[$code] = $postList[$code];
					}
				}
			}
		}

		// detect groups which different
		$diffGroups = array();
		if (is_array($additionalFields))
		{
			foreach ($additionalFields as $key => $value)
			{
				$group = mb_substr($key, 0, mb_strpos($key, '_'));
				if (
					!in_array($group, $diffGroups) &&
					isset($additionalFieldsParent[$key]) &&
					trim($additionalFieldsParent[$key]) != trim($value)
				)
				{
					$diffGroups[] = $group;
				}
			}
		}

		// delete from child form duplicate values
		if (is_array($additionalFieldsParent))
		{
			foreach ($additionalFieldsParent as $key => $value)
			{
				$group = mb_substr($key, 0, mb_strpos($key, '_'));
				if (
					!in_array($group, $diffGroups) &&
					isset($additionalFields[$key]) &&
					trim($additionalFields[$key]) == trim($value)
				)
				{
					$additionalFields[$key] = '';
				}
			}
		}

		return $additionalFields;
	}

	/**
	 * Gets rights value for saving.
	 * @param bool $integer Return task id in integer vals.
	 * @return array
	 */
	protected function getRightsValue($integer = false)
	{
		$return = [];
		$rights = $this->request('RIGHTS');

		if (
			isset($rights['ACCESS_CODE']) && is_array($rights['ACCESS_CODE']) &&
			isset($rights['TASK_ID']) && is_array($rights['TASK_ID'])
		)
		{
			$tasks = $this->getAccessTasks();
			foreach ($rights['TASK_ID'] as $k => $taskIds)
			{
				foreach ((array) $taskIds as $taskId)
				{
					if (
						isset($tasks[$taskId]) &&
						isset($rights['ACCESS_CODE'][$k])
					)
					{
						if (!isset($return[$rights['ACCESS_CODE'][$k]]))
						{
							$return[$rights['ACCESS_CODE'][$k]] = [];
						}

						$return[$rights['ACCESS_CODE'][$k]][] = $integer
							? $tasks[$taskId]['ID']
							: $tasks[$taskId]['NAME'];
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Fill default fields from exist another row.
	 * @return int
	 */
	protected function getCopyId()
	{
		return (int)$this->request('copy');
	}

	/**
	 * Get hooks for current entity.
	 * @param string|bool $class Get hooks from this class.
	 * @param int|bool $id Get hooks from this entity id.
	 * @return array
	 */
	protected function getHooks($class = false, $id = false)
	{
		$hooks = array();

		if ($id === false)
		{
			$id = $this->id;
		}
		if ($class === false)
		{
			$class = $this->class;
		}
		$classFull = $this->getValidClass($class);

		if (
			$classFull &&
			method_exists($classFull, 'getHooks')
		)
		{
			\Bitrix\Landing\Hook::setEditMode();
			$hooks = $classFull::getHooks($id);
			$requestAdditional = $this->getAdditionalFieldsValue();
			foreach ($hooks as $hook)
			{
				foreach ($hook->getPageFields() as $field)
				{
					if (isset($requestAdditional[$field->getCode()]))
					{
						$field->setValue(
							$requestAdditional[$field->getCode()]
						);
					}
				}
			}
		}

		return $hooks;
	}

	/**
	 * Get current item from table (or request) with map description.
	 * @return array
	 */
	protected function getRow()
	{
		$item = array();
		$classFull = $this->getValidClass($this->class);

		if ($classFull)
		{
			if ($this->id > 0 || $this->getCopyId())
			{
				$row = $this->getItems($this->class, array(
					'filter' => array(
						'ID' => $this->id > 0
								? $this->id
								: $this->getCopyId()
						)
					)
				);
				if ($row)
				{
					$row = array_shift($row);
				}
			}

			// get map of class
			$localMap = $this->getMap();
			$fillFromRequest = $this->fillFromRequest();
			foreach ($classFull::getMap() as $code => $field)
			{
				$defaultValue = method_exists($field, 'getDefaultValue')
								? $field->getDefaultValue()
								: '';
				$item[$code] = array(
					'TITLE' => $field->getTitle(),
					'READONLY' => !in_array($code, $localMap),
					'STORED' => $row[$code] ?? $defaultValue,
					'~CURRENT' => $fillFromRequest
								? ($code == 'ID') ? $this->id : $this->request($code)
								: (isset($row[$code]) ? $row[$code] : $defaultValue)
				);
				if (is_array($item[$code]['~CURRENT']))
				{
					$item[$code]['CURRENT'] = $item[$code]['~CURRENT'];
				}
				else
				{
					$item[$code]['CURRENT'] = \htmlspecialcharsbx($item[$code]['~CURRENT']);
				}
			}
		}

		return $item;
	}

	/**
	 * Save current item.
	 * @return boolean
	 */
	protected function updateRow()
	{
		$fields = array();
		$classFull = $this->getValidClass($this->class);
		// check common errors
		if (!$classFull)
		{
			return false;
		}
		if (!check_bitrix_sessid())
		{
			$this->addError('LANDING_ERROR_SESS_EXPIRED');
			return false;
		}
		// collect fields
		foreach ($this->getRow() as $code => $field)
		{
			if (!$field['READONLY'])
			{
				$fields[$code] = $field['~CURRENT'];
			}
		}
		// add/update
		if (!empty($fields))
		{
			if ($this->additionalFieldsAllowed())
			{
				$fields['ADDITIONAL_FIELDS'] = $this->getAdditionalFieldsValue();
			}
			if ($this->id > 0)
			{
				$res = $classFull::update($this->id, $fields);
			}
			else
			{
				$res = $classFull::add($fields);
			}
			if ($res->isSuccess())
			{
				$this->id = $res->getId();
				return true;
			}
			else
			{
				$this->addErrorFromResult($res);
			}
		}
		return false;
	}

	/**
	 * Delete current item.
	 * @return boolean
	 */
	protected function deleteRow()
	{
		$classFull = $this->getValidClass($this->class);
		// check common errors
		if (!$classFull)
		{
			return false;
		}
		if (!check_bitrix_sessid())
		{
			$this->addError('LANDING_ERROR_SESS_EXPIRED');
			return false;
		}
		// delete
		$res = $classFull::delete($this->id);
		if ($res->isSuccess())
		{
			return true;
		}
		else
		{
			$this->addErrorFromResult($res);
			return false;
		}
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if (
			$this->init() &&
			!($this->arResult['FATAL'] ?? false)
		)
		{
			$this->arParams['SUCCESS_SAVE'] = false;
			// add / update
			if (
				$this->isSaving() &&
				$this->updateRow()
			)
			{
				$this->arParams['SUCCESS_SAVE'] = true;
				if (
					$this->redirectAfterSave &&
					$this->successSavePage
				)
				{
					\localRedirect($this->successSavePage);
				}
			}
		}

		parent::executeComponent();
	}
}