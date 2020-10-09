<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
class MPLSimpleHTMLParser
{
	var $data;
	var $parse_search_needle = '/([^\[]*)(?:\[(.*)\])*/i';
	var $parse_tag = '/((\<\s*(\/)?\s*([a-z]+).*?(?:(\/)\>|\>))[^<]*)/ism';
	var $parse_beginning_spaces = '/^([\s]*)/m';
	var $replace_tag_begin = '/^\s*\w+\s*/';
	var $parse_params = '/([a-z]+)\s*=\s*(?:([^\s]*)|(?:[\'"]([^\'"])[\'"]))/im';
	var $lastError = '';

	function __construct ($data)
	{
		$this->data = $data;
	}

	function findTagStart($needle) // needle = input[name=input;class=red]
	{
		$offset = 0;

		$search = array();
		if (preg_match( $this->parse_search_needle, $needle, $matches ) == 0)
			return '';
		if (sizeof($matches) > 1)
			$search['TAG'] = trim($matches[1]);
		if (sizeof($matches) > 2)
		{
			$arAttr = explode(';', $matches[2]);
			foreach($arAttr as $attr)
			{
				list($attr_name, $attr_value) = explode('=', $attr);
				$search[mb_strtoupper(trim($attr_name))] = trim($attr_value);
			}
		}
		$tmp = $this->data;
		// skip beginning spaces
		if (preg_match($this->parse_beginning_spaces, $tmp, $spaces) > 0)
		{
			$offset = mb_strlen($spaces[1]);
			$tmp = mb_substr($tmp, $offset);
		}

		while ($tmp <> '' && preg_match($this->parse_tag, $tmp, $matches) > 0)
		{
			$tag_name = $matches[4];
			$tag = $matches[2];
			$skip = $matches[1];
			if (mb_strlen($skip) < 1) return false;
			if ($tag_name == $search['TAG']) // tag found
			{
				// parse params
				$params = preg_replace($this->replace_tag_begin, '', trim($tag, "<>"));
				if (preg_match_all($this->parse_params, $params, $arParams, PREG_SET_ORDER ) > 0)
				{
					// store tag params
					$arTagParams = array();
					foreach($arParams as $arParam)
						$arTagParams[mb_strtoupper(trim($arParam[1]))] = trim(trim($arParam[2]), '"\'');
					// compare all search params
					$found = true;
					foreach($search as $key => $value)
					{
						if ($key == 'TAG') continue;
						if (!( isset($arTagParams[$key]) && $arTagParams[$key] == $value))
						{
							$found = false;
							break;
						}
					}
					if ($found)
					{
						return $offset;
					}
				}
			}
			$offset += mb_strlen($skip);
			$tmp = mb_substr($tmp, mb_strlen($skip));

			// skip special tags
			while ($skip = $this->skipTags($tmp))
			{
				$offset += $skip;
				$tmp = mb_substr($tmp, $skip);
			}
		}
		return false;
	}

	function skipTags($tmp)
	{
		static $tags_open = array('<!--', '<script');
		static $tags_close = array('-->', '</script>');
		static $n_tags = 2;
		static $tags_quoted;

		if (!is_array($tags_quoted))
			for ($i=0; $i<$n_tags;$i++)
				$tags_quoted[$i] = array('open' => preg_quote($tags_open[$i]), 'close' => preg_quote($tags_close[$i]));

		for ($i=0; $i<$n_tags;$i++)
		{
			if (preg_match('#^\s*'.$tags_quoted[$i]['open'].'#i', $tmp) < 1) continue;
			if (preg_match('#('.$tags_quoted[$i]['close'].'\s*)#im', $tmp, $matches) > 0)
			{
				$endpos = mb_strpos($tmp, $matches[1]);
				$offset = $endpos + mb_strlen($matches[1]);
				return $offset;
			}
		}
		return false;
	}

	function setError($msg)
	{
		$this->lastError = $msg;
		return false;
	}

	function findTagEnd($startIndex)
	{
		if ($startIndex === false || (intval($startIndex) == 0 && $startIndex !== 0))
			return $this->setError('E_PARSE_INVALID_INDEX');
		$tmp = mb_substr($this->data, $startIndex);

		$this->lastError = '';
		$arStack = array();
		$offset = 0;
		$closeMistmatch = 2;
		$tag_id = 0;

		// skip beginning spaces
		if (preg_match($this->parse_beginning_spaces, $tmp, $spaces) > 0)
		{
			$offset = mb_strlen($spaces[1]);
			$tmp = mb_substr($tmp, $offset);
		}

		while ($tmp <> '' && preg_match($this->parse_tag, $tmp, $matches) > 0)
		{
			$tag_id++;
			$tag_name = mb_strtoupper(trim($matches[4]));
			$tag = $matches[2];
			$skip = $matches[1];
			if (mb_strlen($skip) < 1) return $this->setError('E_PARSE_INVALID_DOM_1');
			if ($matches[3] == '/') // close tag
			{
				if (end($arStack) == $tag_name)
					array_pop($arStack);
				else // lost close tag somewhere
				{
					$fixed = false;
					for ($i=2;$i<=$closeMistmatch+1;$i++)
					{
						if (sizeof($arStack) > $i && $arStack[sizeof($arStack)-$i] == $tag_name)
						{
							$arStack = array_slice($arStack, 0, -$i);
							$fixed = true;
						}
					}
					if (!$fixed)
					{
						return $this->setError('E_PARSE_INVALID_DOM_2');
					}
				}
			}
			elseif (isset($matches[5]) && $matches[5] == '/') // self close tag
			{
				// do nothing
			}
			elseif ($tag_name == 'LI' && end($arStack) == 'LI') // oh
			{
				// do nothing
			}
			else // open tag
			{
				$arStack[] = $tag_name;
			}
			if (sizeof($arStack) > 300)
				return $this->setError('E_PARSE_TOO_BIG_DOM_3');  // too big DOM
			elseif (sizeof($arStack) == 0) // done !
			return $offset + mb_strlen($tag);
			else // continue
			{
				$offset += mb_strlen($skip);
				$tmp = mb_substr($tmp, mb_strlen($skip));
			}
			// skip special tags
			while ($skip = $this->skipTags($tmp))
			{
				$offset += $skip;
				$tmp = mb_substr($tmp, $skip);
			}
		}
		return $this->setError('E_PARSE_INVALID_DOM_4');  // not enough data in $data ?
	}

	function getTagHTML($search)
	{
		$messagePost = '';
		$messageStart = $this->findTagStart($search);
		if ($messageStart === false) return '';
		$messageEnd = $this->findTagEnd($messageStart);
		if ($messageEnd !== false)
			$messagePost = mb_substr($this->data, $messageStart, $messageEnd);
		return trim($messagePost);
	}

	function getInnerHTML($startLabel, $endLabel, $multiple=false)
	{
		$startPos = mb_strpos($this->data, $startLabel);
		if ($startPos === false) return '';
		$startPos += mb_strlen($startLabel);
		$endPos = mb_strpos($this->data, $endLabel, $startPos);
		if ($endPos === false) return '';
		return trim(mb_substr($this->data, $startPos, $endPos - $startPos));
	}
}

function __MPLParseRecordsHTML(&$response, &$arParams, &$arResult)
{

}
?>