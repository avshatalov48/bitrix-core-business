<?php

namespace Bitrix\Im\V2\Marketplace;

class Placement
{
	public const IM_CONTEXT_MENU = 'IM_CONTEXT_MENU';
	public const IM_NAVIGATION = 'IM_NAVIGATION';
	public const IM_TEXTAREA = 'IM_TEXTAREA';
	public const IM_SIDEBAR = 'IM_SIDEBAR';
	public const IM_SMILES_SELECTOR = 'IM_SMILES_SELECTOR';

	public static function getPlacementList(): array
	{
		return [
			self::IM_CONTEXT_MENU,
			self::IM_NAVIGATION,
			self::IM_TEXTAREA,
			self::IM_SIDEBAR,
			self::IM_SMILES_SELECTOR,
		];
	}

	/**
	 * Event handler OnRestServiceBuildDescription of the Rest module
	 * @return array
	 */
	public static function onRestServiceBuildDescription(): array
	{
		return [
			\CRestUtil::GLOBAL_SCOPE => [
				\CRestUtil::PLACEMENTS => [
					self::IM_CONTEXT_MENU => [
						'options' => [
							'extranet' => [
								'type' => 'string',
								'default' => 'N',
								'require' => false,
							],
							'context' => [
								'type' => 'string',
								'default' => 'ALL',
								'require' => false,
							],
							'role' => [
								'type' => 'string',
								'default' => 'USER',
								'require' => false,
							],
						],
						'registerCallback' => [
							'moduleId' => 'im',
							'callback' => [self::class, 'onRegisterPlacementContextMenu'],
						],
					],
					self::IM_NAVIGATION => [
						'options' => [
							'iconName' => [
								'type' => 'string',
								'require' => true,
							],
							'extranet' => [
								'type' => 'string',
								'default' => 'N',
								'require' => false,
							],
							'role' => [
								'type' => 'string',
								'default' => 'USER',
								'require' => false,
							],
						],
						'registerCallback' => [
							'moduleId' => 'im',
							'callback' => [self::class, 'onRegisterPlacementNavigation'],
						],
					],
					self::IM_TEXTAREA => [
						'options' => [
							'iconName' => [
								'type' => 'string',
								'require' => true,
							],
							'extranet' => [
								'type' => 'string',
								'default' => 'N',
								'require' => false,
							],
							'context' => [
								'type' => 'string',
								'default' => 'ALL',
								'require' => false,
							],
							'role' => [
								'type' => 'string',
								'default' => 'USER',
								'require' => false,
							],
							'color' => [
								'type' => 'string',
								'require' => false,
							],
							'width' => [
								'type' => 'int',
								'default' => 100,
								'require' => false,
							],
							'height' => [
								'type' => 'int',
								'default' => 100,
								'require' => false,
							]
						],
						'registerCallback' => [
							'moduleId' => 'im',
							'callback' => [self::class, 'onRegisterPlacementTextArea'],
						],
					],
					self::IM_SIDEBAR => [
						'options' => [
							'iconName' => [
								'type' => 'string',
								'require' => true,
							],
							'extranet' => [
								'type' => 'string',
								'default' => 'N',
								'require' => false,
							],
							'context' => [
								'type' => 'string',
								'default' => 'ALL',
								'require' => false,
							],
							'role' => [
								'type' => 'string',
								'default' => 'USER',
								'require' => false,
							],
							'color' => [
								'type' => 'string',
								'require' => false,
							],
						],
						'registerCallback' => [
							'moduleId' => 'im',
							'callback' => [self::class, 'onRegisterPlacementSidebar'],
						],
					],
					self::IM_SMILES_SELECTOR => [
						'options' => [
							'extranet' => [
								'type' => 'string',
								'default' => 'N',
								'require' => false,
							],
							'role' => [
								'type' => 'string',
								'default' => 'USER',
								'require' => false,
							],
							'context' => [
								'type' => 'string',
								'default' => 'ALL',
								'require' => false,
							],
						],
						'registerCallback' => [
							'moduleId' => 'im',
							'callback' => [self::class, 'onRegisterPlacementSmilesSelector'],
						],
					],
				],
			],
		];
	}

	/**
	 * @see \Bitrix\Rest\Api\Placement::bind in section with $placementInfo['registerCallback']['callback']
	 * @param array $placementBind
	 * @param array $placementInfo
	 * @return array{error: ?string, error_description: ?string}
	 */
	public static function onRegisterPlacementContextMenu(array $placementBind, array $placementInfo): array
	{
		$result =
			RegistrationValidator::init($placementBind)
			->validateExtranet()
			->validateContext()
			->validateRole()
		;

		return $result->getResult();
	}

	/**
	 * @see \Bitrix\Rest\Api\Placement::bind in section with $placementInfo['registerCallback']['callback']
	 * @param array $placementBind
	 * @param array $placementInfo
	 * @return array{error: ?string, error_description: ?string}
	 */
	public static function onRegisterPlacementNavigation(array $placementBind, array $placementInfo): array
	{
		$result =
			RegistrationValidator::init($placementBind)
			->validateIconName()
			->validateExtranet()
			->validateRole()
		;

		return $result->getResult();
	}

	/**
	 * @see \Bitrix\Rest\Api\Placement::bind in section with $placementInfo['registerCallback']['callback']
	 * @param array $placementBind
	 * @param array $placementInfo
	 * @return array{error: ?string, error_description: ?string}
	 */
	public static function onRegisterPlacementTextArea(array $placementBind, array $placementInfo): array
	{
		$result =
			RegistrationValidator::init($placementBind)
			->validateIconName()
			->validateExtranet()
			->validateContext()
			->validateRole()
			->validateColor()
			->validateHeight()
			->validateWidth()
		;

		return $result->getResult();
	}

	/**
	 * @see \Bitrix\Rest\Api\Placement::bind in section with $placementInfo['registerCallback']['callback']
	 * @param array $placementBind
	 * @param array $placementInfo
	 * @return array{error: ?string, error_description: ?string}
	 */
	public static function onRegisterPlacementSidebar(array $placementBind, array $placementInfo): array
	{
		$result =
			RegistrationValidator::init($placementBind)
			->validateIconName()
			->validateExtranet()
			->validateContext()
			->validateRole()
			->validateColor()
		;

		return $result->getResult();
	}

	/**
	 * @see \Bitrix\Rest\Api\Placement::bind in section with $placementInfo['registerCallback']['callback']
	 * @param array $placementBind
	 * @param array $placementInfo
	 * @return array{error: ?string, error_description: ?string}
	 */
	public static function onRegisterPlacementSmilesSelector(array $placementBind, array $placementInfo): array
	{
		$result =
			RegistrationValidator::init($placementBind)
			->validateExtranet()
			->validateContext()
			->validateRole()
		;

		return $result->getResult();
	}

}