import { MessageComponent } from 'im.v2.const';
import { DemoChatBuilder } from 'im.v2.lib.demo';

export const initDemoState = (): string => {
	const chatBuilder = new DemoChatBuilder();

	const { dialogId } = chatBuilder.addChat();

	chatBuilder.addMessage({
		text: dialogId,
		componentId: MessageComponent.taskChatCreationMessage,
	});

	chatBuilder.save();

	return dialogId;
};
