<?php

namespace Bitrix\Main\Mail\Smtp;

/**
 * Alters behavior of parent, for work with string token
 */
class PreparedOauthCredentials extends \PHPMailer\PHPMailer\OAuth
{
	/**
	 * Constructor
	 *
	 * @param array $options Options [userName => email@domain.com, token => string]
	 */
	public function __construct(array $options)
	{
		parent::__construct($options);
		$this->oauthToken = $options['token'];
	}

	/**
	 * Generate a base64-encoded OAuth token.
	 *
	 * @return string
	 */
	public function getOauth64(): string
	{
		return base64_encode(
			'user=' .
			$this->oauthUserEmail .
			"\001auth=Bearer " .
			$this->oauthToken .
			"\001\001"
		);
	}
}
