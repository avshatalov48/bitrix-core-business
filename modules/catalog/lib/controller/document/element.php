<?php

namespace Bitrix\Catalog\Controller\Document;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use CCatalogStoreDocsElement;
use Bitrix\Catalog\RestView;

class Element extends Engine\Controller
{
	private const LIST_COUNT_DEFAULT = 50;
	private const LIST_COUNT_LIMIT = 500;

	/**
	 * @param array $fields
	 *
	 * @return false|int
	 */
	public function addAction(array $fields)
	{
		$view = new RestView\DocumentElement();
		$fields = $view->internalizeFieldsAdd($fields);

		return CCatalogStoreDocsElement::add($fields);
	}

	public function updateAction(int $id, array $fields): bool
	{
		$view = new RestView\DocumentElement();
		$fields = $view->internalizeFieldsUpdate($fields);

		return CCatalogStoreDocsElement::update($id, $fields);
	}

	public function deleteAction(int $id)
	{
		return CCatalogStoreDocsElement::delete($id);
	}

	public function listAction(
		array $order = [],
		array $filter = [],
		array $select = [],
		int $offset = 0,
		int $limit = self::LIST_COUNT_DEFAULT
	): array
	{
		if ($limit <= 0)
		{
			$limit = self::LIST_COUNT_DEFAULT;
		}
		elseif ($limit > self::LIST_COUNT_LIMIT)
		{
			$limit = self::LIST_COUNT_LIMIT;
		}

		$result = [];
		$view = new RestView\DocumentElement();
		$data = $view->internalizeFieldsList(
			[
				'order' => $order,
				'filter' => $filter,
				'select' => $select,
			]
		);
		$page = 1 + (int)($offset / $limit);

		$res = CCatalogStoreDocsElement::getList(
			$data['order'] ?? [],
			$data['filter'] ?? [],
			false,
			[
				'nPageSize' => $limit,
				'iNumPage' => $page,
			],
			$data['select'] ?? [],
		);

		while ($element = $res->fetch())
		{
			$result[] = $element;
		}

		if ($offset >= 0)
		{
			$result['total'] = $res->nSelectedCount;
			if ($res->nSelectedCount > $offset + $limit)
			{
				$result['next'] = $page * $limit;
			}
		}

		return $result;
	}

	public function fieldsAction()
	{
		$view = new RestView\DocumentElement();
		return $view->getFields();
	}

	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\Scope(ActionFilter\Scope::REST),
			]
		);
	}
}
