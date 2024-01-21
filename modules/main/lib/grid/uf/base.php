<?

namespace Bitrix\Main\Grid\Uf;

use Bitrix\Main\Loader;

class Base
{
	protected $entityTypeId;
	protected $userFieldsReserved = [];

	public function __construct($entityTypeId)
	{
		$this->entityTypeId = $entityTypeId;
	}

	protected function getEntityUFList()
	{
		global $USER_FIELD_MANAGER;

		static $result = [];
		if (!isset($result[$this->entityTypeId]))
		{
			$ufList = $USER_FIELD_MANAGER->getUserFields($this->entityTypeId, 0, LANGUAGE_ID, false);
			$result[$this->entityTypeId] = $this->postFilterFields($ufList);
		}

		return $result[$this->entityTypeId];
	}

	protected function getUserFieldsReserved()
	{
		return $this->userFieldsReserved;
	}

	public function addUFHeaders(&$gridHeaders, $import = false)
	{
		$userUFList = $this->getEntityUFList();

		foreach($userUFList as $FIELD_NAME => $uf)
		{
			if(
				!isset($uf['SHOW_IN_LIST'])
				|| $uf['SHOW_IN_LIST'] !== 'Y'
			)
			{
				continue;
			}

			$editable = true;
			$type = $uf['USER_TYPE']['BASE_TYPE'];

			if (
				$uf['EDIT_IN_LIST'] === 'N'
				|| $uf['MULTIPLE'] === 'Y'
				|| $uf['USER_TYPE']['BASE_TYPE'] === 'file'
				|| $uf['USER_TYPE']['USER_TYPE_ID'] === 'employee'
				|| $uf['USER_TYPE']['USER_TYPE_ID'] === 'crm'
			)
			{
				$editable = false;
			}
			elseif (in_array($uf['USER_TYPE']['USER_TYPE_ID'], ['enumeration', 'iblock_section', 'iblock_element']))
			{
				$type = 'list';
				$editable = [
					'items' => ['' => '']
				];

				if (is_callable([$uf['USER_TYPE']['CLASS_NAME'], 'GetList']))
				{
					$enumRes = call_user_func_array([$uf['USER_TYPE']['CLASS_NAME'], 'GetList'], [$uf]);
					if ($enumRes)
					{
						while ($enumFields = $enumRes->fetch())
						{
							$editable['items'][$enumFields['ID']] = $enumFields['VALUE'];
						}
					}
				}
			}
			else if ($uf['USER_TYPE']['USER_TYPE_ID'] == 'boolean')
			{
				$type = 'list';

				//Default value must be placed at first position.
				$defaultValue = (
				isset($uf['SETTINGS']['DEFAULT_VALUE'])
					? (int)$uf['SETTINGS']['DEFAULT_VALUE']
					: 0
				);

				if($defaultValue === 1)
				{
					$editable = [
						'items' => [
							'1' => GetMessage('MAIN_YES'),
							'0' => GetMessage('MAIN_NO')
						]
					];
				}
				else
				{
					$editable = [
						'items' => [
							'0' => GetMessage('MAIN_NO'),
							'1' => GetMessage('MAIN_YES')
						]
					];
				}
			}
			elseif ($uf['USER_TYPE']['BASE_TYPE'] == 'datetime')
			{
				$type = 'date';
			}
			elseif (
				$uf['USER_TYPE']['USER_TYPE_ID'] == 'crm_status'
				&& Loader::includeModule('crm')
			)
			{
				$type = 'list';
				$editable = [
					'items' => ['' => ''] + \CCrmStatus::getStatusList($uf['SETTINGS']['ENTITY_TYPE'])
				];
			}
			elseif(mb_substr($uf['USER_TYPE']['USER_TYPE_ID'], 0, 5) === 'rest_')
			{
				// skip REST type fields here
				continue;
			}

			if($type === 'string')
			{
				$type = 'text';
			}
			elseif(
				$type === 'int'
				|| $type === 'double'
			)
			{
				//HACK: \CMainUIGrid::prepareEditable does not recognize 'number' type
				$type = 'int';
			}

			if (!empty($uf['LIST_COLUMN_LABEL']))
			{
				$name = htmlspecialcharsbx($uf['LIST_COLUMN_LABEL']);
			}
			elseif (!empty(['EDIT_FORM_LABEL']))
			{
				$name = htmlspecialcharsbx($uf['EDIT_FORM_LABEL']);
			}
			else
			{
				$name = htmlspecialcharsbx($FIELD_NAME);
			}

			$gridHeaders[$FIELD_NAME] = array(
				'id' => $FIELD_NAME,
				'name' => $name,
				'sort' => $uf['MULTIPLE'] == 'N' ? $FIELD_NAME : false,
				'default' => $uf['SHOW_IN_LIST'] == 'Y',
				'editable' => $editable,
				'type' => $type
			);

			if ($import)
			{
				$gridHeaders[$FIELD_NAME]['mandatory'] = (
					$uf['MANDATORY'] === 'Y'
						? 'Y'
						: 'N'
				);
			}
		}

	}

	protected function postFilterFields(array $fields)
	{
		foreach ($this->getUserFieldsReserved() as $ufId)
		{
			if (isset($fields[$ufId]))
			{
				unset($fields[$ufId]);
			}
		}

		return $fields;
	}

}