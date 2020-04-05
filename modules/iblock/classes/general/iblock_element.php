<?
class _CIBElement
{
	var $fields;
	var $props=false;

	function GetFields()
	{
		return $this->fields;
	}

	/**
	 * @param bool|array $arOrder
	 * @param array $arFilter
	 * @return array
	 */
	function GetProperties($arOrder = false, $arFilter = array())
	{
		if($arOrder === false)
			$arOrder = array("sort"=>"asc","id"=>"asc","enum_sort"=>"asc","value_id"=>"asc");
		if (count($arFilter)==0 && is_array($this->props))
		{
			$arAllProps = array();
			/** @noinspection PhpWrongForeachArgumentTypeInspection */
			foreach($this->props as $arProp)
			{
				if(strlen(trim($arProp["CODE"]))>0)
					$PIND = $arProp["CODE"];
				else
					$PIND = $arProp["ID"];

				$arProp["VALUE"] = $this->fields["PROPERTY_".$arProp["ID"]];
				$arProp["DESCRIPTION"] = $this->fields["DESCRIPTION_".$arProp["ID"]];
				if($arProp["MULTIPLE"]=="N")
				{
					if($arProp["PROPERTY_TYPE"]=="L")
					{
						$arProp["VALUE_ENUM_ID"] = $val = $arProp["VALUE"];
						$arEnum = CIBlockPropertyEnum::GetByID($val);
						if($arEnum!==false)
						{
							$arProp["~VALUE"] = $arEnum["VALUE"];
							if(is_array($arProp["VALUE"]) || preg_match("/[;&<>\"]/", $arProp["VALUE"]))
								$arProp["VALUE"]  = htmlspecialcharsEx($arEnum["VALUE"]);
							else
								$arProp["VALUE"]  = $arEnum["VALUE"];
							$arProp["VALUE_ENUM"] = $arProp["VALUE"];
							$arProp["VALUE_XML_ID"]  = htmlspecialcharsEx($arEnum["XML_ID"]);
							$arProp["VALUE_SORT"] = $arEnum["SORT"];
						}
						else
						{
							$arProp["~VALUE"] = "";
							$arProp["VALUE"]  = "";
						}
					}
					elseif(is_array($arProp["VALUE"]) || strlen($arProp["VALUE"]))
					{
						if($arProp["PROPERTY_TYPE"]=="N")
							$arProp["VALUE"] = htmlspecialcharsEx(CIBlock::NumberFormat($arProp["VALUE"]));
						$arProp["~VALUE"] = $this->fields["~PROPERTY_".$arProp["ID"]];
						$arProp["~DESCRIPTION"] = $this->fields["~DESCRIPTION_".$arProp["ID"]];
					}
					else
					{
						$arProp["VALUE"] = $arProp["~VALUE"] = "";
						$arProp["DESCRIPTION"] = $arProp["~DESCRIPTION"] = "";
					}
				}
				else
				{
					$arList = $arProp["VALUE"];
					$arListTilda = $this->fields["~PROPERTY_".$arProp["ID"]];
					if($arProp["PROPERTY_TYPE"]=="L")
					{
						$arProp["~VALUE"] = $arProp["VALUE"] = $arProp["VALUE_ENUM_ID"] = false;
						$arProp["VALUE_XML_ID"] = false;
						foreach($arList as $key=>$val)
						{
							if(strlen($val)>0)
							{
								$arEnum = CIBlockPropertyEnum::GetByID($key);
								if($arEnum!==false)
								{
									$xml_id = htmlspecialcharsEx($arEnum["XML_ID"]);
									$sort = $arEnum["SORT"];
								}
								else
								{
									$xml_id = false;
									$sort = false;
								}

								if(is_array($arProp["VALUE"]))
								{

									$arProp["VALUE_ENUM_ID"][] = $key;
									$arProp["~VALUE"][] = $val;
									if(is_array($val) || preg_match("/[;&<>\"]/", $val))
										$arProp["VALUE"][] = htmlspecialcharsEx($val);
									else
										$arProp["VALUE"][] = $val;
									$arProp["VALUE_XML_ID"][] = $xml_id;
									$arProp["VALUE_SORT"][] = $sort;
								}
								else
								{
									$arProp["VALUE_ENUM_ID"] = array($key);
									$arProp["~VALUE"] = array($val);
									if(is_array($val) || preg_match("/[;&<>\"]/", $val))
										$arProp["VALUE"] = array(htmlspecialcharsEx($val));
									else
										$arProp["VALUE"] = array($val);
									$arProp["VALUE_XML_ID"] = array($xml_id);
									$arProp["VALUE_SORT"] = array($sort);
								}
							}
						}
						$arProp["VALUE_ENUM"] = $arProp["VALUE"];
					}
					else
					{
						$arDesc = $arProp["DESCRIPTION"];
						$arDescTilda = $this->fields["~DESCRIPTION_".$arProp["ID"]];

						$arProp["~VALUE"] = $arProp["VALUE"] = false;
						$arProp["~DESCRIPTION"] = $arProp["DESCRIPTION"] = false;
						foreach($arList as $key=>$val)
						{
							if(is_array($val) || strlen($val)>0)
							{
								if(is_array($arProp["VALUE"]))
								{
									$arProp["~VALUE"][] = $arListTilda[$key];
									if($arProp["PROPERTY_TYPE"]=="N")
										$val = htmlspecialcharsEx(CIBlock::NumberFormat($val));
									$arProp["VALUE"][] = $val;
									$arProp["~DESCRIPTION"][] = $arDescTilda[$key];
									$arProp["DESCRIPTION"][] = $arDesc[$key];
								}
								else
								{
									$arProp["~VALUE"] = array($arListTilda[$key]);
									if($arProp["PROPERTY_TYPE"]=="N")
										$val = htmlspecialcharsEx(CIBlock::NumberFormat($val));
									$arProp["VALUE"] = array($val);
									$arProp["~DESCRIPTION"] = array($arDescTilda[$key]);
									$arProp["DESCRIPTION"] = array($arDesc[$key]);
								}
							}
						}
					}
				}
				$arAllProps[$PIND]=$arProp;
			}
			return $arAllProps;
		}

		if(array_key_exists("ID", $arFilter) && is_string($arFilter['ID']))
		{
			if (!is_numeric(substr($arFilter["ID"], 0, 1)))
			{
				$arFilter["CODE"] = $arFilter["ID"];
				unset($arFilter["ID"]);
			}
		}

		if(!array_key_exists("ACTIVE", $arFilter))
			$arFilter["ACTIVE"]="Y";

		$props = CIBlockElement::GetProperty($this->fields["IBLOCK_ID"], $this->fields["ID"], $arOrder, $arFilter);

		$arAllProps = Array();
		while($arProp = $props->Fetch())
		{
			if(strlen(trim($arProp["CODE"]))>0)
				$PIND = $arProp["CODE"];
			else
				$PIND = $arProp["ID"];

			if($arProp["PROPERTY_TYPE"]=="L")
			{
				$arProp["VALUE_ENUM_ID"] = $arProp["VALUE"];
				$arProp["VALUE"] = $arProp["VALUE_ENUM"];
			}

			if(is_array($arProp["VALUE"]) || (strlen($arProp["VALUE"]) > 0))
			{
				$arProp["~VALUE"] = $arProp["VALUE"];
				if(is_array($arProp["VALUE"]) || preg_match("/[;&<>\"]/", $arProp["VALUE"]))
					$arProp["VALUE"] = htmlspecialcharsEx($arProp["VALUE"]);
				$arProp["~DESCRIPTION"] = $arProp["DESCRIPTION"];
				if(preg_match("/[;&<>\"]/", $arProp["DESCRIPTION"]))
					$arProp["DESCRIPTION"] = htmlspecialcharsEx($arProp["DESCRIPTION"]);
			}
			else
			{
				$arProp["VALUE"] = $arProp["~VALUE"] = "";
				$arProp["DESCRIPTION"] = $arProp["~DESCRIPTION"] = "";
			}

			if($arProp["MULTIPLE"]=="Y")
			{
				if (isset($arAllProps[$PIND]))
				{
					if ($arAllProps[$PIND]['ID'] != $arProp['ID'])
						unset($arAllProps[$PIND]);
				}
				if (isset($arAllProps[$PIND]))
				{
					$arTemp = &$arAllProps[$PIND];
					if($arProp["VALUE"]!=="")
					{
						if(is_array($arTemp["VALUE"]))
						{
							$arTemp["VALUE"][] = $arProp["VALUE"];
							$arTemp["~VALUE"][] = $arProp["~VALUE"];
							$arTemp["DESCRIPTION"][] = $arProp["DESCRIPTION"];
							$arTemp["~DESCRIPTION"][] = $arProp["~DESCRIPTION"];
							$arTemp["PROPERTY_VALUE_ID"][] = $arProp["PROPERTY_VALUE_ID"];
							if($arProp["PROPERTY_TYPE"]=="L")
							{
								$arTemp["VALUE_ENUM_ID"][] = $arProp["VALUE_ENUM_ID"];
								$arTemp["VALUE_ENUM"][] = $arProp["VALUE_ENUM"];
								$arTemp["VALUE_XML_ID"][] = $arProp["VALUE_XML_ID"];
								$arTemp["VALUE_SORT"][] = $arProp["VALUE_SORT"];
							}
						}
						else
						{
							$arTemp["VALUE"] = array($arProp["VALUE"]);
							$arTemp["~VALUE"] = array($arProp["~VALUE"]);
							$arTemp["DESCRIPTION"] = array($arProp["DESCRIPTION"]);
							$arTemp["~DESCRIPTION"] = array($arProp["~DESCRIPTION"]);
							$arTemp["PROPERTY_VALUE_ID"] = array($arProp["PROPERTY_VALUE_ID"]);
							if($arProp["PROPERTY_TYPE"]=="L")
							{
								$arTemp["VALUE_ENUM_ID"] = array($arProp["VALUE_ENUM_ID"]);
								$arTemp["VALUE_ENUM"] = array($arProp["VALUE_ENUM"]);
								$arTemp["VALUE_XML_ID"] = array($arProp["VALUE_XML_ID"]);
								$arTemp["VALUE_SORT"] = array($arProp["VALUE_SORT"]);
							}
						}
					}
				}
				else
				{
					$arProp["~NAME"] = $arProp["NAME"];
					if(preg_match("/[;&<>\"]/", $arProp["NAME"]))
						$arProp["NAME"] = htmlspecialcharsEx($arProp["NAME"]);
					$arProp["~DEFAULT_VALUE"] = $arProp["DEFAULT_VALUE"];
					if(is_array($arProp["DEFAULT_VALUE"]) || preg_match("/[;&<>\"]/", $arProp["DEFAULT_VALUE"]))
						$arProp["DEFAULT_VALUE"] = htmlspecialcharsEx($arProp["DEFAULT_VALUE"]);
					if($arProp["VALUE"]!=="")
					{
						$arProp["VALUE"] = array($arProp["VALUE"]);
						$arProp["~VALUE"] = array($arProp["~VALUE"]);
						$arProp["DESCRIPTION"] = array($arProp["DESCRIPTION"]);
						$arProp["~DESCRIPTION"] = array($arProp["~DESCRIPTION"]);
						$arProp["PROPERTY_VALUE_ID"] = array($arProp["PROPERTY_VALUE_ID"]);
						if($arProp["PROPERTY_TYPE"]=="L")
						{
							$arProp["VALUE_ENUM_ID"] = array($arProp["VALUE_ENUM_ID"]);
							$arProp["VALUE_ENUM"] = array($arProp["VALUE_ENUM"]);
							$arProp["VALUE_XML_ID"] = array($arProp["VALUE_XML_ID"]);
							$arProp["VALUE_SORT"] = array($arProp["VALUE_SORT"]);
						}
					}
					else
					{
						$arProp["VALUE"] = false;
						$arProp["~VALUE"] = false;
						$arProp["DESCRIPTION"] = false;
						$arProp["~DESCRIPTION"] = false;
						$arProp["PROPERTY_VALUE_ID"] = false;
						if($arProp["PROPERTY_TYPE"]=="L")
						{
							$arProp["VALUE_ENUM_ID"] = false;
							$arProp["VALUE_ENUM"] = false;
							$arProp["VALUE_XML_ID"] = false;
							$arProp["VALUE_SORT"] = false;
						}
					}
					$arAllProps[$PIND] = $arProp;
				}
			}
			else
			{
				$arProp["~NAME"] = $arProp["NAME"];
				if(preg_match("/[;&<>\"]/", $arProp["NAME"]))
					$arProp["NAME"] = htmlspecialcharsEx($arProp["NAME"]);
				$arProp["~DEFAULT_VALUE"] = $arProp["DEFAULT_VALUE"];
				if(is_array($arProp["DEFAULT_VALUE"]) || preg_match("/[;&<>\"]/", $arProp["DEFAULT_VALUE"]))
					$arProp["DEFAULT_VALUE"] = htmlspecialcharsEx($arProp["DEFAULT_VALUE"]);
				$arAllProps[$PIND] = $arProp;
			}
		}

		return $arAllProps;
	}

	function GetProperty($ID)
	{
		$res = $this->GetProperties(array(), array("ID"=>$ID));
		list(, $res) = each($res);
		return $res;
	}

	function GetGroups()
	{
		return CIBlockElement::GetElementGroups($this->fields["ID"]);
	}
}