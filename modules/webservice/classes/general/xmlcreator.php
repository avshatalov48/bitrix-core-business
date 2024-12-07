<?php

class CXMLCreator {

	var $tag;
	var $data;
	var $startCDATA = "";
	var $endCDATA = "";

	var $attributs = array();
	var $children = array();

	public function __construct($tag, $cdata = false)
	{
		$cdata ? $this->setCDATA() : null;
		$this->tag = $tag;
	}

	// format of $heavyTag = '[Index:]TagName [asd:qwe="asd"] [zxc:dfg="111"]'
	// returns created CXMLCreator node with setted TagName and Attributes
	public static function createTagAttributed($heavyTag, $value = null)
	{
		$heavyTag = trim($heavyTag);
		$name = $heavyTag;

		$attrs = 0;
		$attrsPos = mb_strpos($heavyTag, " ");

		if ($attrsPos)
		{
			$name = mb_substr($heavyTag, 0, $attrsPos);
			$attrs = mb_strstr(trim($heavyTag), " ");
		}

		if (!trim($name)) return false;

		$nameSplited = explode(":", $name);
		if ($nameSplited)
			$name = $nameSplited[count($nameSplited) - 1];
		$name = CDataXML::xmlspecialcharsback($name);

		$node = new CXMLCreator( $name );

		if ($attrs and mb_strlen($attrs))
		{
			$attrsSplit = explode("\"", $attrs);
			$i = 0;
			while ($validi = mb_strpos(trim($attrsSplit[$i]), "="))
			{
				$attrsSplit[$i] = trim($attrsSplit[$i]);
				// attr:ns=
				$attrName = CDataXML::xmlspecialcharsback(mb_substr($attrsSplit[$i], 0, $validi));
				// attrs:ns
				$attrValue = CDataXML::xmlspecialcharsback($attrsSplit[$i+1]);

				$node->setAttribute($attrName, $attrValue);
				$i = $i + 2;
			}
		}

		if (null !== $value)
			$node->setData($value);

		return $node;
	}

	public static function encodeValueLight( $name, $value)
	{
		global $xsd_simple_type;

		//AddMessage2Log($name."|".mydump($value));
		if (!$name)
		{
			ShowError("Tag name undefined (== 0) in encodeValueLight.");
			return false;
		}

		$node = CXMLCreator::createTagAttributed($name);
		$name = $node->tag;

		if (!$node)
		{
			ShowError("Can't create NODE object. Unable to parse tag name: ".$name);
			return false;
		}

		if (is_object($value) && mb_strtolower(get_class($value)) == "cxmlcreator")
		{
			$node->addChild($value);
		}
		else if (is_object($value))
		{
			$ovars = get_object_vars($value);
			foreach ($ovars as $pn => $pv)
			{
				$decode = CXMLCreator::encodeValueLight( $pn, $pv);
				if ($decode) $node->addChild($decode);
			}
		}
		else if (is_array($value))
		{
			foreach ($value as $pn => $pv)
			{
				$decode = CXMLCreator::encodeValueLight( $pn, $pv);
				if ($decode)
				{
					$node->addChild($decode);
				}
			}
		}
		else
		{
			if (!$value) $node->setData("");
			else if (!isset($xsd_simple_type[gettype($value)]))
			{
				ShowError("Unknown param type.");
				return false;
			}

			$node->setData($value);
		}

		return $node;
	}

	function setCDATA()
	{
		$this->startCDATA = "<![CDATA[";
		$this->endCDATA = "]]>";
	}

	function setAttribute($attrName, $attrValue)
	{
		$newAttribute = array($attrName => $attrValue);
		$this->attributs = array_merge($this->attributs, $newAttribute);
	}

	function setData($data)
	{
		$this->data = $data;
	}

	function setName($tag)
	{
		//$tag = static::xmlspecialchars($tag);
		$this->tag = $tag;
	}

	function addChild($element)
	{
		//AddMessage2Log(mydump(get_class($element)));
		if($element && (get_class($element) == "CXMLCreator" || get_class($element) == "cxmlcreator"))
		{
			array_push($this->children, $element);
		}
	}

	function getChildrenCount()
	{
		return count($this->children);
	}

	function _getAttributs()
	{
		$attributs = "";
		if (is_array($this->attributs)){
			foreach($this->attributs as $key=>$val)
			{
				$attributs .= " " . static::xmlspecialchars($key). "=\"" . static::xmlspecialchars($val) . "\"";
			}
		}
		return $attributs;
	}

	function _getChildren()
	{
		$children = "";
		foreach($this->children as $key=>$val)
		{
			$children .= $val->getXML();
		}
		return $children;

	}

	function getXML()
	{
		if (!$this->tag) return "";
		$xml  = "<" . static::xmlspecialchars($this->tag) . $this->_getAttributs() . ">";
		$xml .= $this->startCDATA;
		$xml .= $this->data;
		$xml .= $this->endCDATA;
		$xml .= $this->_getChildren();
		$xml .= "</" . static::xmlspecialchars($this->tag) . ">";
		return $xml;
	}

	public static function getXMLHeader()
	{
		return "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	}

	function __destruct()
	{
		unset($this->tag);
	}

	public static function CreateFromDOM($dom)
	{
		return CXMLCreator::__createFromDOM($dom->root[0]);
	}

	public static function __createFromDOM($domNode)
	{
		$result = new CXMLCreator($domNode->name);

		$result->setData($domNode->content);

		if (is_array($domNode->attributes))
		{
			foreach ($domNode->attributes as $attrDomNode)
			{
				$result->setAttribute($attrDomNode->name, $attrDomNode->content);
			}
		}

		if (is_array($domNode->children))
		{
			foreach ($domNode->children as $domChild)
			{
				$result->addChild(CXMLCreator::__createFromDOM($domChild));
			}
		}

		return $result;
	}

	public static function xmlspecialchars($str)
	{
		static $search = array("&","<",">","\"","'","\r","\n");
		static $replace = array("&amp;","&lt;","&gt;","&quot;","&apos;","&#13;","&#10;");
		return str_replace($search, $replace, $str);
	}

	public static function xmlspecialcharsback($str)
	{
		static $search = array("&lt;","&gt;","&quot;","&apos;","&amp;","&#13;","&#10;");
		static $replace = array("<",">","\"","'","&","\r","\n");
		return str_replace($search, $replace, $str);
	}
}
