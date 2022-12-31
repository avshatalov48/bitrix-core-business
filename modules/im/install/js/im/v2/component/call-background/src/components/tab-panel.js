import {LimitManager} from '../classes/limit-manager';
import {TabId} from '../const';

import '../css/tab-panel.css';

import type {Tab} from '../types/tab';

// @vue/component
export const TabPanel = {
	props:
	{
		selectedTab: {
			type: String,
			required: true
		}
	},
	emits: ['tabChange'],
	data()
	{
		return {};
	},
	computed:
	{
		tabs(): Tab[]
		{
			const tabs = [];
			if (LimitManager.isMaskFeatureAvailable())
			{
				tabs.push({
					id: TabId.mask,
					loc: 'BX_IM_CALL_BG_TAB_MASK',
					isNew: true
				});
			}

			tabs.push({
				id: TabId.background,
				loc: 'BX_IM_CALL_BG_TAB_BG',
				isNew: false
			});

			return tabs;
		}
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template:
	`
		<div class="bx-im-call-background__tab-panel">
			<div
				v-for="tab in tabs"
				:key="tab.id"
				@click="$emit('tabChange', tab.id)"
				:class="{'--active': selectedTab === tab.id, '--new': tab.isNew}"
				class="bx-im-call-background__tab"
			>
				<div v-if="tab.isNew" class="bx-im-call-background__tab_new">{{ loc('BX_IM_CALL_BG_TAB_NEW') }}</div>
				<div class="bx-im-call-background__tab_text">{{ loc(tab.loc) }}</div>
			</div>
		</div>
	`
};