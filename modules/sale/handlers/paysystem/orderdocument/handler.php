<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale;
use Bitrix\Main;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\DocumentGenerator;
use Bitrix\Crm\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class OrderDocumentHandler
 * @package Sale\Handlers\PaySystem
 */
class OrderDocumentHandler extends PaySystem\BaseServiceHandler
{
	/**
	 * @return string
	 */
	protected static function getDataProviderClass()
	{
		return Integration\DocumentGenerator\DataProvider\Order::class;
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function preInitiatePay(Payment $payment, Request $request = null)
	{
		$this->getDocument($payment);
	}

	/**
	 * @param Sale\Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 */
	public function initiatePay(Sale\Payment $payment, Request $request = null)
	{
		if (!Main\Loader::includeModule('documentgenerator')
			||
			!Main\Loader::includeModule('crm')
		)
		{
			return new PaySystem\ServiceResult();
		}

		$document = $this->getDocument($payment);
		if ($document === null)
		{
			return new PaySystem\ServiceResult();
		}

		$documentInfo = $document->getFile()->getData();
		$this->setExtraParams($documentInfo);

		return $this->showTemplate($payment, 'template');
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}

	/**
	 * @return bool
	 */
	public function isAffordPdf()
	{
		return true;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getHandlerModeList()
	{
		$result = [];

		if (!Main\Loader::includeModule('documentgenerator')
			||
			!Main\Loader::includeModule('crm')
		)
		{
			return $result;
		}

		$provider = static::getDataProviderClass();
		$templateList = DocumentGenerator\Model\TemplateTable::getListByClassName($provider);

		foreach ($templateList as $item)
		{
			$result[$item['ID']] = htmlspecialcharsbx($item['NAME']);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return false|static
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getDocument(Sale\Payment $payment)
	{
		$dbRes = DocumentGenerator\Model\DocumentTable::getList([
			'select' => ['ID', 'UPDATE_TIME'],
			'filter' => [
				'=PROVIDER' => static::getDataProviderClass(),
				'=VALUE' => $payment->getOrderId(),
				'=TEMPLATE_ID' => $this->service->getField('PS_MODE'),
			],
			'order' => ['ID' => 'DESC'],
			'limit' => 1,
		]);

		$data = $dbRes->fetch();
		if ($data)
		{
			$document = DocumentGenerator\Document::loadById($data['ID']);

			/** @var Sale\PaymentCollection $collection */
			$collection = $payment->getCollection();

			$order = $collection->getOrder();
			if ($data['UPDATE_TIME'] < $order->getField('DATE_UPDATE'))
			{
				$document->update([]);
			}
		}
		else
		{
			if (!$this->service->getField('PS_MODE'))
			{
				return null;
			}

			$template = DocumentGenerator\Template::loadById($this->service->getField('PS_MODE'));
			if (!$template || $template->isDeleted())
			{
				return null;
			}
			$template->setSourceType(static::getDataProviderClass());

			$document = DocumentGenerator\Document::createByTemplate($template, $payment->getOrderId());
			$document->getFile();
		}

		return $document;
	}

}