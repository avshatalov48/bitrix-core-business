import { KeyboardButtonType, KeyboardButtonContext } from 'im.v2.const';

import { KeyboardButton } from './components/keyboard-button';
import { KeyboardSeparator } from './components/keyboard-separator';

import { ActionManager } from './classes/action-manager';
import { BotService } from './classes/bot-service';
import './keyboard.css';

import type { JsonObject } from 'main.core';
import type { KeyboardButtonConfig } from 'im.v2.const';
import type { ActionEvent, CustomCommandEvent } from './types/events';

export const Keyboard = {
	props:
	{
		buttons: {
			type: Array,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		messageId: {
			type: [Number, String],
			required: true,
		},
	},
	components: { KeyboardButton, KeyboardSeparator },
	data(): JsonObject
	{
		return {
			keyboardBlocked: false,
		};
	},
	emits: ['click'],
	watch:
	{
		buttons()
		{
			this.keyboardBlocked = false;
		},
	},
	computed:
	{
		ButtonType: () => KeyboardButtonType,
		preparedButtons(): KeyboardButtonConfig[]
		{
			return this.buttons.filter((button: KeyboardButtonConfig) => {
				return button.context !== KeyboardButtonContext.mobile;
			});
		},
	},
	methods:
	{
		onButtonActionClick(event: ActionEvent)
		{
			this.getActionManager().handleAction(event);
		},
		onButtonCustomCommandClick(event: CustomCommandEvent)
		{
			this.getBotService().sendCommand(event);
		},
		getActionManager(): ActionManager
		{
			if (!this.actionManager)
			{
				this.actionManager = new ActionManager(this.dialogId);
			}

			return this.actionManager;
		},
		getBotService(): BotService
		{
			if (!this.botService)
			{
				this.botService = new BotService({
					messageId: this.messageId,
					dialogId: this.dialogId,
				});
			}

			return this.botService;
		},
	},
	template: `
		<div class="bx-im-keyboard__container">
			<template v-for="button in preparedButtons">
				<KeyboardButton
					v-if="button.type === ButtonType.button"
					:config="button"
					:keyboardBlocked="keyboardBlocked"
					@blockKeyboard="keyboardBlocked = true"
					@action="onButtonActionClick"
					@customCommand="onButtonCustomCommandClick"
				/>
				<KeyboardSeparator v-else-if="button.type === ButtonType.newLine" />
			</template>
		</div>
	`,
};
