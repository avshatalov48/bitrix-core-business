<?php

abstract class CBPRuntimeService
{
	protected $runtime;

	public function setRuntime(CBPRuntime $runtime)
	{
		$this->runtime = $runtime;
	}

	public function start(CBPRuntime $runtime = null)
	{
		if ($runtime != null)
			$this->SetRuntime($runtime);
	}

	public function stop()
	{
		
	}
}
