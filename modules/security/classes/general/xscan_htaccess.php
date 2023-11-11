<?php

class CBitrixXscanHtaccess {

	public $doc_root = null;
	public $start_time = null;
	public $time_limit = null;
	public $break_point = null;
	public $skip_path = null;
	public $found = false;
	public $result = [];

	function __construct(array $result = [])
	{
		$this->doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
		$this->start_time = time();

		$mem = (int)ini_get('memory_limit');
		$this->time_limit = ini_get('max_execution_time') ?: 30;
		$this->time_limit = min($this->time_limit, 30);
		$this->time_limit = $this->time_limit * 0.7;

		$this->result = $result;

	}


	function Search($path)
	{
		$path = str_replace('\\', '/', $path);
		do
		{
			$path = str_replace('//', '/', $path, $flag);
		}
		while ($flag);

		if ($this->start_time && time() - $this->start_time > $this->time_limit)
		{
			if (!$this->break_point)
			{
				$this->break_point = $path;
			}
			return;
		}

		if ($this->skip_path && !$this->found)
		{
			if (strpos($this->skip_path, dirname($path)) !== 0)
			{
				return;
			}

			if ($this->skip_path == $path)
			{
				$this->found = true;
			}
		}

		if (is_dir($path)) // dir
		{
			$p = realpath($path);

			if (is_link($path))
			{
				$d = dirname($path);
				if (strpos($p, $d) !== false || strpos($d, $p) !== false)
				{
					return true;
				}
			}

			$dir = opendir($path);

			while ($item = readdir($dir))
			{
				if ($item == '.' || $item == '..')
				{
					continue;
				}

				$this->Search($path . '/' . $item);
			}
			closedir($dir);
		}
		elseif (preg_match('/\.htaccess$/', $path))
		{
			if (!$this->skip_path || $this->found)
			{
				$this->result[] = $path;
			}
		}
	}
}
