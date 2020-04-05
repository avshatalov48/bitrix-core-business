<?php
namespace Bitrix\Rest;


use Bitrix\Main\Entity\Query;

class EventOfflineQuery extends Query
{
	public function getMarkQuery($processId)
	{
		// initialize all internal guts
		$this->getQuery();

		$connection = $this->entity->getConnection();
		$helper = $connection->getSqlHelper();

		$sqlWhere = $this->buildWhere();
		$sqlOrder = $this->buildOrder();


		$update = $helper->prepareUpdate($this->entity->getDBTableName(), array('PROCESS_ID' => $processId));

		$queryParts = array_filter(array(
			'UPDATE' => $this->quoteTableSource($this->entity->getDBTableName()).' '.$helper->quote($this->getInitAlias()),
			'SET' => $update[0],
			'WHERE' => $sqlWhere,
			'ORDER BY' => $sqlOrder,
			'LIMIT' => $this->getLimit(), // we cannot use getTopSql here
		));

		foreach ($queryParts as $k => &$v)
		{
			$v = $k . ' ' . $v;
		}

		$sql = join("\n", $queryParts);

		return $sql;
	}
}