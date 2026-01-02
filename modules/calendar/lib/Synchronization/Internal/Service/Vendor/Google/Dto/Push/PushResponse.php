<?php
declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\Push;

use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Service\Push\Dto\AbstractPushResponse;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;

/**
 * @link https://developers.google.com/calendar/api/v3/reference/events/watch#response
 * @link https://developers.google.com/calendar/api/v3/reference/calendarList/watch#response
 */
class PushResponse extends AbstractPushResponse
{
	/**
	 * @param string $id
	 * @param string $resourceId
	 * @param int|null $expiration
	 *
	 * @throws DtoValidationException
	 */
	public function __construct(
		string $id,
		string $resourceId,
		public readonly ?int $expiration = null
	)
	{
		parent::__construct($id, $resourceId);

		if ($expiration && $expiration < time() * 1000)
		{
			throw new DtoValidationException('The expiration value should be in the future');
		}
	}

	/**
	 * @param string $jsonResponse
	 *
	 * @return self
	 *
	 * @throws DtoValidationException
	 * @throws ArgumentException
	 */
	public static function fromJson(string $jsonResponse): self
	{
		$data = Json::decode($jsonResponse);

		$required = ['id', 'kind', 'resourceId'];

		foreach ($required as $key)
		{
			if (empty($data[$key]))
			{
				throw new DtoValidationException(sprintf('Field "%s" is required in PushResponse DTO', $key));
			}
		}

		if ($data['kind'] !== 'api#channel')
		{
			throw new DtoValidationException('Data type should be "api#channel"');
		}

		$expiration = isset($data['expiration']) ? (int)$data['expiration'] : null;

		return new self($data['id'], $data['resourceId'], $expiration);
	}

	public function getExpirationDateTime(): ?DateTime
	{
		return $this->expiration ? new DateTime($this->expiration / 1000, 'U') : null;
	}
}
