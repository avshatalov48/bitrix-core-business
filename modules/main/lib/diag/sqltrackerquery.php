<?php

namespace Bitrix\Main\Diag;

class SqlTrackerQuery implements \ArrayAccess
{
	/** @var string */
	protected $sql = "";
	/** @var array|null */
	protected $binds = null;
	/** @var string */
	protected $state = "";
	/** @var string */
	protected $node = "";
	/** @var float */
	protected $startTime = 0.0;
	/** @var float */
	protected $finishTime = 0.0;
	/** @var float */
	protected $time = 0.0;
	/** @var array|null */
	protected $trace = null;
	/** @var SqlTracker */
	protected $tracker;

	/**
	 * @param SqlTracker $tracker This sql tracker.
	 */
	public function __construct(SqlTracker $tracker)
	{
		$this->tracker = $tracker;
	}

	/**
	 * Starts sql timer.
	 *
	 * @param string $sql Query text.
	 * @param array|null $binds Binded variables used with query.
	 *
	 * @return void
	 */
	public function startQuery($sql, array $binds = null)
	{
		$this->sql = $sql;
		$this->binds = $binds;
		$this->startTime = Helper::getCurrentMicrotime();
	}

	/**
	 * Ends sql timer.
	 *
	 * @param integer $skip How many backtrace skip. By default 3.
	 *
	 * @return void
	 */
	public function finishQuery($skip = 3)
	{
		$this->finishTime = Helper::getCurrentMicrotime();
		$this->time = $this->finishTime - $this->startTime;
		$this->trace = $this->filterTrace(Helper::getBackTrace($this->tracker->getDepthBackTrace(), null, $skip));

		$this->tracker->addTime($this->time);
		$this->tracker->writeFileLog($this->sql, $this->time, "", 4);
	}

	/**
	 * Resets sql timer start.
	 * combined with refinishQuery allows additional time to be included into execution.
	 *
	 * @return void
	 * @see SqlTrackerQuery::refinishQuery
	 */
	public function restartQuery()
	{
		$this->startTime = Helper::getCurrentMicrotime();
	}

	/**
	 * Finishes query timer one more time.
	 * Use with restartQuery.
	 *
	 * @return void
	 * @see SqlTrackerQuery::restartQuery
	 */
	public function refinishQuery()
	{
		$this->finishTime = Helper::getCurrentMicrotime();
		$this->addTime($this->finishTime - $this->startTime);
	}

	/**
	 * Returns tracked sql text.
	 *
	 * @return string
	 */
	public function getSql()
	{
		return $this->sql;
	}

	/**
	 * Sets tracked sql text.
	 * Returns the object for call chaining.
	 *
	 * @param string $sql Sql text.
	 *
	 * @return SqlTrackerQuery
	 */
	public function setSql($sql)
	{
		$this->sql = (string)$sql;
		return $this;
	}

	/**
	 * Returns sql binds used for query execution.
	 *
	 * @return array|null
	 */
	public function getBinds()
	{
		return $this->binds;
	}

	/**
	 * Sets tracked sql binds.
	 * Returns the object for call chaining.
	 *
	 * @param array $binds Sql binds.
	 *
	 * @return SqlTrackerQuery
	 */
	public function setBinds(array $binds)
	{
		$this->binds = $binds;
		return $this;
	}

	/**
	 * Returns page state of the query.
	 *
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Sets tracked sql page state.
	 * Returns the object for call chaining.
	 *
	 * @param string $state Page state.
	 *
	 * @return SqlTrackerQuery
	 */
	public function setState($state)
	{
		$this->state = (string)$state;
		return $this;
	}

	/**
	 * Returns sql connection node id of the query.
	 *
	 * @return string
	 */
	public function getNode()
	{
		return $this->node;
	}

	/**
	 * Sets tracked sql connection node id.
	 * Returns the object for call chaining.
	 *
	 * @param string $node Cluster node identifier.
	 *
	 * @return SqlTrackerQuery
	 */
	public function setNode($node)
	{
		$this->node = (string)$node;
		return $this;
	}

