<?php

class CPerfomanceSchema
{
	public $data_relations = null;
	public $data_actions = null;
	public $data_attributes = null;

	public function addModuleSchema(array $arModuleSchema)
	{
		foreach ($arModuleSchema as $module_id => $arModuleTables)
		{
			if (!array_key_exists($module_id, $this->data_relations))
			{
				$this->data_relations[$module_id] = [];
			}

			foreach ($arModuleTables as $parent_table_name => $arParentColumns)
			{
				if (!array_key_exists($parent_table_name, $this->data_relations[$module_id]))
				{
					$this->data_relations[$module_id][$parent_table_name] = [];
				}

				foreach ($arParentColumns as $parent_column => $arChildren)
				{
					if ($parent_column === '~actions')
					{
						if (!array_key_exists($module_id, $this->data_actions))
						{
							$this->data_actions[$module_id] = [];
						}
						if (!array_key_exists($parent_table_name, $this->data_actions[$module_id]))
						{
							$this->data_actions[$module_id][$parent_table_name] = [];
						}
						$this->data_actions[$module_id][$parent_table_name] = array_merge(
							$this->data_actions[$module_id][$parent_table_name],
							$arChildren
						);
					}
					else
					{
						if (!array_key_exists($parent_column, $this->data_relations[$module_id][$parent_table_name]))
						{
							$this->data_relations[$module_id][$parent_table_name][$parent_column] = [];
						}

						foreach ($arChildren as $child_table_name => $child_column)
						{
							if (preg_match('#^~(.+)$#', $child_table_name, $m))
							{
								$this->data_attributes[$module_id][$parent_table_name][$parent_column][$m[1]] = $child_column;
							}
							else
							{
								$this->data_relations[$module_id][$parent_table_name][$parent_column][$child_table_name] = $child_column;
							}
						}
					}
				}
			}
		}
	}

	public function Init()
	{
		if (!isset($this->data_relations))
		{
			$this->data_relations = [];
			$this->data_actions = [];
			$this->data_attributes = [];
			foreach (GetModuleEvents('perfmon', 'OnGetTableSchema', true) as $arEvent)
			{
				$arModuleSchema = ExecuteModuleEventEx($arEvent);
				if (is_array($arModuleSchema))
				{
						$this->addModuleSchema($arModuleSchema);
				}
			}
		}
	}

	public function GetAttributes($table_name)
	{
		$this->Init();
		foreach ($this->data_attributes as $arModuleTables)
		{
			if (isset($arModuleTables[$table_name]))
			{
				return $arModuleTables[$table_name];
			}
		}
		return [];
	}

	public function GetRowActions($table_name)
	{
		$this->Init();
		foreach ($this->data_actions as $arModuleTables)
		{
			if (isset($arModuleTables[$table_name]))
			{
				return $arModuleTables[$table_name];
			}
		}
		return [];
	}

	public function GetChildren($table_name)
	{
		$this->Init();
		$result = [];
		foreach ($this->data_relations as $arModuleTables)
		{
			if (array_key_exists($table_name, $arModuleTables))
			{
				$key = $table_name;
			}
			elseif (array_key_exists(mb_strtolower($table_name), $arModuleTables))
			{
				$key = mb_strtolower($table_name);
			}
			elseif (array_key_exists(mb_strtoupper($table_name), $arModuleTables))
			{
				$key = mb_strtoupper($table_name);
			}
			else
			{
				$key = '';
			}

			if ($key)
			{
				foreach ($arModuleTables[$key] as $parent_column => $arChildren)
				{
					foreach ($arChildren as $child_table_name => $child_column)
					{
						$result[] = [
							'PARENT_COLUMN' => $parent_column,
							'CHILD_TABLE' => trim($child_table_name, '^'),
							'CHILD_COLUMN' => $child_column,
						];
					}
				}
			}
		}

		uasort($result, ['CPerfomanceSchema', '_sort']);
		return $result;
	}

	public function GetParents($table_name)
	{
		$this->Init();
		$result = [];
		foreach ($this->data_relations as $arModuleTables)
		{
			foreach ($arModuleTables as $parent_table_name => $arParentColumns)
			{
				foreach ($arParentColumns as $parent_column => $arChildren)
				{
					foreach ($arChildren as $child_table_name => $child_column)
					{
						$child_table_name = trim($child_table_name, '^');
						if (
							$child_table_name === $table_name
							|| $child_table_name === mb_strtolower($table_name)
							|| $child_table_name === mb_strtoupper($table_name)
						)
						{
							$result[$child_column] = [
								'PARENT_TABLE' => $parent_table_name,
								'PARENT_COLUMN' => $parent_column,
							];
						}
					}
				}
			}
		}

		uasort($result, ['CPerfomanceSchema', '_sort']);
		return $result;
	}

	private function _sort($a, $b)
	{
		if (isset($a['CHILD_TABLE']))
		{
			return strcmp($a['CHILD_TABLE'], $b['CHILD_TABLE']);
		}
		else
		{
			return strcmp($a['PARENT_TABLE'], $b['PARENT_TABLE']);
		}
	}
}
