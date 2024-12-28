import { EventType } from 'im.v2.const';
import { EventEmitter } from 'main.core.events';
import { hint } from 'ui.vue3.directives.hint';

import { Button as ChatButton, ButtonSize, ButtonIcon } from 'im.v2.component.elements';
import { ForwardPopup } from 'im.v2.component.entity-selector';

import type { CustomColorScheme } from 'im.v2.component.elements';

import '../css/bulk-actions-panel.css';

const BUTTON_COLOR_DELETE = '#f4433e';
const BUTTON_COLOR_FORWARD = '#ffffff';
const BUTTON_BACKGROUND_COLOR_FORWARD = '#00ace3';
const BUTTON_BACKGROUND_COLOR_FORWARD_HOVER = '#3eddff';

// @vue/component
export const BulkActionsPanel = {
	name: 'BulkActionsPanel',
	components: { ChatButton, ForwardPopup },
	directives: {
		hint,
	},
	data(): Object
	{
		return {
			isShowForwardPopup: false,
			messagesIds: [],
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonIcon: () => ButtonIcon,
		deleteButtonColorScheme(): CustomColorScheme
		{
			return {
				backgroundColor: 'transparent',
				borderColor: 'transparent',
				iconColor: BUTTON_COLOR_DELETE,
				textColor: BUTTON_COLOR_DELETE,
				hoverColor: 'transparent',
			};
		},
		forwardButtonColorScheme(): CustomColorScheme
		{
			return {
				backgroundColor: BUTTON_BACKGROUND_COLOR_FORWARD,
				borderColor: 'transparent',
				iconColor: BUTTON_COLOR_FORWARD,
				textColor: BUTTON_COLOR_FORWARD,
				hoverColor: BUTTON_BACKGROUND_COLOR_FORWARD_HOVER,
			};
		},
		selectedMessages(): Set<number>
		{
			return this.$store.getters['messages/select/getCollection'];
		},
		selectedMessagesSize(): number
		{
			return this.selectedMessages.size;
		},
		formattedMessagesCounter(): string
		{
			if (!this.selectedMessagesSize)
			{
				return '';
			}

			return `(${this.selectedMessagesSize})`;
		},
		messageCounterText(): string
		{
			if (!this.selectedMessagesSize)
			{
				return this.loc('IM_CONTENT_BULK_ACTIONS_SELECT_MESSAGES');
			}

			return this.loc('IM_CONTENT_BULK_ACTIONS_COUNT_MESSAGES');
		},
		tooltipSettings(): { text: string, popupOptions: Object<string, any> }
		{
			return {
				text: this.loc('IM_CONTENT_BULK_ACTIONS_PANEL_DELETE_COMING_SOON'),
				popupOptions: {
					angle: true,
					targetContainer: document.body,
					offsetTop: -13,
					offsetLeft: 65,
					bindOptions: {
						position: 'top',
					},
				},
			};
		},
	},
	methods:
	{
		onShowForwardPopup()
		{
			this.messagesIds = [...this.selectedMessages];
			this.isShowForwardPopup = true;
		},
		onCloseForwardPopup()
		{
			this.messagesIds = [];
			this.isShowForwardPopup = false;
		},
		closeBulkActionsMode()
		{
			EventEmitter.emit(EventType.dialog.closeBulkActionsMode);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-bulk-actions-panel">
			<div class="bx-im-content-bulk-actions-panel__container">
				<div class="bx-im-content-bulk-actions-panel__left-section">
					<div @click="closeBulkActionsMode" class="bx-im-content-bulk-actions-panel__cancel"></div>
					<div class="bx-im-content-bulk-actions-panel__counter-container">
						<span class="bx-im-content-bulk-actions-panel__counter-name">{{ messageCounterText }}</span>
						<span class="bx-im-content-bulk-actions-panel__counter-number">{{ formattedMessagesCounter }}</span>
					</div>
				</div>
				<div class="bx-im-content-bulk-actions-panel__right-section">
					<ChatButton
						v-hint="tooltipSettings"
						:size="ButtonSize.L"
						:icon="ButtonIcon.Delete"
						:customColorScheme="deleteButtonColorScheme"
						:isDisabled="true"
						:isRounded="true"
						:isUppercase="false"
						:text="loc('IM_CONTENT_BULK_ACTIONS_PANEL_DELETE')"
					/>
					<ChatButton
						:size="ButtonSize.L"
						:icon="ButtonIcon.Forward"
						:customColorScheme="forwardButtonColorScheme"
						:isRounded="true"
						:isUppercase="false"
						:isDisabled="!selectedMessagesSize"
						:text="loc('IM_CONTENT_BULK_ACTIONS_PANEL_FORWARD')"
						@click="onShowForwardPopup"
					/>
				</div>
				<ForwardPopup
					v-if="isShowForwardPopup"
					:messagesIds="messagesIds"
					@close="onCloseForwardPopup"
				/>
			</div>
		</div>
	`,
};
