<?php

namespace Bitrix\Mail\Disk\ProxyType;

use Bitrix\Main;
use Bitrix\Disk;

if (!Main\Loader::includeModule('disk'))
{
	return false;
}

class Mail extends Disk\ProxyType\Base
{

	/**
	 * @param $user
	 * @return \Bitrix\Disk\Security\SecurityContext
	 */
	public function getSecurityContextByUser($user)
	{
		return new \Bitrix\Mail\Disk\Security\MailSecurityContext($user);
	}

	/**
	 * @inheritdoc
	 */
	public function getStorageBaseUrl()
	{
		return '/';
	}

	/**
	 * @inheritdoc
	 */
	public function getEntityUrl()
	{
		return '/';
	}

	/**
	 * @inheritdoc
	 */
	public function getEntityTitle()
	{
		return 'mail';
	}

	/**
	 * @inheritdoc
	 */
	public function getEntityImageSrc($width, $height)
	{
		return '/bitrix/images/mail/mail.gif';
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		return $this->getEntityTitle();
	}

}
