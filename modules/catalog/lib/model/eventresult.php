<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Main;

class EventResult extends Main\Entity\EventResult
{
	public function __construct()
	{
		parent::__construct();
		$this->modified = [
			'fields' => [],
			'external_fields' => [],
			'actions' => []
		];
		$this->unset = [
			'fields' => [],
			'external_fields' => [],
			'actions' => []
		];
	}

	public function modifyFields(array $fields)
	{
		$this->modified['fields'] = $fields;
	}

	public function unsetFields(array $fields)
	{
		$this->unset['fields'] = $fields;
	}

	/**
	 * @param string $fieldName
	 */
	public function unsetField($fieldName)
	{
		$this->unset['fields'][] = $fieldName;
	}

	public function modifyExternalFields(array $fields)
	{
		$this->modified['external_fields'] = $fields;
	}

	public function unsetExternalFields(array $fields)
	{
		$this->unset['external_fields'] = $fields;
	}

	public function modifyActions(array $actions)
	{
		$this->modified['actions'] = $actions;
	}

	public function unsetActions(array $actions)
	{
		$this->unset['actions'] = $actions;
	}
}