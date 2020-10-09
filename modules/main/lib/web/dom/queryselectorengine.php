<?php
namespace Bitrix\Main\Web\DOM;

class QuerySelectorEngine extends QueryEngine
{
	const PATH_CODE_NAME = 'name';
	const PATH_CODE_CHILD = 'child';
	const PATH_CODE_CLASS = 'class';
	const PATH_CODE_DESCENDANT = 'descendant';
	const PATH_CODE_ATTR = 'attr';
	const PATH_CODE_PSEUDO = 'pseudo';


	public function query($queryString = "", Node $node, $limit = 0, $direction = self::DIR_DOWN)
	{
		//TODO: use children property
		$instructionList = $this->parseQueryString($queryString);
		if(count($instructionList) <= 0)
		{
			return array();
		}

		return $this->queryInternal($instructionList, $node, $limit, $direction);
	}

	public function queryInternal(array $instructionList, Node $node, $limit = 0, $direction = self::DIR_DOWN)
	{
		$resultList = array();
		//echo "Instructions: ".print_R($instructionList, true);
		$filter = array();

		$isFilterOnlyChildList = null;

		$length = count($instructionList);
		for($i = 0; $i < $length; $i++)
		{
			$instruction = $instructionList[$i];
			switch($instruction['code'])
			{
				case self::PATH_CODE_NAME:

					$filter[] = array(QueryEngine::FILTER_NODE_NAME => $instruction['value']);
					break;

				case self::PATH_CODE_CLASS:

					$filter[] = array(QueryEngine::FILTER_ATTR_CLASS_NAME => $instruction['value']);
					break;

				case self::PATH_CODE_ATTR:

					$attrInstruction = $instruction['value'];
					if(isset($attrInstruction['value']))
					{
						$filter[] = array(QueryEngine::FILTER_ATTR_VALUE => array($attrInstruction));
					}
					else
					{
						$filter[] = array(QueryEngine::FILTER_ATTR => $attrInstruction['name']);
					}

					break;

				case self::PATH_CODE_DESCENDANT:

					$isFilterOnlyChildList = false;
					break 2;

				case self::PATH_CODE_CHILD:

					$isFilterOnlyChildList = true;
					break 2;

				default:

					//throw new \Bitrix\Main\NotSupportedException('Not supported instruction ' . $instruction['code']);
					return array();
			}

		}

		if(count($filter) <= 0)
		{
			return $resultList;
		}

		//echo "Filter: ".print_R($filter, true);


		if($i >= $length)
		{
			return $this->walk($filter, null, $node, $limit);
		}
		else
		{
			$findNodeList = array();
			if($isFilterOnlyChildList)
			{
				foreach($node->getChildNodesArray() as $findNode)
				{
					if($this->isNodeFiltered($findNode, $filter))
					{
						$findNodeList[] = $findNode;
					}
				}
			}
			else
			{
				$this->limit = null;
				$this->deep = false;
				$this->direction = $direction;
				$findNodeList = $this->walkInternal($filter, null, $node);
				//echo "findNodeList: " . count($findNodeList) . "\n\n\n";
			}
		}

		if(count($findNodeList) <= 0)
		{
			return $resultList;
		}


		$childInstructionList = array();
		while(++$i < $length)
		{
			$childInstructionList[] = $instruction = $instructionList[$i];
		}

		if(count($childInstructionList) <= 0)
		{
			return $resultList;
		}

		foreach($findNodeList as $findNode)
		{
			$resultList = array_merge(
				$resultList,
				$this->queryInternal($childInstructionList, $findNode, $limit, $direction)
			);
		}


		return $resultList;
	}

	public function parseQueryStringPseudo($string)
	{
		return '';
	}

	public function parseQueryStringAttr($string)
	{
		static $operations = array('~', '|', '^', '$', '*', '=', '!');

		$result = array();
		$list = explode('=', $string);

		if(isset($list[1]))
		{
			$operation = mb_substr($list[0], -1);
			if(in_array($operation, $operations))
			{
				$result['name'] = trim(mb_substr($list[0], 0, -1));
				$result['operation'] = $operation;
			}
			else
			{
				$result['name'] = trim($list[0]);
				$result['operation'] = '=';
			}

			$result['value'] = trim($list[1], "'\" \t\n\r\0\x0B");
		}
		else
		{
			$result['name'] = trim($list[0]);
		}

		return $result;
	}

	/**
	 * @param string $string
	 * @return array
	 */
	public function parseQueryString($string)
	{
		static $dividers = array('*', '#', '.', ' ', '>', '<', '[', ':');
		$path = array();

		$string = trim($string);
		$length = mb_strlen($string);

		$i = 0;
		while($i < $length)
		{
			$operator = '';

			$char = mb_substr($string, $i, 1);
			switch($char)
			{
				case '#':
					$operator = self::PATH_CODE_ATTR;
					$buffer = $this->writeToBuffer($string, $length, $i, $dividers);
					$path[] = array(
						'code' => $operator,
						'value' => array(
							'name' => 'id',
							'operation' => '=',
							'value' => $buffer
						)
					);
					break;

				case '.':
					$operator = self::PATH_CODE_CLASS;
					$buffer = $this->writeToBuffer($string, $length, $i, $dividers);
					$path[] = array('code' => $operator, 'value' => $buffer);
					break;

				case ' ':
					$operator = self::PATH_CODE_DESCENDANT;
					$path[] = array('code' => $operator, 'value' => '');
					++$i;
					break;

				case '>':
					$operator = self::PATH_CODE_CHILD;
					$path[] = array('code' => $operator, 'value' => '');
					++$i;
					break;

				case '<':
					$path[] = array('code' => $operator, 'value' => '');
					++$i;
					break;

				case ':':
					$operator = self::PATH_CODE_PSEUDO;
					$buffer = $this->writeToBuffer($string, $length, $i, $dividers);
					$path[] = array('code' => $operator, 'value' => $this->parseQueryStringPseudo($buffer));
					break;

				case '[':
					$operator = self::PATH_CODE_ATTR;
					$buffer = $this->writeToBuffer($string, $length, $i, [']']);
					$path[] = array('code' => $operator, 'value' => $this->parseQueryStringAttr($buffer));
					++$i;
					break;

				default:
					//throw new DomException('Wrong QuerySelector string');
					$operator = self::PATH_CODE_NAME;
					$buffer = $char;
					$buffer .= $this->writeToBuffer($string, $length, $i, $dividers);
					$path[] = array('code' => $operator, 'value' => $buffer);
			}
		}

		return $path;
	}

	/**
	 * @param string $string
	 * @param int $length
	 * @param int $i
	 * @param array $dividers
	 * @return string
	 */
	protected function writeToBuffer($string, $length, &$i, array $dividers)
	{
		$buffer = '';
		$escapeNext = false;

		while(++$i < $length)
		{
			$char = mb_substr($string, $i, 1);
			if($char === '\\')
			{
				if($escapeNext)
				{
					$buffer = mb_substr($buffer, 0, -1);
					$escapeNext = false;
				}
				else
				{
					$escapeNext = true;
				}
			}
			elseif(in_array($char, $dividers))
			{
				if($escapeNext)
				{
					$buffer = mb_substr($buffer, 0, -1);
					$escapeNext = false;
				}
				else
				{
					break;
				}
			}
			else
			{
				$escapeNext = false;
			}

			$buffer .= $char;
		}

		return $buffer;
	}
}