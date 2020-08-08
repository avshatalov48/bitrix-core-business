<?php


namespace Bitrix\Sale\Exchange\Integration\Controller;


use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\OrderTable;
use Bitrix\Sale\Result;

Loc::loadMessages(__FILE__);

class Stepper extends Controller
{
	const BATCH_LENGTH = 50;

	public function activityBatchAction(array $list, $total=0, $start=0)
	{
		$batch = array_slice($list, 0, static::BATCH_LENGTH, true);
		$slice = array_slice($list, static::BATCH_LENGTH, null, true);

		$start += count($batch);

		$b = $this->getUnprocessedItems($batch);
		$batch = $b->getData();

		foreach (array_keys($batch) as $orderId)
		{
			OrderTable::update($orderId, ['IS_SYNC_B24' => 'Y']);
		}

		$result['progress'] = round((100*$start)/$total);

		$result['process'] = [
			'items' => $batch,
			'list' => $slice,
			'total' => $total,
			'start' => $start
		];

		if($start == $total)
		{
			$result['finish'] = true;
		}

		if(count($b->getErrorMessages())>0)
		{
			$result['error'] = implode("<br>", $b->getErrorMessages());
		}
		return $result;
	}

	public function progressBarAction($value)
	{
		require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

		ob_start();

		$message = new \CAdminMessage('');
		$message->ShowMessage(array(
			"TYPE" => "PROGRESS",
			"DETAILS" => '#PROGRESS_BAR#'.
				'<div class="adm-loc-ri-statusbar">'.Loc::getMessage('SALE_ORDER_REQUEST_STATUS').': <span class="bx-ui-loc-ri-loader"></span>&nbsp;<span class="bx-ui-loc-ri-status-text">'.Loc::getMessage('SALE_ORDER_REQUEST_STATUS_PROCESS').'</span></div>',
			"HTML" => true,
			"PROGRESS_TOTAL" => 100,
			"PROGRESS_VALUE" => $value,
			"PROGRESS_TEMPLATE" => '<span class="bx-ui-loc-ri-percents">#PROGRESS_VALUE#</span>%'
		));
		$res = ob_get_clean();
		return $res;
	}

	public function messageOKAction()
	{
		return $this->messageByTypeAction(Loc::getMessage('SALE_ORDER_REQUEST_ORDER_IDS_OK'),'OK');
	}

	public function messageByTypeAction($message, $type)
	{
		require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

		if($type == 'ERROR')
		{
			$adminMessage = new \CAdminMessage(
				array(
					"DETAILS" => $message,
					"TYPE" => "ERROR",
					"HTML" => true
				)
			);
		}
		else
		{
			$adminMessage = new \CAdminMessage(
				array(
					"DETAILS" => $message,
					"TYPE" => "OK",
					"HTML" => true
				)
			);
		}

		return $adminMessage->Show();
	}

	static protected function getInActiveOrders($orderIds)
	{
		$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var \Bitrix\Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		$r=[];
		if(count($orderIds)>0)
		{
			$list = $orderClass::getList([
				'select'=>['ID'],
				'filter'=>['ID'=>$orderIds, 'IS_SYNC_B24'=>'Y']
			])->fetchAll();

			if(count($list)>0)
			{
				foreach ($list as $l)
				{
					$r[] = $l['ID'];
				}
			}
		}

		return $r;
	}

	protected function getUnprocessedItems($items)
	{
		$result = new Result();
		$list = [];
		$orderIds = array_keys($items);

		$inActiveOrders = static::getInActiveOrders($orderIds);

		foreach ($items as $index => $item)
		{
			$r = $this->checkInActiveOrder($index, $inActiveOrders);
			if($r->isSuccess())
			{
				$list[$index] = $item;
			}
			else
			{
				$result->addError(new Error(Loc::getMessage('SALE_ORDER_APP_REST_SENDER_ORDER_ERROR').$index.' '.implode(', ', $r->getErrorMessages())));
			}
		}

		$result->setData($list);
		return $result;
	}

	protected function checkInActiveOrder($id, $list)
	{
		$r = new \Bitrix\Sale\Result();

		if(count($list)>0)
		{
			if(in_array($id, $list))
			{
				$r->addError(new Error(Loc::getMessage('SALE_ORDER_APP_REST_SENDER_Y')));
			}
		}

		return $r;
	}
}