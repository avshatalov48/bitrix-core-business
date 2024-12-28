<?php

use Bitrix\Disk\Driver;
use Bitrix\Disk\UrlManager;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Mainpage;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\WorkgroupTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

CBitrixComponent::includeComponentClass('bitrix:landing.blocks.mp_widget.base');

class LandingBlocksMainpageWidgetLivefeed extends LandingBlocksMainpageWidgetBase
{
	private const POST_COUNT_DEFAULT = 15;

	private const PATH_TO_POST_DEFAULT = '/company/personal/user/#user_id#/blog/#post_id#/';


	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$this->checkParam('TITLE', Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_TITLE'));
		$this->checkParam('COLOR_BUTTON', '#bdc1c6');
		$this->checkParam('COLOR_HEADERS_V2', '#ffffff');

		$this->checkParam('USER_ID', Manager::getUserId());
		$this->checkParam('POST_COUNT', self::POST_COUNT_DEFAULT);
		$this->checkParam('GROUP_ID', null);

		$this->getData();

		parent::executeComponent();
	}

	protected function getData(): void
	{
		$useDemoData = false;
		if (Mainpage\Manager::isUseDemoData())
		{
			$data = $this->getDemoData();
		}
		else
		{
			$data = $this->getRealData();
			if (!isset($data['POSTS']))
			{
				$data = $this->getDemoData();
				$useDemoData = true;
			}
		}

		$this->arResult['TITLE'] = $this->arParams['TITLE'];
		$this->arResult['POSTS'] = $data['POSTS'];
		$this->arResult['USERS'] = $data['USERS'];
		$this->arResult['USERS_ID'] = $data['USERS_ID'];
		$this->arResult['USE_DEMO_DATA'] = $useDemoData;
		$this->arResult['PHRASES'] = [
			'POST_IMPORTANT' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_PHRASES_POST_IMPORTANT'),
			'NAVIGATOR_BUTTON' => $this->getNavigatorButtonPhrases(),
		];

		if (count($data['POSTS']) > 5)
		{
			$this->arResult['IS_SHOW_EXTEND_BUTTON'] = true;
		}
		else
		{
			$this->arResult['IS_SHOW_EXTEND_BUTTON'] = false;
		}
	}

	protected function getDemoData(): array
	{
		return [
			'TITLE' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_TITLE'),
			'POSTS' => [
				[
					'AUTHOR_ID' => '1',
					'TITLE' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_POST_TITLE_1'),
					'DATE_PUBLISH' => $this->convertDateFormat('12.01.2024 14:10:00'),
					'DATE_PUBLISH_SHORT' => $this->convertDateFormat(
						'12.01.2024 14:10:00',
						'H:i d.m.Y',
					),
					'RATING_TOTAL_VOTES' => '23',
					'NUM_COMMENTS' => '2',
					'IMG_SRC' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/1.jpg',
					'IMPORTANT' => true,
				],
				[
					'AUTHOR_ID' => '2',
					'TITLE' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_POST_TITLE_2'),
					'DATE_PUBLISH' => $this->convertDateFormat('22.01.2024 13:10:00'),
					'DATE_PUBLISH_SHORT' => $this->convertDateFormat(
						'22.01.2024 13:10:00',
						'H:i d.m.Y',
					),
					'RATING_TOTAL_VOTES' => '23',
					'NUM_COMMENTS' => '3',
					'IMG_SRC' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/2.jpg',
					'IMPORTANT' => true,
				],
				[
					'AUTHOR_ID' => '3',
					'TITLE' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_POST_TITLE_3'),
					'DATE_PUBLISH' => $this->convertDateFormat('13.02.2024 10:20:00'),
					'DATE_PUBLISH_SHORT' => $this->convertDateFormat(
						'13.02.2024 10:20:00',
						'H:i d.m.Y',
					),
					'RATING_TOTAL_VOTES' => '7',
					'NUM_COMMENTS' => '7',
					'IMG_SRC' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/3.jpg',
					'IMPORTANT' => true,
				],
				[
					'AUTHOR_ID' => '4',
					'TITLE' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_POST_TITLE_4'),
					'DATE_PUBLISH' => $this->convertDateFormat('04.01.2024 15:35:00'),
					'DATE_PUBLISH_SHORT' => $this->convertDateFormat(
						'04.01.2024 15:35:00',
						'H:i d.m.Y',
					),
					'RATING_TOTAL_VOTES' => '12',
					'NUM_COMMENTS' => '45',
					'IMG_SRC' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/4.jpg',
					'IMPORTANT' => true,
				],
				[
					'AUTHOR_ID' => '1',
					'TITLE' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_POST_TITLE_5'),
					'DATE_PUBLISH' => $this->convertDateFormat('15.03.2024 16:20:00'),
					'DATE_PUBLISH_SHORT' => $this->convertDateFormat(
						'15.03.2024 16:20:00',
						'H:i d.m.Y',
					),
					'RATING_TOTAL_VOTES' => '23',
					'NUM_COMMENTS' => '3',
					'IMG_SRC' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/5.jpg',
					'IMPORTANT' => false,
				],
				[
					'AUTHOR_ID' => '3',
					'TITLE' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_POST_TITLE_6'),
					'DATE_PUBLISH' => $this->convertDateFormat('15.03.2024 16:20:00'),
					'DATE_PUBLISH_SHORT' => $this->convertDateFormat(
						'15.03.2024 16:20:00',
						'H:i d.m.Y',
					),
					'RATING_TOTAL_VOTES' => '11',
					'NUM_COMMENTS' => '4',
					'IMG_SRC' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/6.jpg',
					'IMPORTANT' => false,
				],
			],
			'USERS' => [
				1 => [
					'ID' => '1',
					'NAME' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_USER_NAME_1'),
					'PERSONAL_PHOTO' => [
						'IMG' => [
							'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/7.png',
						],
					],
				],
				2 => [
					'ID' => '2',
					'NAME' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_USER_NAME_2'),
					'PERSONAL_PHOTO' => [
						'IMG' => [
							'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/8.png',
						],
					],
				],
				3 => [
					'ID' => '3',
					'NAME' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_USER_NAME_3'),
					'PERSONAL_PHOTO' => [
						'IMG' => [
							'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/9.png',
						],
					],
				],
				4 => [
					'ID' => '4',
					'NAME' => Loc::getMessage('LANDING_WIDGET_CLASS_LIVEFEED_DEMO_DATA_USER_NAME_4'),
					'PERSONAL_PHOTO' => [
						'IMG' => [
							'src' => 'https://cdn.bitrix24.site/bitrix/images/landing/widget/livefeed/10.png',
						],
					],
				],
			],
			'USERS_ID' => [],
		];
	}

