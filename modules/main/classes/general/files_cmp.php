<?php

class FilesCmp
{
	public static function cmp_size_asc($a, $b)
	{
		if($a["SIZE"] == $b["SIZE"])
			return 0;
		return ($a["SIZE"] < $b["SIZE"]) ? -1 : 1;
	}
	public static function cmp_size_desc($a, $b)
	{
		if ($a["SIZE"] == $b["SIZE"])
			return 0;
		return ($a["SIZE"] > $b["SIZE"]) ? -1 : 1;
	}
	public static function cmp_timestamp_asc($a, $b)
	{
		if($a["TIMESTAMP"] == $b["TIMESTAMP"])
			return 0;
		return ($a["TIMESTAMP"] < $b["TIMESTAMP"]) ? -1 : 1;
	}
	public static function cmp_timestamp_desc($a, $b)
	{
		if ($a["TIMESTAMP"] == $b["TIMESTAMP"])
			return 0;
		return ($a["TIMESTAMP"] > $b["TIMESTAMP"]) ? -1 : 1;
	}
	public static function cmp_name_asc($a, $b)
	{
		if($a["NAME"] == $b["NAME"])
			return 0;
		return ($a["NAME"] < $b["NAME"]) ? -1 : 1;
	}
	public static function cmp_name_desc($a, $b)
	{
		if($a["NAME"] == $b["NAME"])
			return 0;
		return ($a["NAME"] > $b["NAME"]) ? -1 : 1;
	}
	public static function cmp_name_nat_asc($a, $b)
	{
		$cmp = strnatcasecmp(trim($a["NAME"]), trim($b["NAME"]));
		if($cmp == 0)
			$cmp = strnatcmp(trim($a["NAME"]), trim($b["NAME"]));
		return $cmp;
	}
	public static function cmp_name_nat_desc($a, $b)
	{
		$cmp = strnatcasecmp(trim($a["NAME"]), trim($b["NAME"]));
		if($cmp == 0)
			$cmp = strnatcmp(trim($a["NAME"]), trim($b["NAME"]));
		return $cmp*(-1);
	}
}
