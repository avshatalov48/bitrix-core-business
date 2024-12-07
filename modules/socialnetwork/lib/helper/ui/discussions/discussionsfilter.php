<?php

namespace Bitrix\Socialnetwork\Helper\UI\Discussions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\DateType;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Livefeed\RenderParts\User;
use CExtranet;
use CLists;
use CModule;
use CSocNetLogComponent;

abstract class DiscussionsFilter
{
	private int $groupId;
	private const LIVEFEED_CODE = 'LIVEFEED';
	public const POPUP_OFFSET_LEFT = 0;

	public function __construct(?int $groupId)
	{
		$this->groupId = $groupId ?? 0;
	}

	public function getContainerId(): string
	{
		return $this->groupId === 0 ? self::LIVEFEED_CODE : self::LIVEFEED_CODE . '_SG' . $this->groupId;
	}

	public function getFilter(): array
	{
		$filter = [
			[
				'id' => 'DATE_CREATE',
				'name' => Loc::getMessage('SONET_FILTER_DATE_CREATE'),
				'type' => 'date',
				'default' => true,
				'exclude' => [
					DateType::TOMORROW,
					DateType::NEXT_DAYS,
					DateType::NEXT_WEEK,
					DateType::NEXT_MONTH
				]
			],
			[
				'id' => 'EVENT_ID',
				'name' => Loc::getMessage('SONET_FILTER_EVENT_ID'),
				'type' => 'list',
				'params' => [
					'multiple' => 'Y',
				],
				'items' => $this->getEventIdList(),
				'default' => true
			],
			[
				'id' => 'CREATED_BY_ID',
				'name' => Loc::getMessage('SONET_FILTER_CREATED_BY'),
				'default' => true,
				'type' => 'dest_selector',
				'params' => [
					'apiVersion' => '3',
					'context' => 'FEED_FILTER_CREATED_BY',
					'multiple' => 'N',
					'contextCode' => 'U',
					'enableAll' => 'N',
					'enableSonetgroups' => 'N',
					'allowEmailInvitation' => 'N',
					'allowSearchEmailUsers' => 'N',
					'departmentSelectDisable' => 'Y',
				],
			],
		];

		if ($this->groupId === 0)
		{
			$filter[] = [
				'id' => 'TO',
				'name' => Loc::getMessage('SONET_FILTER_TO'),
				'default' => true,
				'type' => 'dest_selector',
				'params' => [
					'apiVersion' => '3',
					'context' => 'FEED_FILTER_TO',
					'multiple' => 'N',
					'enableAll' => 'Y',
					'enableSonetgroups' => 'Y',
					'departmentSelectDisable' => 'N',
					'allowEmailInvitation' =>
						(
						ModuleManager::isModuleInstalled('mail')
						&& ModuleManager::isModuleInstalled('intranet') ? 'Y' : 'N'
						),
					'allowSearchEmailUsers' => ($this->isExtranetUser() ? 'N' : 'Y')
				]
			];
		}

		$filter[] = [
			'id' => 'FAVORITES_USER_ID',
			'name' => Loc::getMessage('SONET_FILTER_FAVORITES'),
			'type' => 'list',
			'items' => [
				'Y' => Loc::getMessage('SONET_FILTER_LIST_YES')
			]
		];

		$filter[] = [
			'id' => 'TAG',
			'name' => Loc::getMessage('SONET_FILTER_TAG'),
			'type' => 'string'
		];

		if (ModuleManager::isModuleInstalled('extranet'))
		{
			$filter[] = [
				'id' => 'EXTRANET',
				'name' => Loc::getMessage('SONET_FILTER_EXTRANET'),
				'type' => 'checkbox'
			];
		}

		return $filter;
	}

	public function getPresets(?array $paramsForPresets): array
	{
		if (empty($paramsForPresets))
		{
			return [];
		}

		return $this->convertParamsToPresetFilters($paramsForPresets);
	}

	protected function bizprocIsAvailable(): bool
	{
		$bizprocIsAvailable = (
			CModule::IncludeModule("lists")
			&& CLists::isFeatureEnabled()
			&& ModuleManager::isModuleInstalled('intranet')
			&& (
				!Loader::includeModule('extranet')
				|| !CExtranet::isExtranetSite()
			)
		);

		return $bizprocIsAvailable;
	}

	protected function isExtranetUser(): bool
	{
		return CModule::IncludeModule("extranet") && !CExtranet::IsIntranetUser();
	}

