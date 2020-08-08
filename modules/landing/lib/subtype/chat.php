<?php
namespace Bitrix\Landing\Subtype;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Chat
{
	/**
	 * Prepare manifest.
	 * @param array $manifest Block's manifest.
	 * @param \Bitrix\Landing\Block $block Block instance.
	 * @param array $params Additional params.
	 * @return array
	 */
	public static function prepareManifest(array $manifest, \Bitrix\Landing\Block $block = NULL, array $params = []): array
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('im'))
		{
			$manifest['requiredUserAction'] = array(
				'header' => Loc::getMessage('LANDING_BLOCK_IM_NOT_INSTALLED_HEADER'),
				'description' => Loc::getMessage('LANDING_BLOCK_IM_NOT_INSTALLED_TEXT'),
				'text' => Loc::getMessage('LANDING_BLOCK_IM_NOT_INSTALLED_LINK'),
				'href' => '/bitrix/admin/module_admin.php?lang=' . LANGUAGE_ID,
				'className' => 'landing-required-link'
			);
		}
		$manifest['callbacks'] = array(
			'afterAdd' => function (\Bitrix\Landing\Block &$block) use($params)
			{
				$block->saveDynamicParams(
					self::getSourceParams($params)
				);
			},
		);

		return $manifest;
	}

	/**
	 * Prepares and returns references array for dynamic block.
	 * @param array $references Raw preferences array.
	 * @return array
	 */
	protected static function buildReferences(array $references): array
	{
		$return = [];

		foreach ($references as $fieldCode => $selector)
		{
			if (is_string($selector))
			{
				$return[$selector] = [
					'id' => $fieldCode,
					'link' => false
				];
			}
		}

		return $return;
	}

	/**
	 * Prepares and returns source params for dynamic block.
	 * @param array $params Subtype params.
	 * @return array
	 */
	protected static function getSourceParams(array $params): array
	{
		$return = [];

		if (
			isset($params['type']) && is_string($params['type']) &&
			isset($params['card']) && is_string($params['card']) &&
			isset($params['attributeData']) && is_string($params['attributeData']) &&
			isset($params['references']) && is_array($params['references'])
		)
		{
			$return[$params['card']] = [
				'source' => 'landing:chat',
				'references' => self::buildReferences($params['references']),
				'settings' => [
					'source' => [
						'source' => 'landing:chat',
						'additional' => [
							'type' => $params['type'],
							'attributeData' => $params['attributeData'],
							'attributeButton' => isset($params['attributeButton'])
												? $params['attributeButton']
												: null
						]
					],
					'pagesCount' => isset($params['references'])
									? $params['references']
									: 10
				]
			];
		}

		return $return;
	}
}
