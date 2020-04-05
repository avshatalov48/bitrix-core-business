<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender\Connectors;

use \Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Connector\Base as ConnectorBase;
use \Bitrix\Sender\MailingTable;
use \Bitrix\Sender\MailingSubscriptionTable;

Loc::loadMessages(__FILE__);

class UnSubscribers extends ConnectorBase
{
	/**
	 * Return name
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('sender_connector_unsubscribers_name1');
	}

	/**
	 * Return code
	 * @return string
	 */
	public function getCode()
	{
		return "sender_unsubscribers";
	}

	/**
	 * Return email list
	 * @return \Bitrix\Main\DB\Result
	 */
	public function getData()
	{
		$mailingId = $this->getFieldValue('MAILING_ID', 0);
		$filter = array();
		if($mailingId)
		{
			$filter['=MAILING_ID'] = $mailingId;
		}
		$mailingDb = MailingSubscriptionTable::getUnSubscriptionList(array(
			'select' => array(
				'SENDER_CONTACT_ID' => 'CONTACT.ID',
				'EMAIL' => 'CONTACT.CODE'
			),
			'filter' => $filter,
			'group' => array('CONTACT.CODE'),
		));

		return $mailingDb;
	}

	/**
	 * Return form layout
	 * @return string
	 */
	public function getForm()
	{
		$mailingDb = MailingTable::getList(array(
			'select' => array('ID','NAME',),
			'order' => array('NAME' => 'ASC', 'ID' => 'DESC')
		));
		$mailingList = $mailingDb->fetchAll();
		$mailingList = array_merge(
			array(
				array('ID' => '', 'NAME' => Loc::getMessage('sender_connector_unsubscribers_all'))
			),
			$mailingList
		);

		$mailingInput = '<select name="'.$this->getFieldName('MAILING_ID').'">';
		foreach($mailingList as $mailing)
		{
			$inputSelected = ($mailing['ID'] == $this->getFieldValue('MAILING_ID') ? 'selected' : '');
			$mailingInput .= '<option value="'.$mailing['ID'].'" '.$inputSelected.'>';
			$mailingInput .= htmlspecialcharsbx($mailing['NAME']);
			$mailingInput .= '</option>';
		}
		$mailingInput .= '</select>';

		return '
			<table>
				<tr>
					<td>' . Loc::getMessage('sender_connector_unsubscribers_mailing') . '</td>
					<td>' . $mailingInput . '</td>
				</tr>
			</table>
		';
	}
}