	/**
	 * Returns sql execution time.
	 *
	 * @return float
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * Sets tracked sql execution time.
	 * Returns the object for call chaining.
	 *
	 * @param float $time Sql execution time in seconds.
	 *
	 * @return SqlTrackerQuery
	 */
	public function setTime($time)
	{
		$this->tracker->addTime(-$this->time);
		$this->time = (float)$time;
		$this->tracker->addTime($this->time);
		return $this;
	}

	/**
	 * Increments sql execution time.
	 *
	 * @param float $time Time in seconds to add.
	 *
	 * @return void
	 */
	public function addTime($time)
	{
		$time = (float)$time;
		$this->time += $time;
		$this->tracker->addTime($time);
	}

	/**
	 * Returns backtrace of the query.
	 *
	 * @return array|null
	 */
	public function getTrace()
	{
		return $this->trace;
	}

	/**
	 * Sets tracked sql backtrace.
	 * Returns the object for call chaining.
	 *
	 * @param array|null $trace Query backtrace.
	 *
	 * @return SqlTrackerQuery
	 */
	public function setTrace(array $trace = null)
	{
		$this->trace = ($trace !== null ? $this->filterTrace($trace) : []);

		return $this;
	}

	/**
	 * Whether an offset exists.
	 * Part of ArrayAccess implementation made for backward compatibility.
	 *
	 * @param mixed $offset Array key.
	 *
	 * @return boolean
	 */
	public function offsetExists($offset): bool
	{
		switch ((string)$offset)
		{
			case "BX_STATE":
			case "TIME":
			case "QUERY":
			case "TRACE":
			case "NODE_ID":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Offset to retrieve.
	 * Part of ArrayAccess implementation made for backward compatibility.
	 *
	 * @param mixed $offset Array key.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		switch ($offset)
		{
			case "BX_STATE":
				return $this->state;
			case "TIME":
				return $this->time;
			case "QUERY":
				return $this->sql;
			case "TRACE":
				return $this->trace;
			case "NODE_ID":
				return $this->node;
			default:
				return false;
		}
	}

	/**
	 * Offset to set.
	 * Part of ArrayAccess implementation made for backward compatibility.
	 *
	 * @param mixed $offset Array key.
	 * @param mixed $value Array value.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value): void
	{
	}

	/**
	 * Offset to unset.
	 * Part of ArrayAccess implementation made for backward compatibility.
	 *
	 * @param mixed $offset Array key.
	 *
	 * @return void
	 */
	public function offsetUnset($offset): void
	{
	}

	/**
	 * Removes and formats memory consuming function arguments in the backtrace.
	 *
	 * @param array $trace Backtrace.
	 *
	 * @return array
	 */
	protected function filterTrace($trace)
	{
		$filtered = array();
		foreach ($trace as $tr)
		{
			$args = array();
			if (!empty($tr["args"]) && is_array($tr["args"]))
			{
				foreach ($tr["args"] as $k1 => $v1)
				{
					if (is_array($v1))
					{
						foreach ($v1 as $k2 => $v2)
						{
							if (is_scalar($v2))
							{
								$args[$k1][$k2] = $v2;
							}
							elseif (is_object($v2))
							{
								$args[$k1][$k2] = get_class($v2);
							}
							else
							{
								$args[$k1][$k2] = gettype($v2);
							}
						}
					}
					else
					{
						if (is_scalar($v1))
						{
							$args[$k1] = $v1;
						}
						elseif (is_object($v1))
						{
							$args[$k1] = get_class($v1);
						}
						else
						{
							$args[$k1] = gettype($v1);
						}
					}
				}
			}

			$filtered[] = [
				"file" => $tr["file"] ?? null,
				"line" => $tr["line"] ?? null,
				"class" => $tr["class"] ?? null,
				"type" => $tr["type"] ?? null,
				"function" => $tr["function"] ?? null,
				"args" => $args,
			];
		}
		return $filtered;
	}
}
