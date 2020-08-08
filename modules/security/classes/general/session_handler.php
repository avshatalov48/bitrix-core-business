<?php
class CSecuritySessionHandler extends SessionHandler
{
	private $class;

	public function __construct($class)
	{
		$this->class = $class;
	}

	public function open($save_path, $session_name)
	{
		return call_user_func_array(array($this->class, 'open'), array($save_path, $session_name));
	}

	public function close()
	{
		return call_user_func_array(array($this->class, 'close'), array());
	}

	public function read($session_id)
	{
		return call_user_func_array(array($this->class, 'read'), array($session_id));
	}

	public function write($session_id, $session_data)
	{
		return call_user_func_array(array($this->class, 'write'), array($session_id, $session_data));
	}

	public function destroy($session_id)
	{
		return call_user_func_array(array($this->class, 'destroy'), array($session_id));
	}

	public function gc($maxlifetime)
	{
		return call_user_func_array(array($this->class, 'gc'), array($maxlifetime));
	}

	public function create_sid()
	{
		return \Bitrix\Main\Security\Random::getString(32, true);
	}
}