	protected function getEventIdList(): array
	{
		$eventIdList = [];
		if (ModuleManager::isModuleInstalled('blog'))
		{
			$eventIdList['blog_post'] = Loc::getMessage('SONET_FILTER_EVENT_ID_BLOG_POST');
			$eventIdList['blog_post_important'] = Loc::getMessage('SONET_FILTER_EVENT_ID_BLOG_POST_IMPORTANT');
			if (ModuleManager::isModuleInstalled('intranet'))
			{
				$eventIdList['blog_post_grat'] = Loc::getMessage('SONET_FILTER_EVENT_ID_BLOG_POST_GRAT');
			}
			if (ModuleManager::isModuleInstalled('vote'))
			{
				$eventIdList['blog_post_vote'] = Loc::getMessage('SONET_FILTER_EVENT_ID_BLOG_POST_VOTE');
			}
		}

		if (ModuleManager::isModuleInstalled('forum'))
		{
			$eventIdList['forum'] = Loc::getMessage('SONET_FILTER_EVENT_ID_FORUM');
		}

		if (
			ComponentHelper::checkLivefeedTasksAllowed()
			&& ModuleManager::isModuleInstalled('tasks')
		)
		{
			$eventIdList['tasks'] = Loc::getMessage('SONET_FILTER_EVENT_ID_TASK');
		}

		if (ModuleManager::isModuleInstalled('timeman'))
		{
			$eventIdList['timeman_entry'] = Loc::getMessage('SONET_FILTER_EVENT_ID_TIMEMAN_ENTRY');
			$eventIdList['report'] = Loc::getMessage('SONET_FILTER_EVENT_ID_REPORT');
		}

		if (ModuleManager::isModuleInstalled('calendar'))
		{
			$eventIdList['calendar'] = Loc::getMessage('SONET_FILTER_EVENT_ID_CALENDAR');
		}

		if (ModuleManager::isModuleInstalled('xdimport'))
		{
			$eventIdList['data'] = Loc::getMessage('SONET_FILTER_EVENT_ID_DATA');
		}

		if (ModuleManager::isModuleInstalled('photogallery'))
		{
			$eventIdList['photo'] = Loc::getMessage('SONET_FILTER_EVENT_ID_PHOTO');
		}

		if (ModuleManager::isModuleInstalled('wiki'))
		{
			$eventIdList['wiki'] = Loc::getMessage('SONET_FILTER_EVENT_ID_WIKI');
		}

		if ($this->bizprocIsAvailable())
		{
			$eventIdList['lists_new_element'] = Loc::getMessage('SONET_FILTER_EVENT_ID_BP');
		}

		return $eventIdList;
	}

	abstract public function getParamsForPresets(): array;

	protected function convertParamsToPresetFilters(array $paramsFilters): array
	{
		if (empty($paramsFilters))
		{
			return [];
		}
		
		usort($paramsFilters, static fn($a, $b) => ($a["SORT"] - $b["SORT"]));

		$paramsFilters = CSocNetLogComponent::ConvertPresetToFilters(
			$paramsFilters,
			$this->groupId
		);

		$presetFilters = [];
		foreach($paramsFilters as $filter)
		{
			$skipPreset = false;
			$newFilter = $filter["FILTER"];
			if (!empty($newFilter['EXACT_EVENT_ID']))
			{
				$newFilter['EVENT_ID'] = [$newFilter['EXACT_EVENT_ID']];
				unset($newFilter['EXACT_EVENT_ID']);
			}
			if (!empty($newFilter['CREATED_BY_ID']))
			{
				$renderPartsUser = new User(['skipLink' => true]);
				if ($renderData = $renderPartsUser->getData($newFilter['CREATED_BY_ID']))
				{
					$newFilter['CREATED_BY_ID_label'] = $renderData['name'];
				}
				$newFilter['CREATED_BY_ID'] = 'U'.$newFilter['CREATED_BY_ID'];
			}
			if (!empty($filter['ID']))
			{
				if ($filter['ID'] === 'extranet')
				{
					$newFilter = ['EXTRANET' => 'Y'];
				}
				elseif (
					$filter['ID'] === 'bizproc'
					&& !$this->bizprocIsAvailable()
				)
				{
					$skipPreset = true;
				}
			}

			if (!$skipPreset)
			{
				$presetFilters[$filter["ID"]] = [
					"name" => $filter["NAME"],
					"fields" => $newFilter,
					"disallow_for_all" => ($filter["ID"] === "my")
				];
			}
		}

		return $presetFilters;
	}
}