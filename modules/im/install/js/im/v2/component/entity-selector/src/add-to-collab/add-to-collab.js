import { Loc } from 'main.core';
import { PopupOptions } from 'main.popup';

import { UserType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { MessengerPopup, SegmentButton } from 'im.v2.component.elements';

import { AddGuestsTab } from './components/add-guests-tab';
import { AddEmployeesTab } from './components/add-employees-tab';

import './css/add-to-collab.css';

import type { JsonObject } from 'main.core';
import type { ImModelUser } from 'im.v2.model';
import type { BitrixVueComponentProps } from 'ui.vue3';

const TabId = Object.freeze({
	guests: 'guests',
	employees: 'employees',
});

const Tabs: Tab[] = [
	{
		id: TabId.guests,
		title: Loc.getMessage('IM_ENTITY_SELECTOR_GUESTS_TAB'),
	},
	{
		id: TabId.employees,
		title: Loc.getMessage('IM_ENTITY_SELECTOR_EMPLOYEES_TAB'),
	},
];

const POPUP_ID = 'im-add-to-collab-popup';
const TAB_CONTENT_HEIGHT = 498;

// @vue/component
export const AddToCollab = {
	name: 'AddToCollab',
	components: { MessengerPopup, SegmentButton, AddGuestsTab, AddEmployeesTab },
	props:
	{
		bindElement: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		popupConfig: {
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {
			activeTabId: TabId.guests,
		};
	},
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		Tabs: () => Tabs,
		config(): PopupOptions
		{
			return {
				titleBar: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_COLLAB_TITLE'),
				closeIcon: true,
				bindElement: this.bindElement,
				offsetTop: this.popupConfig.offsetTop,
				offsetLeft: this.popupConfig.offsetLeft,
				padding: 0,
				contentPadding: 0,
				contentBackground: '#fff',
				className: 'bx-im-add-to-collab__scope',
			};
		},
		tabComponent(): BitrixVueComponentProps
		{
			return this.activeTabId === TabId.guests ? AddGuestsTab : AddEmployeesTab;
		},
		isCollaber(): boolean
		{
			const currentUser: ImModelUser = this.$store.getters['users/get'](Core.getUserId());

			return currentUser.type === UserType.collaber;
		},
		isInviteLinkAvailable(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.inviteByLinkAvailable);
		},
		finalHeight(): number
		{
			const inviteLinkBlockHeight = 58 + 12;
			const tabsBlockHeight = 38;

			let finalHeight = TAB_CONTENT_HEIGHT;
			if (this.isCollaber)
			{
				finalHeight -= tabsBlockHeight;
			}

			if (!this.isInviteLinkAvailable)
			{
				finalHeight -= inviteLinkBlockHeight;
			}

			return finalHeight;
		},
	},
	methods:
	{
		onTabSwitch(tabId: string)
		{
			this.activeTabId = tabId;
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
			v-slot="{ enableAutoHide, disableAutoHide }"
			@close="$emit('close')"
		>
			<div class="bx-im-add-to-collab__tabs">
				<SegmentButton :tabs="Tabs" @segmentSelected="onTabSwitch" />
			</div>
			<KeepAlive>
				<component
					:is="tabComponent"
					:dialogId="dialogId" 
					:height="finalHeight"
					@close="$emit('close')"
					@openHelpdeskSlider="disableAutoHide"
					@closeHelpdeskSlider="enableAutoHide"
				/>
			</KeepAlive>
		</MessengerPopup>
	`,
};
