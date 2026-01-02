<?php

namespace Bitrix\Vote\Service;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Main\Web\Uri;
use Bitrix\Vote\Attach;
use Bitrix\Vote\AttachTable;

class AttachedVoteResultUrlService
{
	private const SIGN_SEPARATOR = '.';

	private AttachedVoteSigner $signer;

	public function __construct()
	{
		$this->signer = new AttachedVoteSigner();
	}

	public function getResultUrl(string $signedAttachId, ?string $uid): string
	{
		$id = $uid ?? $signedAttachId;

		return "/vote-result/$id";
	}

	/**
	 * @param string $id
	 *
	 * @return Attach
	 * @throws BadSignatureException
	 * @throws ObjectNotFoundException
	 * @throws ArgumentTypeException
	 */
	public function getAttachByUrlId(string $id): Attach
	{
		return str_contains($id, self::SIGN_SEPARATOR) ? $this->getAttachBySignedId($id) : $this->getAttachByUID($id);
	}

	/**
	 * @param string $signedId
	 *
	 * @return Attach
	 * @throws BadSignatureException
	 * @throws ArgumentTypeException
	 */
	private function getAttachBySignedId(string $signedId): Attach
	{
		$attachId = $this->signer->unsign($signedId);

		return new Attach($attachId);
	}

	/**
	 * @param string $uid
	 *
	 * @return Attach
	 * @throws ObjectNotFoundException
	 */
	private function getAttachByUID(string $uid): Attach
	{
		$attachId = AttachTable::getIdByUid($uid);
		if ($attachId)
		{
			return new Attach($attachId);
		}

		throw new ObjectNotFoundException();
	}

	public function getAbsoluteResultUrl(string $signedAttachId, ?string $uid = null): ?string
	{
		$host = $this->getHost();
		if (!$host)
		{
			return null;
		}

		$request = Context::getCurrent()?->getRequest();
		$protocol = $request?->isHttps() ? 'https' : 'http';
		$port = (int)$request?->getServerPort();

		$portSuffix = $port && !in_array($port, [443, 80], true) ? ":$port" : '';
		$parsedUri = new Uri($protocol . '://' . $host . $portSuffix);
		$parsedUri->setPath($this->getResultUrl($signedAttachId, $uid));

		return rtrim($parsedUri->getLocator(), '/');
	}

	private function getHost(): ?string
	{
		$fromRequest = Context::getCurrent()?->getRequest()?->getHttpHost();
		if ($fromRequest)
		{
			return $fromRequest;
		}

		if (defined('BX24_HOST_NAME') && BX24_HOST_NAME)
		{
			return BX24_HOST_NAME;
		}

		if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME)
		{
			return SITE_SERVER_NAME;
		}

		return Option::get('main', 'server_name', null);
	}
}