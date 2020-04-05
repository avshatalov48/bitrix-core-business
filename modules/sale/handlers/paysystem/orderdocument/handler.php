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
class OrderDocumentHandler
	extends PaySystem\BaseServiceHandler
	implements PaySystem\IDocumentGeneratePdf
{
	/**
	 * @return string
	 */
	protected static function getDataProviderClass()
	{
		return Integration\DocumentGenerator\DataProvider\Order::class;
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
		$params = array_merge($documentInfo, DocumentGenerator\Model\ExternalLinkTable::getPublicUrlsByDocumentId($document->ID));
		if(!empty($params['hash']))
		{
			$params['isPublicMode'] = true;
		}
		if ($this->service->getField('NEW_WINDOW') === 'Y')
		{
			$params['IFRAME'] = 'Y';
			$params['PRINT'] = 'Y';
		}
		$this->setExtraParams($params);

		return $this->showTemplate($payment, 'template');
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return [];
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
	 * @param $params
	 * @return PaySystem\ServiceResult|mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function registerCallbackOnGenerate(Payment $payment, $params)
	{
		$document = $this->getDocument($payment);
		if ($document === null)
		{
			return new PaySystem\ServiceResult();
		}

		$callback = [
			'DOCUMENT_ID' => $document->ID,
			'MODULE_ID' => $params['MODULE_ID'],
			'CALLBACK_CLASS' => $params['CALLBACK_CLASS'],
			'CALLBACK_METHOD' => $params['CALLBACK_METHOD'],
		];

		Sale\DocumentGenerator\CallbackRegistry::add($callback);

		return new PaySystem\ServiceResult();
	}

	/**
	 * @param Payment $payment
	 * @return DocumentGenerator\Document|false|null
	 * @throws Main\ArgumentException
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
			$document->setValues(['DocumentTitle' => $this->getFileName($payment)]);
			$document->getFile();
		}

		$document->enablePublicUrl();

		return $document;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 * @throws Main\ObjectException
	 */
	protected function getFileName(Payment $payment)
	{
		$today = new Main\Type\Date();
		return 'invoice_'.$this->getInvoiceNumber($payment).'_'.str_replace(['.', '\\', '/'], '-' ,$today->toString());
	}

	/**
	 * @param $payment
	 * @return mixed
	 */
	protected function getInvoiceNumber(Payment $payment)
	{
		return $payment->getField('ACCOUNT_NUMBER');
	}

	/**
	 * @param Payment $payment
	 * @return bool|false|mixed|string|null
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getContent(Payment $payment)
	{
		global $APPLICATION;

		$file = $this->getFile($payment);
		if ($file)
		{
			return $APPLICATION->GetFileContent($file['src']);
		}

		return null;
	}

	/**
	 * @param Payment $payment
	 * @return array|bool|false|mixed|null
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getFile(Payment $payment)
	{
		if (!Main\Loader::includeModule('documentgenerator'))
		{
			return null;
		}

		$document = $this->getDocument($payment);
		if ($document === null)
		{
			return null;
		}

		$documentInfo = $document->getFile()->getData();
		if (isset($documentInfo['pdfUrl']))
		{
			$fileId = DocumentGenerator\Model\FileTable::getBFileId($document->PDF_ID);
			if ($fileId !== false)
			{
				$fileArray = \CFile::GetFileArray($fileId);
				if ($fileArray)
				{
					return $fileArray;
				}
			}
		}

		return null;
	}

	/**
	 * @param Payment $payment
	 * @return bool|mixed
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function isGenerated(Payment $payment)
	{
		if (!Main\Loader::includeModule('documentgenerator'))
		{
			return false;
		}

		$document = $this->getDocument($payment);
		if ($document === null)
		{
			return false;
		}

		$documentInfo = $document->getFile()->getData();
		return isset($documentInfo['pdfUrl']);
	}
}