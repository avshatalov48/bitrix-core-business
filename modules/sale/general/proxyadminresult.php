<?php
class CSaleProxyAdminResult extends CAdminResult
{
	private $parameters = array();
	private $entityName = '';

	public function __construct($parameters, $entityName, $tableId)
	{
		$this->parameters = $parameters;
		$this->entityName = $entityName;
		parent::__construct(array(), $tableId);
	}

	function NavStart($nPageSize = 0, $bShowAll = true, $iNumPage = false)
	{
		$nSize = self::GetNavSize($this->table_id, $nPageSize);

		if(!is_array($nPageSize))
			$nPageSize = array();

		$nPageSize["nPageSize"] = $nSize;
		if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] === "excel")
		{
			$nPageSize["NavShowAll"] = true;
		}

		$this->nInitialSize = $nPageSize["nPageSize"];

		if(is_array($nPageSize))
			$this->InitNavStartVars($nPageSize);
		else
			$this->InitNavStartVars(intval($nPageSize), $bShowAll, $iNumPage);

		// force to db resource type, although we got empty array on input
		$en = $this->entityName;

		$runtime = $this->parameters['runtime'] ?? [];
		if (!is_array($runtime))
		{
			$runtime = [];
		}
		$filter = $this->parameters['filter'] ?? [];
		if (!is_array($filter))
		{
			$filter = [];
		}

		// to increase perfomance, have to throw away unused (in filter) runtimes
		foreach ($runtime as $fld => $desc)
		{
			$found = false;
			foreach($filter as $condition => $value)
			{
				if(mb_strpos($condition, $fld) !== false)
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				unset($runtime[$fld]);
			}
		}

		$count = $en::getList(array(
			'filter' => $filter,
			'select' => array('REC_CNT'),
			'runtime' => array_merge($runtime, array(
				'REC_CNT' => array(
					'data_type' => 'integer',
					'expression' => array(
						'count(*)'
					)
				)
			))
		))->fetch();
		$this->NavRecordCount = $count['REC_CNT'];

		// the following code was taken from DBNavStart()

		// here we could use Bitrix\Main\DB\Paginator

		//calculate total pages depend on rows count. start with 1
		$this->NavPageCount = floor($this->NavRecordCount/$this->NavPageSize);
		if($this->NavRecordCount % $this->NavPageSize > 0)
			$this->NavPageCount++;

		//page number to display. start with 1
		$this->calculatePageNumber();

		$parameters = $this->parameters;
		$parameters['limit'] = $this->NavPageSize;
		$parameters['offset'] = ($this->NavPageNomer - 1) * $this->NavPageSize;

		$res = $en::getList($parameters);
		$this->arResult = array();
		while($item = $res->Fetch())
			$this->arResult[] = $item;
	}
}
class CSaleProxyAdminUiResult extends CAdminUiResult
{
	private $parameters = array();
	private $entityName = '';

	public function __construct($parameters, $entityName, $tableId)
	{
		$this->parameters = $parameters;
		$this->entityName = $entityName;
		parent::__construct(array(), $tableId);
	}

	function NavStart($nPageSize = 0, $bShowAll = true, $iNumPage = false)
	{
		$nSize = self::GetNavSize($this->table_id, $nPageSize);

		if(!is_array($nPageSize))
			$nPageSize = array();

		$nPageSize["nPageSize"] = $nSize;
		if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] === "excel")
		{
			$nPageSize["NavShowAll"] = true;
		}

		$this->nInitialSize = $nPageSize["nPageSize"];

		if(is_array($nPageSize))
			$this->InitNavStartVars($nPageSize);
		else
			$this->InitNavStartVars(intval($nPageSize), $bShowAll, $iNumPage);

		// force to db resource type, although we got empty array on input
		$en = $this->entityName;

		$runtime = $this->parameters['runtime'] ?? [];
		if (!is_array($runtime))
		{
			$runtime = [];
		}
		$filter = $this->parameters['filter'] ?? [];
		if (!is_array($filter))
		{
			$filter = [];
		}

		// to increase perfomance, have to throw away unused (in filter) runtimes
		foreach($runtime as $fld => $desc)
		{
			$found = false;
			foreach($filter as $condition => $value)
			{
				if(mb_strpos($condition, $fld) !== false)
				{
					$found = true;
					break;
				}
			}

			if(!$found)
				unset($runtime[$fld]);
		}

		$count = $en::getList(array(
			'filter' => $filter,
			'select' => array('REC_CNT'),
			'runtime' => array_merge($runtime, array(
				'REC_CNT' => array(
					'data_type' => 'integer',
					'expression' => array(
						'count(*)'
					)
				)
			))
		))->fetch();
		$this->NavRecordCount = $count['REC_CNT'];

		// the following code was taken from DBNavStart()

		// here we could use Bitrix\Main\DB\Paginator

		//calculate total pages depend on rows count. start with 1
		$this->NavPageCount = floor($this->NavRecordCount/$this->NavPageSize);
		if($this->NavRecordCount % $this->NavPageSize > 0)
			$this->NavPageCount++;

		//page number to display. start with 1
		$this->calculatePageNumber();

		$parameters = $this->parameters;
		$parameters['limit'] = $this->NavPageSize;
		$parameters['offset'] = ($this->NavPageNomer - 1) * $this->NavPageSize;

		$res = $en::getList($parameters);
		$this->arResult = array();
		while($item = $res->Fetch())
			$this->arResult[] = $item;
	}
}
