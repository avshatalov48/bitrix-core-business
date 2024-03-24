<?php
namespace Bitrix\Subscribe;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SenderEventHandler
{
	/**
	 * Handler of sender:OnConnectorList event.
	 *
	 * @param array $data Empty array.
	 * @return array
	 */
	public static function onConnectorListSubscriber($data)
	{
		$data['CONNECTOR'] = 'Bitrix\Subscribe\SenderConnectorSubscriber';
		return $data;
	}
}

if (Loader::includeModule('sender'))
{
	class SenderConnectorSubscriber extends \Bitrix\Sender\Connector
	{
		/**
		 * Returns localized name of subscribers source.
		 *
		 * @return string
		 */
		public function getName()
		{
			return Loc::getMessage('sender_connector_subscriber_name');
		}

		/**
		 * Returns internal code of subscribers source.
		 *
		 * @return string
		 */
		public function getCode()
		{
			return 'subscriber';
		}

		/**
		 * Returns subscribers depended on side filter fields.
		 *
		 * @return \CDBResult
		 */
		public function getData()
		{
			$filter = [];

			$rubric = $this->getFieldValue('RUBRIC', null);
			if ($rubric)
			{
				$filter['=RUBRICS.ID'] = $rubric;
			}

			$active = $this->getFieldValue('ACTIVE', null);
			if ($active)
			{
				$filter['=ACTIVE'] = $active;
			}

			$confirmed = $this->getFieldValue('CONFIRMED', null);
			if ($confirmed)
			{
				$filter['=CONFIRMED'] = $confirmed;
			}

			$dateInsertFrom = $this->getFieldValue('DATE_INSERT_FROM', null);
			if ($dateInsertFrom)
			{
				$dateInsertFrom = \Bitrix\Main\Type\DateTime::tryParse($dateInsertFrom);
				if ($dateInsertFrom)
				{
					$dateInsertFrom->setTime(0, 0, 0);
					$filter['>=DATE_INSERT'] = $dateInsertFrom;
				}
			}

			$dateInsertTo = $this->getFieldValue('DATE_INSERT_TO', null);
			if ($dateInsertTo)
			{
				$dateInsertTo = \Bitrix\Main\Type\DateTime::tryParse($dateInsertTo);
				if ($dateInsertTo)
				{
					$dateInsertTo->setTime(23, 59, 59);
					$filter['<=DATE_INSERT'] = $dateInsertTo;
				}
			}

			$subscriberList = SubscriptionTable::getList([
				'select' => ['*', 'USER_NAME' => 'USER.NAME', 'USER_LAST_NAME' => 'USER.LAST_NAME'],
				'filter' => $filter,
				'order' => ['ID' => 'ASC'],
			]);
			$subscriberList->addFetchDataModifier([$this, 'onDataFetch']);

			return $subscriberList;
		}

		/**
		 * Modifies $fields with adding calculated NAME field.
		 *
		 * @param array &$fields Fetched data.
		 *
		 * @return array
		 */
		public function onDataFetch(&$fields)
		{
			if (isset($fields['USER_NAME']))
			{
				$fields['NAME'] = $fields['USER_NAME'];
			}

			if (!$fields['NAME'] && isset($fields['USER_LAST_NAME']))
			{
				$fields['NAME'] = $fields['USER_LAST_NAME'];
			}

			return $fields;
		}

		/**
		 * Returns Html form to display filter criteria.
		 *
		 * @return string
		 */
		public function getForm()
		{
			$dropdownValues = [
				'' => Loc::getMessage('sender_connector_subscriber_all'),
				'Y' => Loc::getMessage('sender_connector_subscriber_y'),
				'N' => Loc::getMessage('sender_connector_subscriber_n'),
			];

			$rubricInput = '<select name="' . $this->getFieldName('RUBRIC') . '">';
			$rubricList = RubricTable::getList([
				'select' => ['ID', 'NAME'],
				'order' => ['SORT' => 'ASC', 'NAME' => 'ASC'],
			]);
			while ($rubric = $rubricList->fetch())
			{
				$inputSelected = ($rubric['ID'] == $this->getFieldValue('RUBRIC') ? 'selected' : '');
				$rubricInput .= '<option value="' . $rubric['ID'] . '" ' . $inputSelected . '>';
				$rubricInput .= htmlspecialcharsEx('[' . $rubric['ID'] . '] ' . $rubric['NAME']);
				$rubricInput .= '</option>';
			}
			$rubricInput .= '</select>';

			$activeInput = '<select name="' . $this->getFieldName('ACTIVE') . '">';
			foreach ($dropdownValues as $k => $v)
			{
				$inputSelected = ($k == $this->getFieldValue('ACTIVE') ? 'selected' : '');
				$activeInput .= '<option value="' . $k . '" ' . $inputSelected . '>';
				$activeInput .= htmlspecialcharsEx($v);
				$activeInput .= '</option>';
			}
			$activeInput .= '</select>';

			$confirmedInput = '<select name="' . $this->getFieldName('CONFIRMED') . '">';
			foreach ($dropdownValues as $k => $v)
			{
				$inputSelected = ($k == $this->getFieldValue('CONFIRMED') ? 'selected' : '');
				$confirmedInput .= '<option value="' . $k . '" ' . $inputSelected . '>';
				$confirmedInput .= htmlspecialcharsEx($v);
				$confirmedInput .= '</option>';
			}
			$confirmedInput .= '</select>';

			$dateInsertInput = CalendarPeriod(
				$this->getFieldName('DATE_INSERT_FROM'),
				$this->getFieldValue('DATE_INSERT_FROM'),
				$this->getFieldName('DATE_INSERT_TO'),
				$this->getFieldValue('DATE_INSERT_TO'),
				$this->getFieldFormName()
			);

			return '
				<table>
					<tr>
						<td>' . Loc::getMessage('sender_connector_subscriber_rubric') . '</td>
						<td>' . $rubricInput . '</td>
					</tr>
					<tr>
						<td>' . Loc::getMessage('sender_connector_subscriber_active') . '</td>
						<td>' . $activeInput . '</td>
					</tr>
					<tr>
						<td>' . Loc::getMessage('sender_connector_subscriber_confirmed') . '</td>
						<td>' . $confirmedInput . '</td>
					</tr>
					<tr>
						<td>' . Loc::getMessage('sender_connector_subscriber_dateinsert') . '</td>
						<td>' . $dateInsertInput . '</td>
					</tr>
				</table>
			';
		}
	}
}

