import { Text } from 'main.core';

import { Logger } from 'im.v2.lib.logger';
import { KeyboardButtonDisplay, ColorToken } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import type { JsonObject } from 'main.core';
import type { KeyboardButtonConfig } from 'im.v2.const';

// @vue/component
export const KeyboardButton = {
	name: 'KeyboardButton',
	props:
	{
		config: {
			type: Object,
			required: true,
		},
		keyboardBlocked: {
			type: Boolean,
			required: true,
		},
	},
	emits: ['action', 'customCommand', 'blockKeyboard'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		button(): KeyboardButtonConfig
		{
			return this.config;
		},
		buttonClasses(): string[]
		{
			const { bgColorToken = ColorToken.base, display, disabled, wait } = this.button;
			const displayClass = display === KeyboardButtonDisplay.block ? '--block' : '--line';
			const classes = [displayClass, bgColorToken];
			if (this.keyboardBlocked || disabled)
			{
				classes.push('--disabled');
			}

			if (wait)
			{
				classes.push('--loading');
			}

			return classes;
		},
		buttonStyles(): { width?: string, backgroundColor?: string }
		{
			const styles = {};
			const { width } = this.button;
			if (width)
			{
				styles.width = `${width}px`;
			}

			return styles;
		},
	},
	methods:
	{
		onClick()
		{
			if (this.keyboardBlocked || this.button.disabled || this.button.wait)
			{
				return;
			}

			if (this.button.action && this.button.actionValue)
			{
				this.handleAction();
			}
			else if (this.button.appId)
			{
				Logger.warn('Messenger keyboard: open app is not implemented.');
			}
			else if (this.button.link)
			{
				const preparedLink = Text.decode(this.button.link);
				Utils.browser.openLink(preparedLink);
			}
			else if (this.button.command)
			{
				this.handleCustomCommand();
			}
		},
		handleAction()
		{
			this.$emit('action', {
				action: this.button.action,
				payload: this.button.actionValue,
			});
		},
		handleCustomCommand()
		{
			if (this.button.block)
			{
				this.$emit('blockKeyboard');
			}

			this.button.wait = true;

			this.$emit('customCommand', {
				botId: this.button.botId,
				command: this.button.command,
				payload: this.button.commandParams,
			});
		},
	},
	template: `
		<div
			class="bx-im-keyboard-button__container"
			:class="buttonClasses"
			:style="buttonStyles"
			@click="onClick"
		>
			{{ button.text }}
		</div>
	`,
};
