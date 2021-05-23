<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector;

use Bitrix\Sender\Recipient;

abstract class Base
{
	protected $fieldPrefix;
	protected $fieldPrefixExtended;
	protected $fieldValues;
	protected $fieldFormName;
	protected $moduleId;

	/** @var  ResultView $resultView View of result. */
	protected $resultView;

	/** @var  integer $dataTypeId Data type ID. */
	protected $dataTypeId = Recipient\Type::EMAIL;

	/**
	 * @param string $moduleId
	 * @return void
	 */
	public function setModuleId($moduleId)
	{
		$this->moduleId = $moduleId;
	}

	/**
	 * @return mixed
	 */
	public function getModuleId()
	{
		return $this->moduleId;
	}

	/**
	 * Get data type ID.
	 *
	 * @return integer
	 */
	public function getDataTypeId()
	{
		return $this->dataTypeId;
	}

	/**
	 * Set data type ID.
	 *
	 * @param integer $dataTypeId Data type ID.
	 * @return void
	 */
	public function setDataTypeId($dataTypeId)
	{
		$this->dataTypeId = $dataTypeId;
	}

	/**
	 * @param string $fieldFormName
	 * @return void
	 */
	public function setFieldFormName($fieldFormName)
	{
		$this->fieldFormName = $fieldFormName;
	}

	/** @return string */
	public function getFieldFormName()
	{
		return $this->fieldFormName;
	}

	/**
	 * @param string $fieldPrefix
	 * @return void
	 */
	public function setFieldPrefix($fieldPrefix)
	{
		$this->fieldPrefix = $fieldPrefix;
	}
	/** @return string */
	public function getFieldPrefix()
	{
		return $this->fieldPrefix;
	}

	/**
	 * @param string $fieldPrefixExtended
	 * @return void
	 */
	public function setFieldPrefixExtended($fieldPrefixExtended)
	{
		$this->fieldPrefixExtended = $fieldPrefixExtended;
	}
	/** @return string */
	public function getFieldPrefixExtended()
	{
		return $this->fieldPrefixExtended;
	}

	/**
	 * Set field values.
	 *
	 * @param array $fieldValues Values.
	 * @return void
	 */
	public function setFieldValues(array $fieldValues = null)
	{
		$this->fieldValues = $fieldValues;
	}

	/**
	 * Get field values.
	 *
	 * @return array
	 */
	public function getFieldValues()
	{
		return is_array($this->fieldValues) ? $this->fieldValues : array();
	}

	/**
	 * Return true if it has field values.
	 *
	 * @return bool
	 */
	public function hasFieldValues()
	{
		return count($this->getFieldValues()) > 0;
	}

	/**
	 * @param $id
	 * @return string
	 */
	public function getFieldId($id)
	{
		$fieldPrefix = $this->getFieldPrefix();
		$fieldPrefixExtended = $this->getFieldPrefixExtended();
		if($fieldPrefix)
		{
			$moduleId = str_replace('.', '_', $this->getModuleId());
			return $fieldPrefix . '_' . $moduleId . '_' . $this->getCode() . '_%CONNECTOR_NUM%_' . $id;
		}
		elseif($fieldPrefixExtended)
		{
			return str_replace(array('][', '[', ']'), array('_', '', ''), $fieldPrefixExtended) .'_'. $id;
		}
		else
			return $id;
	}

	/**
	 * @param $name
	 * @return string
	 */
	public function getFieldName($name)
	{
		$fieldPrefix = $this->getFieldPrefix();
		$fieldPrefixExtended = $this->getFieldPrefixExtended();
		if($fieldPrefix || $fieldPrefixExtended)
		{
			$arReturnName = array();
			if($fieldPrefix)
				$arReturnName[] = $fieldPrefix.'['.$this->getModuleId().']['.$this->getCode().'][%CONNECTOR_NUM%]';
			else
				$arReturnName[] = $fieldPrefixExtended;

			$arName = explode('[', $name);
			$arReturnName[] = '['.$arName[0].']';
			if(count($arName)>1)
			{
				unset($arName[0]);
				$arReturnName[] = '['.implode('[', $arName);
			}

			return implode('', $arReturnName);
		}
		else
			return $name;
	}

	/**
	 * @param $name
	 * @param mixed $defaultValue
	 * @return null
	 */
	public function getFieldValue($name, $defaultValue = null)
	{
		if($this->fieldValues && array_key_exists($name, $this->fieldValues))
			return $this->fieldValues[$name];
		else
			return $defaultValue;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->getModuleId().'_'.$this->getCode();
	}

	/**
	 * Get data count.
	 *
	 * @return integer
	 */
	public function getDataCount()
	{
		return $this->getResult()->getSelectedRowsCount();
	}

	/**
	 * Get data count by type.
	 *
	 * @return null|array|DataCounter
	 */
	protected function getDataCountByType()
	{
		return null;
	}

	/**
	 * Get data counter.
	 *
	 * @return DataCounter
	 */
	final function getDataCounter()
	{
		$dataCounts = $this->getDataCountByType();
		if (is_object($dataCounts) && $dataCounts instanceof DataCounter)
		{
			return $dataCounts;
		}
		else if (!is_array($dataCounts))
		{
			$dataCounts = array($this->getDataTypeId() => $this->getDataCount());
		}

		return new DataCounter($dataCounts);
	}

	/**
	 * Get result.
	 *
	 * @return Result
	 */
	final function getResult()
	{
		$personalizeList = array();
		$personalizeListTmp = $this->getPersonalizeList();
		foreach($personalizeListTmp as $tag)
		{
			if(!empty($tag['ITEMS']))
			{
				foreach ($tag['ITEMS'] as $item)
				{
					$personalizeList[$item['CODE']] = $item['CODE'];
				}
				continue;
			}
			if(strlen($tag['CODE']) > 0)
			{
				$personalizeList[] = $tag['CODE'];
			}
		}

		$result = new Result($this->getData());
		$result->setFilterFields($personalizeList);
		$result->setDataTypeId($this->getDataTypeId());

		return $result;
	}

	/**
	 * Return true if support view of result.
	 *
	 * @return bool
	 */
	public function isResultViewable()
	{
		return false;
	}

	/**
	 * Get result view.
	 *
	 * @return ResultView
	 * @return void
	 */
	public function getResultView()
	{
		if (!$this->resultView)
		{
			$this->resultView = new ResultView($this);
			$this->onInitResultView();
		}

		return $this->resultView;
	}

	/**
	 * Set result view.
	 *
	 * @param ResultView $resultView Result view.
	 * @return void
	 */
	public function setResultView($resultView)
	{
		$this->resultView = $resultView;
	}

	protected function onInitResultView()
	{

	}

	/**
	 * @return bool
	 */
	public function requireConfigure()
	{
		return false;
	}

	/**
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return array();
	}

	/**
	 * @return string
	 */
	public abstract function getName();

	/**
	 * @return string
	 */
	public abstract function getCode();

	/**
	 *
	 *
	 * @return array|\Bitrix\Main\DB\Result|\CAllDBResult
	 */
	public abstract function getData();



	public function buildData()
	{
		return null;
	}

	/**
	 * @return string
	 */
	public abstract function getForm();

	/**
	 * Get fields for statistic
	 * @return array
	 */
	public function getStatFields()
	{
		return [];
	}
}