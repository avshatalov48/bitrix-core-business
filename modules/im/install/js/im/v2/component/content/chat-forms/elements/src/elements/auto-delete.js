import { AutoDeleteDelay } from 'im.v2.const';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Toggle, ToggleSize } from 'im.v2.component.elements.toggle';
import { ChatHint } from 'im.v2.component.elements.hint';
import { AutoDeleteDropdown, AutoDeletePopup } from 'im.v2.component.elements.auto-delete';

import './css/auto-delete.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const AutoDelete = {
	name: 'AutoDelete',
	components: { Toggle, ChatHint, AutoDeleteDropdown, AutoDeletePopup },
	props: {
		initialDelay: {
			type: Number,
			default: 0,
		},
	},
	emits: ['delayChange'],
	data(): JsonObject
	{
		return {
			delay: this.initialDelay,
			showPopup: false,
		};
	},
	computed:
	{
		ToggleSize: () => ToggleSize,
		hintText(): string
		{
			return this.loc('IM_CREATE_CHAT_SETTINGS_SECTION_MESSAGES_AUTO_DELETE_HINT');
		},
		isEnabled(): boolean
		{
			return this.delay !== AutoDeleteDelay.Off;
		},
		isFeatureEnabled(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.messagesAutoDeleteEnabled);
		},
	},
	watch:
	{
		delay(newValue: number)
		{
			this.$emit('delayChange', newValue);
		},
	},
	methods:
	{
		onChangeAutoDeleteState()
		{
			if (!this.isFeatureEnabled)
			{
				FeatureManager.messagesAutoDelete.openFeatureSlider();

				return;
			}

			if (this.isEnabled)
			{
				this.delay = AutoDeleteDelay.Off;
			}
			else
			{
				this.showPopup = true;
			}
		},
		onDelayChange(delay: number)
		{
			this.delay = delay;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-chat-forms-auto-delete__container">
			<Toggle
				:size="ToggleSize.M"
				:isEnabled="isEnabled"
				@click="onChangeAutoDeleteState"
				class="bx-im-chat-forms-auto-delete__toggle"
			/>
			<div class="bx-im-chat-forms-auto-delete__label">
				<div class="bx-im-chat-forms-auto-delete__title">
					{{ loc('IM_CREATE_CHAT_SETTINGS_SECTION_MESSAGES_AUTO_DELETE_TITLE') }}
					<ChatHint :text="hintText" class="bx-im-chat-forms-auto-delete__hint" />
				</div>
				<AutoDeleteDropdown 
					:currentDelay="delay" 
					@delayChange="onDelayChange" 
				/>
			</div>
			<AutoDeletePopup
				v-if="showPopup"
				:autoDeleteDelay="delay"
				@close="showPopup = false"
				@autoDeleteDelayChange="onDelayChange"
			/>
		</div>
	`,
};
