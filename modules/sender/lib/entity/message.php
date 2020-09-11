<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Entity;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\Model\MessageFieldTable;
use Bitrix\Sender\Internals\Model\MessageTable;
use Bitrix\Sender\Message\Configuration;
use Bitrix\Sender\Message\Result;

Loc::loadMessages(__FILE__);

/**
 * Class Message
 * @package Bitrix\Sender\Entity
 */
class Message extends Base
{
	/**
	 * Load configuration.
	 *
	 * @param integer|null $id ID.
	 * @param Configuration $configuration Configuration.
	 * @return Configuration
	 */
	public function loadConfiguration($id = null, Configuration $configuration = null)
	{
		if (!$configuration)
		{
			$configuration = new Configuration;
		}

		if ($id && $this->load($id))
		{
			$data = $this->getFields();
			foreach ($configuration->getOptions() as $option)
			{
				$key = $option->getCode();
				$value = isset($data[$key]) ? $data[$key] : null;
				if ($option->getType() === $option::TYPE_FILE)
				{
					$value = ($value <> '') ? explode(',', $value) : $value;
				}

				$configuration->set($key, $value);
			}

			$configuration->setId($id);
		}

		return $configuration;
	}

	/**
	 * Save configuration.
	 *
	 * @param Configuration $configuration Configuration.
	 * @return \Bitrix\Main\Result
	 */
	public function saveConfiguration(Configuration $configuration)
	{
		$this->setId($configuration->getId());
		$result = $configuration->checkOptions();
		if (!$result->isSuccess())
		{
			return $result;
		}

		$data = array();
		foreach ($configuration->getOptions() as $option)
		{
			$value = $option->getValue();
			if ($option->getType() === $option::TYPE_FILE)
			{
				$value = is_array($value) ? implode(',', $value) : $value;
			}

			$data[] = array(
				'CODE' => $option->getCode(),
				'TYPE' => $option->getType(),
				'VALUE' => $value,
			);
		}

		if (count($data) == 0)
		{
			$result->addError(new Error('No options.'));
		}

		$this->setFields($data)->save();

		if ($this->hasErrors())
		{
			$result->addErrors($this->errors->toArray());
		}
		else
		{
			$configuration->setId($this->getId());
		}

		return $result;
	}

	/**
	 * Copy configuration.
	 *
	 * @param integer|string|null $id ID.
	 * @return Result|null
	 */
	public function copyConfiguration($id)
	{
		$copiedId = $this->copyData($id);
		$result = new Result();
		$result->setId($copiedId);

		return $result;
	}

	/**
	 * Remove configuration.
	 *
	 * @param integer $id ID.
	 * @return bool
	 */
	public function removeConfiguration($id)
	{
		$result = static::removeById($id);
		return $result->isSuccess();
	}

	/**
	 * Remove by ID.
	 *
	 * @param integer $id ID.
	 * @return \Bitrix\Main\Result
	 */
	public static function removeById($id)
	{
		return MessageTable::delete($id);
	}

	/**
	 * Get fields.
	 */
	public function getFields()
	{
		$result = array();
		$data = $this->getData();
		foreach ($data['FIELDS'] as $field)
		{
			$result[$field['CODE']] = $field['VALUE'];
		}

		return $result;
	}

	/**
	 * Set fields.
	 *
	 * @param array $fields Fields.
	 * @return $this
	 */
	public function setFields(array $fields)
	{
		$this->set('FIELDS', $fields);
		return $this;
	}

	/**
	 * Get code.
	 */
	public function getCode()
	{
		return $this->get('CODE');
	}

	/**
	 * Set code.
	 *
	 * @param string $code Code.
	 * @return $this
	 */
	public function setCode($code)
	{
		return $this->set('CODE', $code);
	}

	/**
	 * Get default data.
	 *
	 * @return array
	 */
	protected function getDefaultData()
	{
		return array(
			'CODE' => '',
			'FIELDS' => array(),
		);
	}

	/**
	 * Load data.
	 *
	 * @param integer $id ID.
	 * @return array|null
	 */
	protected function loadData($id)
	{
		$data = MessageTable::getRowById($id);
		if (!is_array($data))
		{
			return null;
		}
		if ($this->getCode() && $this->getCode() != $data['CODE'])
		{
			return null;
		}

		$data['FIELDS'] = array();
		$fieldsDb = MessageFieldTable::getList(array(
			'select' => array('TYPE', 'CODE', 'VALUE'),
			'filter'=>array(
				'=MESSAGE_ID'=> $id
			)
		));
		while($field = $fieldsDb->fetch())
		{
			$data['FIELDS'][] = $field;
		}

		return $data;
	}

	protected function parsePersonalizeList($text)
	{

	}

	/**
	 * Save data.
	 *
	 * @param integer|null $id ID.
	 * @param array $data Data.
	 * @return integer|null
	 */
	protected function saveData($id = null, array $data)
	{
		$fields = $data['FIELDS'];
		unset($data['FIELDS']);

		if(!is_array($fields) && count($fields) == 0)
		{
			$this->addError('No message fields.');
			return $id;
		}

		$id = $this->saveByEntity(MessageTable::getEntity(), $id, $data);
		if ($this->hasErrors())
		{
			return $id;
		}

		MessageFieldTable::deleteByMessageId($id);
		foreach ($fields as $field)
		{
			if(in_array($field['CODE'], ['MESSAGE_PERSONALIZE', 'SUBJECT_PERSONALIZE']))
			{
				continue;
			}

			if(in_array($field['CODE'], ['MESSAGE', 'SUBJECT']))
			{

				preg_match_all("/#([0-9a-zA-Z_.|]+?)#/", $field['VALUE'], $matchesFindPlaceHolders);
				$matchesFindPlaceHoldersCount = count($matchesFindPlaceHolders[1]);
				if($matchesFindPlaceHoldersCount > 0)
				{
					$list = json_encode($matchesFindPlaceHolders);
					MessageFieldTable::add(
						[
							'MESSAGE_ID' => $id,
							'TYPE'       => $field['TYPE'],
							'CODE'       => $field['CODE'].'_PERSONALIZE',
							'VALUE'      => $list
						]
					);

				}
			}
			MessageFieldTable::add(array(
				'MESSAGE_ID' => $id,
				'TYPE' => $field['TYPE'],
				'CODE' => $field['CODE'],
				'VALUE' => $field['VALUE']
			));
		}


		return $id;
	}
}