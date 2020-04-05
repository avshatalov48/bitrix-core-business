<?php

namespace Bitrix\Sale\TradingPlatform;

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

/**
 * Class Sftp
 * Transfer files via sftp
 * @package Bitrix\Sale\TradingPlatform
 */
class Sftp
{
	protected $login;
	protected $pass;
	protected $host;
	protected $port;
	protected $fingerprint;

	protected $connection;
	protected $sftp;

	/**
	 * Constructor.
	 * @param string $login Sftp login.
	 * @param string $pass Sftp password.
	 * @param string $host Sftp host.
	 * @param int $port Sftp port.
	 * @param string $fingerprint Hostkey hash.
	 */
	public function __construct($login, $pass, $host, $port, $fingerprint="")
	{
		$this->host = $host;
		$this->login = $login;
		$this->pass = $pass;
		$this->port = $port;
		$this->fingerprint = $fingerprint;
	}

	/**
	 * Makes connection via SFTP
	 * @return bool.
	 * @throws \Bitrix\Main\SystemException
	 */
	public function connect()
	{
		if(!extension_loaded("ssh2"))
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_SSH2_EXT"));

		$this->connection = @ssh2_connect($this->host, $this->port);

		if(!$this->connection)
		{
			throw new SystemException(
				Loc::getMessage(
					"TRADING_PLATFORM_SFTP_ERROR_CONNECT",
					array(
						"#HOST#" => $this->host,
						"#PORT#" => $this->port
					)
				)
			);
		}

		if($this->fingerprint != "")
		{
			$fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);

			if ($fingerprint != $this->fingerprint)
			{
				throw new SystemException(Loc::getMessage(
					"TRADING_PLATFORM_SFTP_ERROR_FINGERPRINT",
					array(
						"#HOST#" => $this->host,
						"#FINGERPRINT1#" => $fingerprint,
						"#FINGERPRINT2#" => $this->fingerprint,
					)
				));
			}
		}

		if(!@ssh2_auth_password($this->connection, $this->login, $this->pass))
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_PASS"));

		$this->sftp = ssh2_sftp($this->connection);

		if(!$this->sftp)
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_INIT"));

		return true;
	}

	/**
	 * @param string $localFile Path to local file.
	 * @param string $remoteFile Path to remote file.
	 * @return bool.
	 * @throws \Bitrix\Main\SystemException
	 */
	public function uploadFile($localFile, $remoteFile)
	{
		$remotePath = "sftp://".intval($this->sftp).$remoteFile;
		$stream = @fopen("ssh2.".$remotePath, 'w');

		if (!$stream)
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_OPEN_FILE", array("#FILE#" => $remotePath)));

		$data = file_get_contents($localFile);

		if ($data === false)
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_READ_FILE", array("#FILE#" => $localFile)));

		if (fwrite($stream, $data) === false)
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_WRITE_FILE", array("#FILE#" => $remotePath)));

		@fclose($stream);

		return true;
	}

	/**
	 * @param string $remoteFile Path to remote file.
	 * @param string $localFile Path to local file.
	 * @return bool.
	 * @throws \Bitrix\Main\SystemException
	 */
	public function downloadFile($remoteFile, $localFile)
	{
		$remotePath = "sftp://".intval($this->sftp).$remoteFile;
		$stream = @fopen("ssh2.".$remotePath, 'r');

		if (!$stream)
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_OPEN_FILE", array("#FILE#" => $remotePath)));

		$contents = stream_get_contents($stream);

		if(file_put_contents($localFile, $contents) === false)
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_WRITE_FILE", array("#FILE#" => $localFile)));

		@fclose($stream);
		return true;
	}

	/**
	 * @param string $remotePath Remote path.
	 * @return array List of files from remote path.
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getFilesList($remotePath)
	{
		$result = array();
		$fullPath = "sftp://".intval($this->sftp).$remotePath;
		$dirHandle = @opendir("ssh2.".$fullPath);

		if($dirHandle === false)
			throw new SystemException(Loc::getMessage("TRADING_PLATFORM_SFTP_ERROR_OPEN_PATH", array("#PATH#" => $fullPath)));

		while (false !== ($file = readdir($dirHandle)))
			if(is_file("ssh2.".$fullPath."/".$file))
				$result[] = $file;

		return $result;
	}

	/**
	 * @param string $remoteFile Remote file.
	 * @return int Filesize.
	 */
	public function getFileSize($remoteFile)
	{
		return filesize("ssh2.sftp://".intval($this->sftp).$remoteFile);
	}
} 