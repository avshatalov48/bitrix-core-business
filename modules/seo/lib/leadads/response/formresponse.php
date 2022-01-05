<?php

namespace Bitrix\Seo\LeadAds\Response;


use Bitrix\Main\Result;
use Bitrix\Seo\LeadAds\LeadAdsForm;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\LeadAds\Response\Builder\FormBuilderInterface;

class FormResponse extends Result
{
	/**@var Response[]*/
	protected $data = [];

	/**@var FormBuilderInterface $formBuilder*/
	protected $formBuilder;

	/**@var int $iterator*/
	private $currentResponse;

	/**@var int $responseCount*/
	private $responseCount;

	/**
	 * @params FormBuilder
	 * @param Response[] $responses
	 */
	public function __construct(FormBuilderInterface $formBuilder, Response... $responses)
	{
		parent::__construct();

		$this->formBuilder = $formBuilder;
		$this->currentResponse = 0;
		$this->responseCount = count($responses);

		foreach ($responses as $response)
		{
			if (!$response->isSuccess())
			{
				$this->addErrors($response->getErrors());
			}
		}

		$this->data = array_values($responses);
	}

	/**
	 * Creates new instance of FormResponse in case it must be immutable
	 * @param Response[] $data
	 *
	 * @return FormResponse
	 */
	public function setData(array $data): FormResponse
	{
		return new static($this->formBuilder,...$data);
	}

	/**
	 * @return LeadAdsForm|null
	 */
	public function fetch(): ?LeadAdsForm
	{
		if ($this->currentResponse >= $this->responseCount || !$this->isSuccess())
		{
			return null;
		}

		/**@var array|null $form*/
		if (!$form = $this->data[$this->currentResponse]->fetch())
		{
			++$this->currentResponse;

			return $this->fetch();
		}

		return $this->formBuilder->buildForm($form);
	}

	/**
	 * @return LeadAdsForm[]
	 */
	public function fetchAll(): array
	{
		for($fetchResult = array(); null !== $item = $this->fetch();)
		{
			$fetchResult[] = $item;
		}

		return $fetchResult;
	}
}