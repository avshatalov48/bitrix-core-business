<?php

namespace Bitrix\Seo\Retargeting;

class Paginator implements \IteratorAggregate
{
	/**@var Request $request*/
	private $request;

	/**@var array|null $params*/
	private $params;

	/**
	 * @param Request $request
	 * @param array $params
	 */
	public function __construct(
		Request $request,
		array $params
	)
	{
		$this->request = $request;
		$this->params = $params;
	}

	/**
	 * @return \Generator<Request>
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getIterator() : \Generator
	{
		do
		{
			$response = $this->request->send($this->params);

			$next = $response instanceof PagingInterface && $this->params = $response->prepareRequestParams($this->params);

			yield $response;
		}
		while($next);
	}
}