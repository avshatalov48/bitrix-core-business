<?php
namespace Bitrix\Landing\Transfer\Export;

use \Bitrix\Landing\Landing as LandingCore;
use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Repo;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\TemplateRef;

class Landing
{
	/**
	 * Exports landing.
	 * @param int $landingId Landing id.
	 * @param string $fileName File name.
	 * @return array|null
	 */
	public static function exportLanding(int $landingId, string $fileName): ?array
	{
		// base fields
		$landing = LandingCore::getList([
			'filter' => [
				'ID' => $landingId
			]
		])->fetch();
		if (!$landing)
		{
			return null;
		}
		$landing['DATE_CREATE'] = (string)$landing['DATE_CREATE'];
		$landing['DATE_MODIFY'] = (string)$landing['DATE_MODIFY'];
		$landing['DATE_PUBLIC'] = (string)$landing['DATE_PUBLIC'];
		$files = [];

		// additional fields
		$hookFields = [];
		foreach (Hook::getForLanding($landingId) as $hookCode => $hook)
		{
			if ($hookCode == 'SETTINGS')
			{
				continue;
			}
			foreach ($hook->getFields() as $fCode => $field)
			{
				$hookCodeFull = $hookCode . '_' . $fCode;
				$hookFields[$hookCodeFull] = $field->getValue();
				if (!$hookFields[$hookCodeFull])
				{
					unset($hookFields[$hookCodeFull]);
				}
				else if (in_array($hookCodeFull, Hook::HOOKS_CODES_FILES))
				{
					if ($hookFields[$hookCodeFull] > 0)
					{
						$files[] = ['ID' => $hookFields[$hookCodeFull]];
					}
				}
			}
		}
		$landing['ADDITIONAL_FIELDS'] = $hookFields;

		// site layout template
		$landing['TEMPLATE_REF'] = [];
		if ($landing['TPL_ID'])
		{
			$landing['TEMPLATE_REF'] = TemplateRef::getForLanding($landingId);
		}

		$landingInstance = LandingCore::createInstance($landingId);

		// saved block (favorites)
		if ($landing['TPL_CODE'])
		{
			$tplCode = $landing['TPL_CODE'];
			if (strpos($tplCode, '@'))
			{
				[, $tplCode] = explode('@', $tplCode);
			}
			foreach (Block::getFavorites($tplCode) as $row)
			{
				$landingInstance->addBlockToCollection(new Block($row['ID']));
			}
		}

		// blocks
		$landing['BLOCKS'] = [];
		foreach ($landingInstance->getBlocks() as $block)
		{
			if (!$block->isActive())
			{
				continue;
			}

			// repo blocks
			$repoBlock = [];
			if ($block->getRepoId())
			{
				$repoBlock = Repo::getBlock(
					$block->getRepoId()
				);
				if ($repoBlock)
				{
					$repoBlock = [
						'app_code' => $repoBlock['block']['app_code'],
						'xml_id' => $repoBlock['block']['xml_id']
					];
				}
			}

			$repoInfo = [];
			if ($block->getRepoId())
			{
				$repoInfo = Repo::getById($block->getRepoId())->fetch();
				if ($repoInfo)
				{
					$repoInfo = [
						'NAME' => $repoInfo['NAME'],
						'DESCRIPTION' => $repoInfo['DESCRIPTION'],
						'SECTIONS' => $repoInfo['SECTIONS'],
						'PREVIEW' => $repoInfo['PREVIEW'],
						'MANIFEST' => $repoInfo['MANIFEST'],
						'CONTENT' => $repoInfo['CONTENT']
					];
				}
				else
				{
					$repoInfo = [];
				}
			}

			$metaBlock = $block->getMeta();
			$exportBlock = $block->export();
			$exportItem = array(
				'code' => $block->getCode(),
				'old_id' => $block->getId(),
				'access' => $block->getAccess(),
				'anchor' => $block->getLocalAnchor(),
				'designed' => $block->isDesigned(),
				'meta' => $metaBlock,
				'full_content' => $block->isDesigned() ? $block->getContent() : null,
				'repo_block' => $repoBlock,
				'repo_info' => $repoInfo,
				'cards' => $exportBlock['cards'],
				'nodes' => $exportBlock['nodes'],
				'menu' => $exportBlock['menu'],
				'style' => $exportBlock['style'],
				'attrs' => $exportBlock['attrs'],
				'dynamic' => $exportBlock['dynamic']
			);

			foreach ($exportItem as $key => $item)
			{
				if (!$item)
				{
					unset($exportItem[$key]);
				}
			}
			$landing['BLOCKS'][$block->getId()] = $exportItem;

			$blockFiles = File::getFilesFromBlockContent(
				$block->getId(),
				$block->getContent()
			);
			foreach ($blockFiles as $fileId)
			{
				$files[] = ['ID' => $fileId];
			}
			// favorite cover
			if (intval($metaBlock['FAVORITE_META']['preview'] ?? 0) > 0)
			{
				$files[] = ['ID' => intval($metaBlock['FAVORITE_META']['preview'])];
			}
		}

		return [
			'FILE_NAME' => str_replace(
				['#site_id#', '#landing_id#'],
				[$landing['SITE_ID'], $landing['ID']],
				$fileName
			),
			'CONTENT' => $landing,
			'FILES' => $files
		];
	}
}