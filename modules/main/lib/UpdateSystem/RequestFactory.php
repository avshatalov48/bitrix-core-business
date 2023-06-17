<?php
namespace Bitrix\Main\UpdateSystem;

class RequestFactory
{
	private RequestBuilderInterface $builder;

	public function __construct(RequestBuilderInterface $builder)
	{
		$this->builder = $builder;
	}

	public function build(): Request
	{
		return $this->builder->setUrl()->setHeaders()->setProxy()->setBody()->build();
	}
}