	protected function getRealData(): array
	{
		$data = [];

		if (
			!Loader::includeModule('blog')
			|| !Loader::includeModule('disk')
		)
		{
			return $data;
		}

		$res = $this->getPostsQuery();
		while ($post = $res->Fetch())
		{
			$userFieldManager = Driver::getInstance()->getUserFieldManager();
			$attachedObjectsByEntity = $userFieldManager->getAttachedObjectByEntity(
				'BLOG_POST',
				$post['ID'],
				'UF_BLOG_POST_FILE'
			);
			$firstAttachedObject = reset($attachedObjectsByEntity);
			if ($firstAttachedObject)
			{
				$fileArray = \CFile::GetFileArray($firstAttachedObject->getFileId());
				$contentType = $fileArray['CONTENT_TYPE'] ?? null;
				if ($contentType)
				{
					if (str_starts_with($contentType, 'image/'))
					{
						$post['IMG_SRC'] = UrlManager::getUrlUfController(
							'show',
							[
								'attachedId' => $firstAttachedObject->getId()
							]
						);
					}
					//todo: add preview for video file
					// if (str_starts_with($contentType, 'video/'))
				}
			}
			$post['PATH'] = CComponentEngine::MakePathFromTemplate(
				self::PATH_TO_POST_DEFAULT,
				[
					'post_id' => CBlogPost::GetPostID(
						$post['ID'],
						$post['CODE'],
						true
					),
					'user_id' => $post['AUTHOR_ID'],
				]);
			$post['DATE_PUBLISH_SHORT'] = $post['DATE_PUBLISH'];

			$data['POSTS'][] = $post;
			$data['USERS_ID'][$post['AUTHOR_ID']] = $post['AUTHOR_ID'];
		}

		if (isset($data['POSTS']) && count($data['POSTS']) !== 0)
		{
			$data['USERS'] = self::getUserData($data['USERS_ID'], [24, 24]);
		}

		return $data;
	}

	protected function getPostsQuery(): CDBResult
	{
		$order = ['DATE_PUBLISH' => 'DESC'];
		$this->arParams['SOCNET_GROUP_ID'] = 0;
		if (
			isset($this->arParams['GROUP_ID']['filter'][0]['value']) &&
			str_starts_with($this->arParams['GROUP_ID']['filter'][0]['value'], 'SG')
		)
		{
			$this->arParams['SOCNET_GROUP_ID'] = substr($this->arParams['GROUP_ID']['filter'][0]['value'], 2);
		}
		else if (Loader::includeModule('socialnetwork'))
		{
			$publicationGroupId = Option::get('landing', 'mainpage_id_publication_group');
			if ($publicationGroupId > 0)
			{
				$res = WorkgroupTable::getList([
					'filter' => [
						'@ID' => $publicationGroupId
					],
					'select' => [ 'ID', 'NAME' ],
					'limit' => 1,
				]);
				$groupRow = $res->fetch();
				$this->arParams['SOCNET_GROUP_ID'] = $groupRow['ID'];
			}
		}

		$filter = [
			'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
			'BLOG_USE_SOCNET' => 'Y',
			'GROUP_ID' => $this->arParams['GROUP_ID'],
			'GROUP_SITE_ID' => SITE_ID,
			'SOCNET_SITE_ID' => [
				SITE_ID, false,
			],
			'SOCNET_GROUP_ID' => $this->arParams['SOCNET_GROUP_ID'],
			'<=DATE_PUBLISH' => ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL"),
		];

		$nav = [
			'bDescPageNumbering' => false,
			'nPageSize' => $this->arParams['POST_COUNT'],
			'bShowAll' => false,
		];

		$select = [
			"ID",
			"TITLE",
			"AUTHOR_ID",
			"DETAIL_TEXT",
			"DETAIL_TEXT_TYPE",
			"DATE_PUBLISH",
			"NUM_COMMENTS",
			'RATING_TOTAL_VOTES',
			'VIEWS',
			'UF_BLOG_POST_IMPRTNT',
			'UF_IMPRTANT_DATE_END',
			'POST_PARAM_BLOG_POST_IMPRTNT',
		];

		return CBlogPost::GetList(
			$order,
			$filter,
			false,
			$nav,
			$select
		);
	}
}