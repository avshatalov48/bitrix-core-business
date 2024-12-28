<?php

use Bitrix\Socialnetwork\Helper\Feature;
use Bitrix\Socialnetwork\Item\Workgroup;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

$folderWorkgroups = COption::GetOptionString('socialnetwork', 'workgroups_page', false, SITE_ID);
$folderWorkgroups = ($folderWorkgroups ?: SITE_DIR . 'workgroups/');
$folderWorkgroups = (preg_match('#^/\w#', $folderWorkgroups) ? $folderWorkgroups: '/');

$groupId = (int) ($arResult['VARIABLES']['group_id'] ?? null);

$group = Workgroup::getById($groupId);

$isScrumProject = ($group && $group->isScrumProject());
$isProject = ($group && !$group->isScrumProject());
if (
	($isScrumProject && Feature::isFeatureEnabled(Feature::SCRUM_CREATE))
	|| ($isProject && Feature::isFeatureEnabled(Feature::PROJECTS_GROUPS, $groupId))
)
{
	return;
}

$featureId = $isScrumProject ? Feature::SCRUM_CREATE : Feature::PROJECTS_GROUPS;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$isFrame = $request->get('IFRAME') === 'Y';

\Bitrix\Main\Page\Asset::getInstance()->setJsToBody(true);
if ($isFrame)
{
	\Bitrix\Main\Page\Asset::getInstance()->addString("
		<style>
			
		</style>
		<script>
			(function() {
				const slider = (
					top.BX
					&& top.BX.SidePanel
					&& top.BX.SidePanel.Instance.getSliderByWindow(window)
				);
			
				const content = slider.getContainer();
				if (content)
				{
					content.style.padding = '7px';
					content.style.webkitFilter = 'blur(5px)';
					content.style.mozFilter = 'blur(5px)';
					content.style.filter = 'blur(5px)';
					content.style.pointerEvents = 'none';
					content.style.webkitUserSelect = 'none';
					content.style.mozUserSelect = 'none';
					content.style.msUserSelect = 'none';
					content.style.userSelect = 'none';
				}
				
				top.BX.addCustomEvent('SidePanel.Slider:onClose', (event) => {
					if (event.getSlider().getUrl() === 'ui:info_helper')
					{
						if (slider)
						{
							slider.close();
						}
					}
				});
			})();
		</script>
	", false, \Bitrix\Main\Page\AssetLocation::AFTER_CSS);
}
else
{
	\Bitrix\Main\Page\Asset::getInstance()->addString("
		<script>
			(function() {
				const contentContainer = document.getElementById('content-table');
				if (contentContainer)
				{
					contentContainer.classList.add('socialnetwork-content-locked');
				}
				
				top.BX.addCustomEvent('SidePanel.Slider:onClose', (event) => {
					if (event.getSlider().getUrl() === 'ui:info_helper')
					{
						top.window.location.href = '".\CUtil::JSEscape($folderWorkgroups)."';
					}
				});
			})();
		</script>
	", false, \Bitrix\Main\Page\AssetLocation::AFTER_CSS);
}

\Bitrix\Main\Page\Asset::getInstance()->addString("
	<script>
		(function() {
			top.BX.Runtime.loadExtension('socialnetwork.limit').then((exports) => {
				const { Limit } = exports;
				Limit.showInstance({
					featureId: '".$featureId."',
					limitAnalyticsLabels: {
						module: 'socialnetwork',
						source: 'group_page',
					}
				});
			});
		})();
	</script>
", false, \Bitrix\Main\Page\AssetLocation::AFTER_CSS);
