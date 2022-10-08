/**
 * Bitrix Messenger
 * Attach element Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import './keyboard.css';
import {BitrixVue} from 'ui.vue';
import {Utils} from "im.lib.utils";
import {Logger} from "im.lib.logger";

const ButtonType = Object.freeze({
	newline: 'NEWLINE',
	button: 'BUTTON'
});

BitrixVue.component('bx-im-view-element-keyboard',
{
	/*
	 * @emits 'click' {action: string, params: Object}
	 */
	props:
	{
		buttons: {type: Array, default: () => []},
		messageId: {default: 0},
		userId: {default: 0},
		dialogId: {default: 0},
	},
	data: function()
	{
		return {
			isMobile : Utils.platform.isMobile(),
			isBlocked : false,
			localButtons : [],
		}

	},
	created()
	{
		this.localButtons = this.prepareButtons(this.buttons);
	},
	watch:
	{
		buttons()
		{
			clearTimeout(this.recoverStateButton);

			this.isBlocked = false;
			this.localButtons = this.prepareButtons(this.buttons);
		}
	},
	methods:
	{
		click(button)
		{
			if (this.isBlocked)
			{
				return false;
			}

			if (button.DISABLED && button.DISABLED === 'Y')
			{
				return false;
			}

			if (button.ACTION && button.ACTION_VALUE.toString())
			{
				this.$emit('click', {action: 'ACTION', params: {
					dialogId: this.dialogId,
					messageId: this.messageId,
					botId: button.BOT_ID,
					action: button.ACTION,
					value: button.ACTION_VALUE,
				}});
			}
			else if (button.FUNCTION)
			{
				let execFunction = button.FUNCTION.toString()
					.replace('#MESSAGE_ID#', this.messageId)
					.replace('#DIALOG_ID#', this.dialogId)
					.replace('#USER_ID#', this.userId);
				eval(execFunction);
			}
			else if (button.APP_ID)
			{
				Logger.warn('Messenger keyboard: open app is not implemented.');
			}
			else if (button.LINK)
			{
				if (Utils.platform.isBitrixMobile())
				{
					app.openNewPage(button.LINK);
				}
				else
				{
					window.open(button.LINK, '_blank');
				}
			}
			else if (button.WAIT !== 'Y')
			{
				if (button.BLOCK === 'Y')
				{
					this.isBlocked = true;
				}

				button.WAIT = 'Y';

				this.$emit('click', {action: 'COMMAND', params: {
					dialogId: this.dialogId,
					messageId: this.messageId,
					botId: button.BOT_ID,
					command: button.COMMAND,
					params: button.COMMAND_PARAMS,
				}});

				this.recoverStateButton = setTimeout(() => {
					this.isBlocked = false;
					button.WAIT = 'N';
				}, 10000)
			}

			return true;
		},
		getStyles(button)
		{
			let styles = {};
			if (button.WIDTH)
			{
				styles['width'] = button.WIDTH+'px';
			}
			else if (button.DISPLAY === 'BLOCK')
			{
				styles['width'] = '225px';
			}
			if (button.BG_COLOR)
			{
				styles['backgroundColor'] = button.BG_COLOR;
			}
			if (button.TEXT_COLOR)
			{
				styles['color'] = button.TEXT_COLOR;
			}

			return styles;
		},

		prepareButtons(buttons)
		{
			return buttons.filter(button =>
			{
				if (!button.CONTEXT)
				{
					return true;
				}

				if (Utils.platform.isBitrixMobile() && button.CONTEXT === 'DESKTOP')
				{
					return false;
				}

				if (!Utils.platform.isBitrixMobile() && button.CONTEXT === 'MOBILE')
				{
					return false;
				}

				// TODO activate this buttons
				if (
					!Utils.platform.isBitrixMobile()
					&& (button.ACTION === 'DIALOG' || button.ACTION === 'CALL')
				)
				{
					return false;
				}

				return true;
			});
		},
	},
	computed:
	{
		ButtonType: () => ButtonType,
	},
	template: `
		<div :class="['bx-im-element-keyboard', {'bx-im-element-keyboard-mobile': isMobile}]">
			<template v-for="(button, index) in localButtons">
				<div v-if="button.TYPE === ButtonType.newline" class="bx-im-element-keyboard-button-separator"></div>
				<span v-else-if="button.TYPE === ButtonType.button" :class="[
					'bx-im-element-keyboard-button', 
					'bx-im-element-keyboard-button-'+button.DISPLAY.toLowerCase(), 
					{
						'bx-im-element-keyboard-button-disabled': isBlocked || button.DISABLED === 'Y',
						'bx-im-element-keyboard-button-progress': button.WAIT === 'Y',
					}
				]" @click="click(button)">
					<span class="bx-im-element-keyboard-button-text" :style="getStyles(button)">{{button.TEXT}}</span>
				</span>
			</template>
		</div>
	`
});