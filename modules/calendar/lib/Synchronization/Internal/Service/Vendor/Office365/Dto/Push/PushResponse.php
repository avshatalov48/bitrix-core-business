<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\Push;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\Dto\AbstractPushResponse;
use Bitrix\Main\Type\DateTime;

/**
 * @link https://learn.microsoft.com/ru-ru/graph/api/subscription-post-subscriptions?view=graph-rest-1.0&tabs=http#response-1
 */
class PushResponse extends AbstractPushResponse
{
	public function __construct(
		string $id,
		string $resourceId,
		public readonly ?string $expirationDateTime = null
	)
	{
		parent::__construct($id, $resourceId);
	}

	/**
	 * @return DateTime
	 *
	 * @throws \DateMalformedStringException
	 */
	public function getExpirationDateTime(): DateTime
	{
		return $this->convertToDateTime($this->expirationDateTime ?: date('c', time() + 70 * 60 * 60));
	}

	/**
	 * @param array $data
	 *
	 * @return self
	 *
	 * @throws DtoValidationException
	 */
	public static function fromArray(array $data): self
	{
		$required = ['id', '@odata.context', 'clientState'];

		foreach ($required as $key)
		{
			if (empty($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in PushResponse DTO', $key));
			}
		}

		if ($data['@odata.context'] !== 'https://graph.microsoft.com/v1.0/$metadata#subscriptions/$entity')
		{
			throw new DtoValidationException(
				'Data type should be "https://graph.microsoft.com/v1.0/$metadata#subscriptions/$entity"'
			);
		}

		$expiration = $data['expirationDateTime'] ?? null;

		return new self($data['clientState'], $data['id'], $expiration);
	}

	/**
	 * @param string $time
	 *
	 * @return DateTime
	 *
	 * @throws \DateMalformedStringException
	 */
	private function convertToDateTime(string $time): DateTime
	{
		$phpDateTime = new \DateTime($time);

		return DateTime::createFromPhp($phpDateTime);
	}
}
