<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Sender\Integration\MessageService\Sms\Service;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderSmsSenderComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? $this->arParams['INPUT_NAME'] : 'SENDER';
		$this->arParams['SENDER_ID'] = isset($this->arParams['SENDER_ID']) ? $this->arParams['SENDER_ID'] : null;
		$this->arParams['FROM_NUMBER'] = isset($this->arParams['FROM_NUMBER']) ? $this->arParams['FROM_NUMBER'] : null;
		if (isset($this->arParams['SENDER']) && $this->arParams['SENDER'] && !$this->arParams['SENDER_ID'])
		{
			$senderList = explode(':', $this->arParams['SENDER']);
			$this->arParams['SENDER_ID'] = $senderList[0];
			$this->arParams['FROM_NUMBER'] = $senderList[1];
		}
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URL'] = $this->getPath() . '/ajax.php';
		$this->arResult['MANAGE_URL'] = Service::getManageUrl();

		$currentSenderId = $this->arParams['SENDER_ID'];
		$currentId = null;
		if ($this->arParams['SENDER_ID'] && $this->arParams['FROM_NUMBER'])
		{
			$currentId = $this->arParams['SENDER_ID'] . ':' . $this->arParams['FROM_NUMBER'];
		}

		$this->arResult['LIST'] = array();
		$this->arResult['CURRENT'] = array();
		foreach (Service::getProviders() as $item)
		{
			if ($item['id'] == 'rest')
			{
				foreach ($item['from'] as $number)
				{
					$from = $item['id'] . ':' . $number['id'];
					$row = array(
						'senderId' => $item['id'],
						'name' => $number['name'],
						'shortName' => $number['name'],
						'isConfigurable' => $item['isConfigurable'],
						'canUse' => $item['canUse'],
						'manageUrl' => $item['manageUrl'],
						'data' => array(
							'list' => array(
								array(
									'id' => $from,
									'name' => 'Unnamed',
									'selected' => $currentId == $from,
								)
							),
							'isHidden' => true
						),
						'selected' => $currentSenderId == $item['id'],
					);

					$this->arResult['LIST'][] = $row;
				}
			}
			else
			{
				$fromList = array();
				foreach ($item['from'] as $fromItem)
				{
					$fromItem['id'] = $item['id'] . ':' . $fromItem['id'];
					$fromItem['selected'] = $currentId == $fromItem['id'];
					$fromList[] = $fromItem;
				}

				$row = array(
					'senderId' => $item['id'],
					'name' => $item['name'],
					'shortName' => $item['shortName'],
					'isConfigurable' => $item['isConfigurable'],
					'canUse' => $item['canUse'],
					'manageUrl' => $item['manageUrl'],
					'data' => array(
						'list' => $fromList,
						'isHidden' => false,
					),
					'selected' => $currentSenderId == $item['id'],
				);

				$this->arResult['LIST'][] = $row;
			}
		}

		if (count($this->arResult['CURRENT']) == 0)
		{
			foreach ($this->arResult['LIST'] as $item)
			{
				if ($currentSenderId && $currentSenderId != $item['senderId'])
				{
					continue;
				}

				$this->arResult['CURRENT'] = $item;
				break;
			}
		}

		if (!$this->arParams['SENDER'])
		{
			$this->arParams['SENDER'] = '';
			if ($this->arResult['CURRENT']['SENDER_ID'] && $this->arResult['CURRENT']['data']['list'][0]['id'])
			{
				$this->arParams['SENDER'] = $this->arResult['CURRENT']['SENDER_ID'];
				$this->arParams['SENDER'] .= ':';
				$this->arParams['SENDER'] .= $this->arResult['CURRENT']['data']['list'][0]['id'];
			}
		}

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}