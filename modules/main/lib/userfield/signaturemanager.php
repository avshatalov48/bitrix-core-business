<?php
namespace Bitrix\Main\UserField;


use Bitrix\Main\Security\Sign\Signer;

class SignatureManager
{
	/**
	 * @var Signer
	 */
	protected $signer;

	public function __construct()
	{
	}

	public function getSignature($data)
	{
		return $this->getSigner()->getSignature($data, $this->getSignatureSalt());
	}

	public function validateSignature($data, $signature)
	{
		return $this->getSigner()->validate(
			$data,
			$signature,
			$this->getSignatureSalt()
		);
	}

	/**
	 * @return Signer
	 */
	public function getSigner()
	{
		if(!$this->signer)
		{
			$this->setDefaultSigner();
		}

		return $this->signer;
	}

	/**
	 * @param Signer $signer
	 */
	public function setSigner(Signer $signer)
	{
		$this->signer = $signer;
	}

	protected function setDefaultSigner()
	{
		$this->setSigner(new Signer());
	}

	protected function getSignatureSalt()
	{
		return bitrix_sessid();
	}
}