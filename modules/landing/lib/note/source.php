<?php
namespace Bitrix\Landing\Note;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\File;

class Source
{
	/**
	 * Supported sources (modules).
	 */
	const AVAILABLE_SOURCES = ['blog', 'blogcomment', 'taskcomment', 'livefeed'];

	/**
	 * Block codes for different content types.
	 */
	const BLOCKS = [
		'header' => [
			'code' => '27.2.one_col_full_title',
			'selector' => '.landing-block-node-title'
		],
		'text' => [
			'code' => '27.6.one_col_fix_text_with_headings',
			'selector' => '.landing-block-node-text'
		],
		'quote' => [
			'code' => '27.5.one_col_fix_text_with_border',
			'selector' => '.landing-block-node-text'
		],
		'code' => [
			'code' => '27.7.one_col_fix_text_on_bg',
			'selector' => '.landing-block-node-text'
		],
		'img' => [
			'code' => '32.2.img_one_big',
			'selector' => '.landing-block-node-img'
		],
		'table' => [
			'code' => '27.6.one_col_fix_text_with_headings',
			'selector' => '.landing-block-node-text'
		],
		'video' => [
			'code' => '49.1.video_just_video',
			'selector' => '.landing-block-node-embed'
		]
	];

	/**
	 * Returns self::BLOCKS item by code.
	 * @param string $type Content type.
	 * @return array
	 */
	protected static function getBlockMetaByType(string $type): ?array
	{
		return self::BLOCKS[$type] ?? null;
	}

	/**
	 * Returns last created site id.
	 * @return int
	 */
	protected static function getLastCreatedSite(): int
	{
		$res = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'CREATED_BY_ID' => Manager::getUserId()
			],
			'order' => [
				'ID' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			$check = Rights::hasAccessForSite(
				$row['ID'],
				Rights::ACCESS_TYPES['edit']
			);
			if ($check)
			{
				return $row['ID'];
			}
		}

		return 0;
	}

	/**
	 * Creates new knowledge page and returns info about it.
	 * @param int $kbId Knowledge id.
	 * @param string $sourceType Source type.
	 * @param int $sourceId Source id.
	 * @return array|null
	 */
	public static function createFromSource(int $kbId, string $sourceType, int $sourceId): ?array
	{
		if (in_array($sourceType, self::AVAILABLE_SOURCES))
		{
			$class = __NAMESPACE__ . '\\Source\\' . $sourceType;
			$data = $class::getData($sourceId);

			if ($data)
			{
				Landing::setEditMode();
				if (!$kbId)
				{
					$kbId = self::getLastCreatedSite();
				}
				$res = Landing::add([
					'TITLE' => $data['TITLE'],
					'SITE_ID' => $kbId,
					'ACTIVE' => 'N',
					'PUBLIC' => 'N'
				]);
				if ($res->isSuccess())
				{
					Target::rememberLastSite($res->getId());
					$landing = Landing::createInstance($res->getId());
					Site::addLandingToMenu($landing->getSiteId(), [
						'ID' => $landing->getId(),
						'TITLE' => $landing->getTitle()
					]);
					// add block to the landing
					foreach ($data['BLOCKS'] as $blockData)
					{
						$blockMeta = self::getBlockMetaByType($blockData['type']);
						if ($blockMeta)
						{
							$blockId = $landing->addBlock($blockMeta['code'], [
								'PUBLIC' => 'N'
							]);
							if ($blockId)
							{
								$block = $landing->getBlockById($blockId);
								if ($blockData['type'] == 'img')
								{
									// for correct saving we should set file to the block
									File::addToBlock($blockId, $blockData['content']['id']);
								}
								$block->updateNodes([
									$blockMeta['selector'] => [$blockData['content']]
								]);
								$block->save();
							}
						}
					}
					return [
						'ID' => $landing->getId(),
						'TITLE' => $landing->getTitle(),
						'PUBLIC_URL' => $landing->getPublicUrl()
					];
				}
			}
		}

		return null;
	}
}