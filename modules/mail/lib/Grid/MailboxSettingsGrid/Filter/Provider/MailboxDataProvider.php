<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Filter\Provider;

use Bitrix\Mail\Grid\MailboxSettingsGrid\Filter\MailboxFilterSettings;
use Bitrix\Main\Filter\EntityDataProvider;
use Bitrix\Main\Filter\Field;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\DateType;

class MailboxDataProvider extends EntityDataProvider
{
	private MailboxFilterSettings $settings;

	public function __construct(MailboxFilterSettings $settings)
	{
		$this->settings = $settings;
	}

	public function getSettings(): MailboxFilterSettings
	{
		return $this->settings;
	}

	/**
	 * @param string $fieldID Field ID.
	 */
	public function prepareFieldData($fieldID): array|null
	{
		$result = null;

		if (in_array($fieldID, ['OWNER', 'ACCESS_USERS', 'CRM_QUEUE']))
		{
			$result = [
				'params' => [
					'apiVersion' => 3,
					'context' => 'MAILBOX_LIST_FILTER_' . $fieldID,
					'multiple' => 'Y',
					'contextCode' => 'U',
					'enableDepartments' => 'N',
					'enableAll' => 'N',
					'enableUsers' => 'Y',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
					'isNumeric' => 'N',
				],
			];
		}

		return $result;
	}

	/**
	 * @param string $fieldID Field ID.
	 */
	protected function getFieldName($fieldID): string
	{
		$name = Loc::getMessage("MAIL_MAILBOX_GRID_FILTER_{$fieldID}");

		if ($name === null)
		{
			$name = $fieldID;
		}

		return $name;
	}

	/**
	 * @return Field[]
	 */
	public function prepareFields(): array
	{
		$result = [];

		$whiteList = $this->getSettings()->getWhiteList();

		$fieldsList = [
			'EMAIL' => [
				'whiteList' => ['EMAIL'],
				'options' => ['default' => true],
			],
			'OWNER' => [
				'whiteList' => ['OWNER'],
				'options' => ['default' => true, 'type' => 'dest_selector', 'partial' => true],
			],
			'LAST_SYNC' => [
				'whiteList' => ['LAST_SYNC'],
				'options' => [
					'default' => true,
					'type' => 'date',
					'exclude' => [
						DateType::TOMORROW,
						DateType::NEXT_DAYS,
						DateType::NEXT_WEEK,
						DateType::NEXT_MONTH,
						DateType::LAST_MONTH,
						DateType::LAST_WEEK,
						DateType::EXACT,
						DateType::RANGE,
					],
				],
			],
			'CRM_INTEGRATION' => [
				'whiteList' => ['CRM_INTEGRATION'],
				'options' => ['default' => true, 'type' => 'checkbox'],
			],
			'ACCESS_USERS' => [
				'whiteList' => ['ACCESS_USERS'],
				'options' => ['default' => true, 'type' => 'dest_selector', 'partial' => true],
			],
			'CRM_QUEUE' => [
				'whiteList' => ['CRM_QUEUE'],
				'options' => ['default' => true, 'type' => 'dest_selector', 'partial' => true],
			],
			'DISK_SIZE' => [
				'whiteList' => ['DISK_SIZE'],
				'options' => ['default' => true, 'type' => 'number'],
			],
			'SENDER_NAME' => [
				'whiteList' => ['SENDER_NAME'],
				'options' => ['default' => true],
			],
		];

		foreach ($fieldsList as $column => $field)
		{
			$whiteListPassed = false;
			if (
				!empty($field['conditionMethod'])
				&& is_callable($field['conditionMethod'])
			)
			{
				$whiteListPassed = call_user_func_array($field['conditionMethod'], []);
			}
			elseif (
				empty($whiteList)
				|| empty($field['whiteList'])
			)
			{
				$whiteListPassed = true;
			}
			else
			{
				foreach ($field['whiteList'] as $whiteListField)
				{
					if (in_array($whiteListField, $whiteList, true))
					{
						$whiteListPassed = true;

						break;
					}
				}
			}

			if ($whiteListPassed)
			{
				$result[$column] = $this->createField(
					$column,
					!empty($field['options']) ? $field['options'] : [],
				);
			}
		}

		return $result;
	}
}
