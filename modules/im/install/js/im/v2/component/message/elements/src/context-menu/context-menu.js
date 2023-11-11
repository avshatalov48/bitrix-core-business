import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { Quote } from 'im.v2.lib.quote';

import './context-menu.css';


// @vue/component
export const ContextMenu = {
	name: 'ContextMenu',
	props:
	{
		message: {
			type: Object,
			required: true,
		},
		menuIsActiveForId: {
			type: [String, Number],
			default: 0,
		},
	},
	computed:
	{
		menuTitle(): string
		{
			return this.$Bitrix.Loc.getMessage(
				'IM_MESSENGER_MESSAGE_MENU_TITLE',
				{ '#SHORTCUT#': Utils.platform.isMac() ? 'CMD' : 'CTRL' },
			);
		},
	},
	methods:
	{
		onMenuClick(event: PointerEvent)
		{
			if (Utils.key.isCombination(event, ['Alt+Ctrl']))
			{
				const message = { ...this.message };

				EventEmitter.emit(EventType.textarea.insertText, {
					text: Quote.prepareQuoteText(message),
					withNewLine: true,
					replace: false,
				});

				return;
			}

			if (Utils.key.isCmdOrCtrl(event))
			{
				const message = { ...this.message };
				EventEmitter.emit(EventType.textarea.replyMessage, {
					messageId: message.id,
				});

				return;
			}

			EventEmitter.emit(EventType.dialog.onClickMessageContextMenu, {
				message: this.message,
				event,
			});
		},
	},
	template: `
		<div class="bx-im-message-context-menu__container bx-im-message-context-menu__scope">
			<button
				:title="menuTitle"
				@click="onMenuClick"
				@contextmenu.prevent
				:class="{'--active': menuIsActiveForId === message.id}"
				class="bx-im-message-context-menu__button"
			></button>
		</div>
	`,
};
