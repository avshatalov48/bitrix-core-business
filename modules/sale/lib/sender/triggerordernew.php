<?

namespace Bitrix\Sale\Sender;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if (!Loader::includeModule('sender'))
{
	return;
}

Loc::loadMessages(__FILE__);

class TriggerOrderNew extends \Bitrix\Sender\TriggerConnector
{

	public function getName()
	{
		return Loc::getMessage('sender_trigger_order_new_name');
	}

	public function getCode()
	{
		return "order_new";
	}

	public function getEventModuleId()
	{
		return 'sale';
	}

	public function getEventType()
	{
		return "OnOrderAdd";
	}

	public function getConnector()
	{
		$connector = new \Bitrix\Sale\Sender\ConnectorOrder;
		$connector->setModuleId('sale');

		return $connector;
	}

	/** @return array */
	public function getProxyFieldsFromEventToConnector()
	{
		$eventData = $this->getParam('EVENT');
		return array('ID' => $eventData[0], 'LID' => $this->getSiteId());
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getPersonalizeFields()
	{
		$eventData = $this->getParam('EVENT');
		$result = ['ORDER_ID' => $eventData[0]];
		if ((int)$eventData[0] <= 0)
			return $result;

		$order = \Bitrix\Sale\Order::load($eventData[0]);
		if ($order)
		{
			$result = [
				'ORDER_ID' => $order->getField('ACCOUNT_NUMBER'),
				'ORDER_REAL_ID' => $order->getId()
			];
		}
		return $result;
	}

	/**
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return array(
			array(
				'CODE' => 'ORDER_ID',
				'NAME' => Loc::getMessage('sender_trigger_order_new_pers_order_id_name'),
				'DESC' => Loc::getMessage('sender_trigger_order_new_pers_order_id_desc')
			)
		);
	}

	public function getForm()
	{
		return '';
	}

}