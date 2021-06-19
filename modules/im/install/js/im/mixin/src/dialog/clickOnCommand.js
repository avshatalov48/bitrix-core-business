import { Logger } from "im.lib.logger";
import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

/**
 * @notice needs TextareaCore mixin
 */
export const DialogClickOnCommand = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnCommand, this.onClickOnCommand);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnCommand, this.onClickOnCommand);
	},
	methods: {
		onClickOnCommand({data: event})
		{
			if (event.type === 'put')
			{
				this.insertText({ text: event.value + ' ' });
			}
			else if (event.type === 'send')
			{
				this.addMessageOnClient(event.value);
			}
			else
			{
				Logger.warn('Unprocessed command', event);
			}
		}
	}
};