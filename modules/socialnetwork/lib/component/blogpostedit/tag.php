<?php

namespace Bitrix\Socialnetwork\Component\BlogPostEdit;

use Bitrix\Blog\Item\Post;
use Bitrix\Socialnetwork\Util;

class Tag
{
	public static function getTagsFromPostData(array $params = []): array
	{
		$result = [];

		$blogId = (int)($params['blogId'] ?? 0);
		$inputTags = (string) ($params['tags'] ?? '');

		$inputTags = !empty($_POST['TAGS']) ? $_POST['TAGS'] : $inputTags;

		if ($inputTags)
		{
			$blogCategoryList = [];

			$res = \CBlogCategory::getList([], [ 'BLOG_ID' => $blogId ]);
			while ($blogCategoryFields = $res->fetch())
			{
				$blogCategoryList[mb_strtolower($blogCategoryFields['NAME'])] = (int)$blogCategoryFields['ID'];
			}

			$tags = explode(',', $inputTags);
			foreach ($tags as $tg)
			{
				$tg = trim($tg);
				if (
					$tg !== ''
					&& !in_array($blogCategoryList[mb_strtolower($tg)] ?? null, $result, true)
				)
				{
					$result[] = (int) (
						((int) ($blogCategoryList[mb_strtolower($tg)] ?? null) > 0)
							? $blogCategoryList[mb_strtolower($tg)]
							: \CBlogCategory::add([
								'BLOG_ID' => $blogId,
								'NAME' => $tg,
							])
					);
				}
			}
		}
		elseif (!empty($_POST['CATEGORY_ID']))
		{
			foreach ($_POST['CATEGORY_ID'] as $v)
			{
				$result[] = (int)(
				mb_strpos($v, 'new_') === 0
					? \CBlogCategory::add([
					'BLOG_ID' => $blogId,
					'NAME' => mb_substr($v, 4),
				])
					: $v
				);
			}
		}

		return $result;
	}

	public static function parseTagsFromFields(array $params = []): array
	{
		$result = [];

		$blogCategoryIdList = ($params['blogCategoryIdList'] ?? []);
		$postFields = ($params['postFields'] ?? []);
		$blogId = (int)($params['blogId'] ?? 0);

		$existingTagList = [];

		if (!empty($blogCategoryIdList))
		{
			$res = \CBlogCategory::getList(
				[],
				[
					'@ID' => $blogCategoryIdList,
				],
				false,
				false,
				[ 'NAME' ]
			);
			while ($blogCategoryFields = $res->fetch())
			{
				$existingTagList[] = $blogCategoryFields['NAME'];
			}
		}

		$codeList = [ 'DETAIL_TEXT' ];
		if (
			!isset($postFields['MICRO'])
			|| $postFields['MICRO'] !== 'Y'
		)
		{
			$codeList[] = 'TITLE';
		}

		$inlineTagList = Util::detectTags($postFields, $codeList);

		$tagList = array_merge($existingTagList, $inlineTagList);
		$tagList = array_intersect_key($tagList, array_unique(array_map('ToLower', $tagList)));

		if (count($tagList) > count($existingTagList))
		{
			$lowerExistingTagList = array_unique(array_map('ToLower', $existingTagList));
			$newTagList = [];

			foreach ($inlineTagList as $inlineTag)
			{
				if (!in_array(mb_strtolower($inlineTag), $lowerExistingTagList, true))
				{
					$newTagList[] = $inlineTag;
				}
			}

			if (!empty($newTagList))
			{
				$newTagList = array_unique($newTagList);

				$existingCategoriesList = [];
				$res = \CBlogCategory::getList(
					[],
					[
						'@NAME' => $newTagList,
						'BLOG_ID' => $blogId,
					],
					false,
					false,
					[ 'ID', 'NAME' ]
				);
				while ($blogCategoryFields = $res->fetch())
				{
					$existingCategoriesList[$blogCategoryFields['NAME']] = (int)$blogCategoryFields['ID'];
				}

				foreach ($newTagList as $newTag)
				{
					if (isset($existingCategoriesList[$newTag]))
					{
						$result[] = $existingCategoriesList[$newTag];
					}
					else
					{
						$result[] = (int)\CBlogCategory::add([
							'BLOG_ID' => $blogId,
							'NAME' => $newTag,
						]);
					}
				}
			}
		}

		return $result;
	}
}
