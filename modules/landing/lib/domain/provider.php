<?php
namespace Bitrix\Landing\Domain;

abstract class Provider
{
	/**
	 * Provider constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Returns unique provider code.
	 * @return string
	 */
	abstract public function getCode(): string;

		/**
	 * Returns true, if provider is available.
	 * @return bool
	 */
	abstract public function enable(): bool;

	/**
	 * Returns true, if domain is available for registration.
	 * @param string $domainName Domain name
	 * @return bool
	 */
	abstract public function isEnableForRegistration(string $domainName): bool;

	/**
	 * Returns suggested domains by basic domain name.
	 * @param string $domainName Domain name.
	 * @param array $tld Domain tld.
	 * @return array
	 */
	abstract public function getSuggestedDomains(string $domainName, array $tld): array;

	/**
	 * Registration new domain. Returns true on success create.
	 * @param string $domainName Domain name.
	 * @param array $params Additional params.
	 * @return bool
	 */
	abstract public function registrationDomain(string $domainName, array $params = []): bool;

	/**
	 * Returns all current portal domains.
	 * @return array
	 */
	abstract public function getPortalDomains(): array;
}