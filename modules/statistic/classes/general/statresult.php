<?php
class CStatResult extends CDBResult
{
	function Fetch()
	{
		global $DB;
		$res = parent::Fetch();

		if(!isset($this) || !is_object($this))
			return $res;

		if($res)
		{
			if(isset($res["USER_ID"])) $USER_ID = intval($res["USER_ID"]);
			elseif(isset($res["LAST_USER_ID"])) $USER_ID = intval($res["LAST_USER_ID"]);
			else $USER_ID = 0;

			if($USER_ID > 0 && !isset($res["LOGIN"]))
			{
				$rsUser = $DB->Query("
					SELECT LOGIN, ".$DB->Concat($DB->IsNull("NAME", "''"), "' '", $DB->IsNull("LAST_NAME", "''"))." USER_NAME
					FROM b_user
					WHERE ID = ".$USER_ID."
				");
				$arUser = $rsUser->Fetch();

				if(is_array($arUser))
				{
					$res["LOGIN"] = $arUser["LOGIN"];
					$res["USER_NAME"] = $arUser["USER_NAME"];
				}
				else
				{
					$res["LOGIN"] = "";
					$res["USER_NAME"] = " ";
				}
			}
		}

		return $res;
	}
}
