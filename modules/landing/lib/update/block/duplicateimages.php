<?php

namespace Bitrix\Landing\Update\Block;

use Bitrix\Landing\File;
use Bitrix\Landing\Internals\FileTable;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Landing\Block;
use Bitrix\Landing\Node;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\EO_File;

/**
 * After enable file duplicate control for all and run the migrator,
 * some image urls in blocks have become incorrect (then has duplicate).
 * Need updater, then fix links
 */
class DuplicateImages
{
	protected const IMG_TYPES = ['img', 'styleimg'];

	protected Block $block;
	protected array $manifest;
	protected string $content;

	/**
	 * @param int|null $blockId
	 * @param array $params
	 */
	public function __construct(?int $blockId, array $params = [])
	{
		if (
			!empty($params)
			&& $params['block']
			&& $params['content']
		)
		{
			$this->block = $params['block'];
			$this->content = $params['content'];
			$this->manifest = $this->block->getManifest();
		}
		elseif ($blockId)
		{
			$this->block = new Block($blockId);
			$this->content = $this->block->getContent();
			$this->manifest = $this->block->getManifest();
		}
		// todo: error, if has no params or blockId
	}

	/**
	 * @param bool $needSave - if false - just return replaced content.True - save changes in DB
	 * @return string
	 * @throws ArgumentTypeException
	 */
	public function update(bool $needSave = false): string
	{
		$srcForReplace = [];
		if (isset($this->manifest['nodes']) && is_array($this->manifest['nodes']))
		{
			foreach ($this->manifest['nodes'] as $selector => $node)
			{
				if (in_array($node['type'], self::IMG_TYPES, true))
				{
					$nodeClass = Node\Type::getClassName($node['type']);
					$previousValues = call_user_func([$nodeClass, 'getNode'], $this->block, $selector);

					foreach ($previousValues as $previousValue)
					{
						if (!($previousValue['id'] ?? null) && !($previousValue['id2x'] ?? null))
						{
							continue;
						}

						if (
							($previousValue['id'] && !File::getFilePath($previousValue['id']))
							|| ($previousValue['id2x'] && !File::getFilePath($previousValue['id2x']))
						)
						{
							continue;
						}

						$hostUrl = '//' . Manager::getHttpHost();

						$newSrc = File::getFilePath($previousValue['id']);
						$newSrc = Manager::getUrlFromFile($newSrc);
						$previousSrc =
							$previousValue['isLazy'] === 'Y'
								? $previousValue['lazyOrigSrc']
								: $previousValue['src']
						;
						if ($previousSrc !== $newSrc)
						{
							$srcForReplace[$previousSrc] = $newSrc;

							// add urls w/o host url too
							if (strpos($newSrc, $hostUrl) === 0)
							{
								$srcForReplace[str_replace($hostUrl, '', $previousSrc)] =
									str_replace($hostUrl, '', $newSrc);
							}
						}

						if ($previousValue['id2x'])
						{
							$newSrc2x = File::getFilePath($previousValue['id2x']);
							$newSrc2x = Manager::getUrlFromFile($newSrc2x);
							$previousSrc2x =
								$previousValue['isLazy'] === 'Y'
									? $previousValue['lazyOrigSrc2x']
									: $previousValue['src2x']
							;
							if ($previousSrc2x !== $newSrc2x)
							{
								$srcForReplace[$previousSrc2x] = $newSrc2x;

								// add urls w/o host url too
								if (strpos($newSrc2x, $hostUrl) === 0)
								{
									$srcForReplace[str_replace($hostUrl, '', $previousSrc2x)] =
										str_replace($hostUrl, '', $newSrc2x);
								}
							}
						}
					}
				}
			}
		}

		if (!empty($srcForReplace))
		{
			$this->content = str_replace(
				array_keys($srcForReplace),
				array_values($srcForReplace),
				$this->content
			);

			if ($needSave)
			{
				$this->block->saveContent($this->content);
				$this->block->save();
			}
		}

		return $this->content;
	}

	public static function updateLanding(int $lid): void
	{
		$res = BlockTable::getList(
			[
				'select' => [
					'ID', 'CONTENT',
				],
				'filter' => [
					'LID' => $lid,
				],
				'order' => 'SORT',
			]
		);
		while ($row = $res->fetch())
		{
			$block = new self($row['ID']);
			$block->update(true);
		}
	}

	public static function onAfterFileDeleteDuplicate(EO_File $original, EO_File $duplicate)
	{
		// BLOCKS
		$sitesToUpdate = [];
		$oldPath = '/' . $duplicate->getSubdir() . '/' . $duplicate->getFileName();
		$newPath = '/' . $original->getSubdir() . '/' . $original->getFileName();

		$resFiles = FileTable::query()
			->addSelect('ENTITY_ID')
			->where('ENTITY_TYPE', '=', File::ENTITY_TYPE_BLOCK)
			->where('FILE_ID', $duplicate->getId())
			->exec()
		;
		while($blockFile = $resFiles->fetch())
		{
			$resBlocks = BlockTable::query()
				->addSelect('ID')
				->addSelect('CONTENT')
				->addSelect('LANDING.SITE_ID')
				->where('ID', $blockFile['ENTITY_ID'])
				->exec()
			;
			while($block = $resBlocks->fetch())
			{
				BlockTable::update($block['ID'], [
					'CONTENT' => str_replace($oldPath, $newPath, $block['CONTENT'])
				]);
				$siteId = (int)$block['LANDING_INTERNALS_BLOCK_LANDING_SITE_ID'];
				if ($siteId && $siteId > 0)
				{
					$sitesToUpdate[] = $siteId;
				}
			}
		}

		$sitesToUpdate = array_unique($sitesToUpdate);
		foreach($sitesToUpdate as $siteId)
		{
			Manager::clearCacheForSite($siteId);
		}

		// ASSETS
		$landingsForUpdate = [];
		$resFiles = FileTable::query()
			->addSelect('ENTITY_ID')
			->where('ENTITY_TYPE', '=', File::ENTITY_TYPE_ASSET)
			->where('FILE_ID', $duplicate->getId())
			->exec()
		;
		while($assetFile = $resFiles->fetch())
		{
			$landingsForUpdate[] = abs((int)$assetFile['ENTITY_ID']);
		}
		$landingsForUpdate = array_unique($landingsForUpdate);
		foreach($landingsForUpdate as $landingId)
		{
			Manager::clearCacheForLanding($landingId);
		}
	}
}