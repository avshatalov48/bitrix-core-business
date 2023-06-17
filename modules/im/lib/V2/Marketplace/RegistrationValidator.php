<?php

namespace Bitrix\Im\V2\Marketplace;

use Bitrix\Im\Color;
use Bitrix\Im\V2\Marketplace\Types\Context;
use Bitrix\Im\V2\Marketplace\Types\Role;

class RegistrationValidator
{
	private array $result;
	private array $placementBind;


	public static function init(array $placementBind): self
	{
		return new static($placementBind);
	}

	/**
	 * @param array $placementBind
	 */
	public function __construct(array $placementBind)
	{
		$this->result = [
			'error' => null,
			'error_description' => null,
		];

		$this->placementBind = $placementBind;
	}

	/**
	 * @return array{error?: string, error_description?: string}
	 */
	public function getResult(): array
	{
		return $this->result;
	}

	/**
	 * @return RegistrationValidator
	 */
	public function validateIconName(): self
	{
		if (!isset($this->placementBind['OPTIONS']['iconName']))
		{
			$this->result['error'] = 'EMPTY_ERROR_ICON_NAME';
			$this->result['error_description'] = 'Field iconName is empty.';

			return $this;
		}

		if (mb_strlen($this->placementBind['OPTIONS']['iconName']) > 50)
		{
			$this->result['error'] = 'INVALID_ERROR_ICON_NAME';
			$this->result['error_description'] = 'Field iconName is invalid.';

			return $this;
		}

		if (!preg_match('/[a-zA-Z \-]/', $this->placementBind['OPTIONS']['iconName']))
		{
			$this->result['error'] = 'INVALID_ERROR_ICON_NAME';
			$this->result['error_description'] = 'Field iconName is invalid.';

			return $this;
		}

		return $this;
	}

	/**
	 * @return RegistrationValidator
	 */
	public function validateExtranet(): self
	{
		if ($this->placementBind['OPTIONS']['extranet'] !== 'N' && $this->placementBind['OPTIONS']['extranet'] !== 'Y')
		{
			$this->result['error'] = 'INVALID_ERROR_EXTRANET';
			$this->result['error_description'] = 'Field extranet is invalid.';

			return $this;
		}

		return $this;
	}

	/**
	 * @return RegistrationValidator
	 */
	public function validateContext(): self
	{
		$userRawContext = $this->placementBind['OPTIONS']['context'];
		$userContextList = explode(';', trim($userRawContext));
		foreach ($userContextList as $context)
		{
			if (!in_array($context, Context::getTypes(), true))
			{
				$this->result['error'] = 'INVALID_ERROR_CONTEXT';
				$this->result['error_description'] = 'Field context is invalid.';

				return $this;
			}
		}

		return $this;
	}

	/**
	 * @return RegistrationValidator
	 */
	public function validateRole(): self
	{
		if (!in_array($this->placementBind['OPTIONS']['role'], Role::getTypes(), true))
		{
			$this->result['error'] = 'INVALID_ERROR_ROLE';
			$this->result['error_description'] = 'Field role is invalid.';
		}

		return $this;
	}

	public function validateColor(): self
	{
		if (
			$this->placementBind['OPTIONS']['color'] !== ''
			&& !array_key_exists($this->placementBind['OPTIONS']['color'], Color::getColors())
		)
		{
			$this->result['error'] = 'INVALID_ERROR_COLOR';
			$this->result['error_description'] = 'Field color is invalid.';
		}

		return $this;
	}

	public function validateHeight(): self
	{
		if ($this->placementBind['OPTIONS']['height'] < 0)
		{
			$this->result['error'] = 'INVALID_ERROR_HEIGHT';
			$this->result['error_description'] = 'Field height is invalid.';
		}

		return $this;
	}

	public function validateWidth(): self
	{
		if ($this->placementBind['OPTIONS']['width'] < 0)
		{
			$this->result['error'] = 'INVALID_ERROR_WIDTH';
			$this->result['error_description'] = 'Field width is invalid.';
		}

		return $this;
	}

}