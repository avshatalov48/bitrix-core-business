<?php
namespace Bitrix\Landing\Block;

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Restriction;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Designer
{
	/**
	 * Current block for design.
	 * @var Block|null
	 */
	protected $block = null;

	/**
	 * Designer constructor.
	 * @param int $blockId block id.
	 */
	public function __construct(int $blockId)
	{
		$block = new Block($blockId, [], [
			'designer_mode' => true
		]);
		if ($block->exist())
		{
			$this->block = $block;
		}
	}

	/**
	 * Returns true if current designer filled up with block.
	 * @return bool
	 */
	public function isReady(): bool
	{
		return $this->block !== null;
	}

	/**
	 * Returns current block.
	 * @return Block|null
	 */
	public function getBlock(): ?Block
	{
		return $this->block;
	}

	/**
	 * Registers onLandingView callback for set design block flag.
	 * @param bool $flag Design block flag.
	 * @return void
	 */
	public static function setLandingDesignBlockMode(bool $flag): void
	{
		$eventManager = EventManager::getInstance();
		$eventManager->addEventHandler('landing', 'onLandingView',
			function(\Bitrix\Main\Event $event) use($flag)
			{
				$result = new \Bitrix\Main\Entity\EventResult;
				$options = $event->getParameter('options');
				$options['design_block'] = $flag;
				$options['design_block_allowed'] = Restriction\Manager::isAllowed('limit_crm_superblock');
				$result->modifyFields([
					'options' => $options
				]);
				return $result;
			}
		);
	}

	/**
	 * Exec block's landing and site hooks.
	 * @see \Bitrix\Landing\Landing::execHooks
	 * @return void
	 */
	public function execHooks(): void
	{
		$hooksExec = [];

		foreach (Hook::getForSite($this->block->getSiteId()) as $hook)
		{
			if ($hook->enabled())
			{
				$hooksExec[$hook->getCode()] = $hook;
			}
		}

		foreach (Hook::getForLanding($this->block->getLandingId()) as $hook)
		{
			if ($hook->enabled())
			{
				$hooksExec[$hook->getCode()] = $hook;
			}
		}

		foreach ($hooksExec as $hook)
		{
			if ($hook->enabledInEditMode())
			{
				$hook->exec();
			}
		}
	}

	/**
	 * Static getter for repository.
	 * @return array
	 */
	protected static function getRepo(): array
	{
		static $repo = [];

		if ($repo)
		{
			return $repo;
		}

		$res = DesignerRepo::getList([
			'order' => [
				'SORT' => 'asc'
			]
		]);
		while ($row = $res->fetch())
		{
			$repo[] = $row;
		}

		return $repo;
	}

	/**
	 * Adjusts styles for manifest (for specific selectors adds new features).
	 * @param string $selector Selector code.
	 * @param array $item Manifest item's style section.
	 * @return void
	 */
	private static function adjustStylesType(string $selector, array &$item): void
	{
		if ($selector === 'landing-block-node-title')
		{
			$item['type'] = (array)$item['type'];
			$item['type'][] = 'heading';
		}
	}

	/**
	 * Returns map of references between classes and type.
	 * @return array
	 */
	protected static function getTypeClassReferences(): array
	{
		$references = [];
		$selectorName = [];

		foreach (self::getRepo() as $repoItem)
		{
			if (
				!isset($repoItem['MANIFEST']) ||
				!is_array($repoItem['MANIFEST'])
			)
			{
				continue;
			}
			ksort($repoItem['MANIFEST']);
			foreach (['nodes', 'style'] as $category)
			{
				if (isset($repoItem['MANIFEST'][$category]))
				{
					foreach ($repoItem['MANIFEST'][$category] as $selector => $item)
					{
						if (!is_array($item))
						{
							continue;
						}
						$selector = trim($selector, '.');
						if (!isset($references[$selector]))
						{
							$references[$selector] = [];
						}
						if (!isset($references[$selector][$category]))
						{
							$references[$selector][$category] = [];
						}
						if (!isset($selectorName[$selector]))
						{
							$selectorName[$selector] = Loc::getMessage('LANDING_DESIGN_NODE_' . mb_strtoupper($selector));
						}
						if ($category === 'style')
						{
							self::adjustStylesType($selector, $item);
						}
						$item['name'] = $selectorName[$selector];
						$references[$selector][$category] = $item;
						if (
							isset($repoItem['MANIFEST']['assets']) &&
							is_array($repoItem['MANIFEST']['assets'])
						)
						{
							$references[$selector]['assets'] = $repoItem['MANIFEST']['assets'];
						}
					}
				}
			}
		}

		return $references;
	}

	/**
	 * Returns additional manifest nodes from content.
	 * @param string $content Block content.
	 * @return array
	 */
	public static function parseManifest(string $content): array
	{
		static $references = [];
		$manifest = [
			'nodes' => [],
			'style' => [],
		];

		if (!$references)
		{
			$references = self::getTypeClassReferences();
		}

		if (preg_match_all('/[\s"]+((' . implode('|', array_keys($references)) . ')[-\d]*)[\s"]+/is', $content, $matches))
		{
			foreach ($matches[2] as $i => $selector)
			{
				if (isset($references[$selector]))
				{
					foreach ($manifest as $code => &$items)
					{
						if (isset($references[$selector][$code]))
						{
							$items['.'.$matches[1][$i]] = $references[$selector][$code];
						}
					}
					unset($items);
					if (isset($references[$selector]['assets']))
					{
						$manifest['assets'] = $references[$selector]['assets'];
					}
				}
			}
		}

		return $manifest;
	}

	/**
	 * Returns repository's elements.
	 * @param bool $installRepo Install repo if empty result.
	 * @return array
	 */
	public static function getRepository(bool $installRepo = true): array
	{
		$repo = [];

		foreach (self::getRepo() as $row)
		{
			$repo[] = [
				'name' => $row['TITLE'] ?: Loc::getMessage('LANDING_DESIGNER_REPO_ELEM_' . mb_strtoupper($row['XML_ID'])),
				'code' => $row['XML_ID'],
				'html' => $row['HTML'],
				'manifest' => $row['MANIFEST']
			];
		}

		if (!$repo && $installRepo)
		{
			DesignerRepo::installRepo();
			return self::getRepository(false);
		}

		return $repo;
	}

	/**
	 * Registers new repo element.
	 * @param array $fields Data array ([XML_ID, ?TITLE, HTML, MANIFEST]).
	 * @return void
	 */
	public static function registerRepoElement(array $fields): void
	{
		if (isset($fields['XML_ID']) && is_string($fields['XML_ID']))
		{
			$res = DesignerRepo::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'=XML_ID' => $fields['XML_ID']
				]
			]);
			if ($row = $res->fetch())
			{
				DesignerRepo::update($row['ID'], $fields);
			}
			else
			{
				DesignerRepo::add($fields)->isSuccess();
			}
		}
	}

	/**
	 * Removes repo element by code.
	 * @param string $code External code.
	 * @return void
	 */
	public static function unregisterRepoElement(string $code): void
	{
		$res = DesignerRepo::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=XML_ID' => $code
			]
		]);
		if ($row = $res->fetch())
		{
			DesignerRepo::delete($row['ID']);
		}
	}
}