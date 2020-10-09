<?php
namespace Bitrix\Main\UserField;

class Uri extends \Bitrix\Main\Web\Uri
{
	/**
	 * Return the URI with user, pass and fragment, if any.
	 * @return string
	 */
	public function getUri()
	{
		if($this->user == '' && $this->host <> '')
		{
			return parent::getUri();
		}

		$url = "";
		$url .= $this->scheme.':';

		if($this->scheme !== 'callto' && $this->scheme !== 'mailto')
		{
			$url .= "//";
		}

		if($this->user <> '')
		{
			$url .= $this->user.':'.$this->pass.'@';
		}

		$url .= $this->getASCIIHost();

		if(($this->scheme == "http" && $this->port <> 80) || ($this->scheme == "https" && $this->port <> 443))
		{
			$url .= ":".$this->port;
		}

		$url .= $this->getPathQuery();

		if($this->fragment <> '')
		{
			$url .= "#".$this->fragment;
		}

		return $url;
	}

	public function getASCIIHost()
	{
		if($this->host <> '')
		{
			$asciiHost = \CBXPunycode::ToASCII($this->host, $a);
			if($asciiHost)
			{
				return $asciiHost;
			}
		}

		return $this->host;
	}

}