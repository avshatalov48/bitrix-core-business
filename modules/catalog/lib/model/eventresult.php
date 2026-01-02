<?php

namespace Bitrix\Catalog\Model;

class EventResult extends \Bitrix\Main\ORM\EventResult
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

	public function modifyFields(array $fields): void
	{
		$this->modified['fields'] = $fields;
	}

	public function unsetFields(array $fields): void
	{
		$this->unset['fields'] = $fields;
	}

	/**
	 * @param string $fieldName
	 */
	public function unsetField($fieldName): void
	{
		$this->unset['fields'][] = $fieldName;
	}

	public function modifyExternalFields(array $fields): void
	{
		$this->modified['external_fields'] = $fields;
	}

	public function unsetExternalFields(array $fields): void
	{
		$this->unset['external_fields'] = $fields;
	}

	public function modifyActions(array $actions): void
	{
		$this->modified['actions'] = $actions;
	}

	public function unsetActions(array $actions): void
	{
		$this->unset['actions'] = $actions;
	}
}
