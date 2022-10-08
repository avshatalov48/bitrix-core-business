<?php

class CCMLResult extends CDBResult
{
	function Fetch()
	{
		$r = parent::Fetch();
		if ($r && !empty($r['ATTRIBUTES']))
		{
			$a = unserialize(
				$r['ATTRIBUTES'],
				['allowed_classes' => false]
			);
			if (is_array($a))
			{
				$r['ATTRIBUTES'] = $a;
			}
		}

		return $r;
	}
}
