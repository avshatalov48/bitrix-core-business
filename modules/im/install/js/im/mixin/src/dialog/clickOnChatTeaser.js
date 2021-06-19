import { EventEmitter } from 'main.core.events';
import { EventType, RestMethod } from "im.const";

//openDialog is in dialogCore
export const DialogClickOnChatTeaser = {
	created()
	{
		EventEmitter.subscribe(EventType.dialog.clickOnChatTeaser, this.onClickOnChatTeaser);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.clickOnChatTeaser, this.onClickOnChatTeaser);
	},
	methods: {
		onClickOnChatTeaser({data: event})
		{
			this.joinParentChat(event.message.id, 'chat'+event.message.params.CHAT_ID).then((dialogId) => {
				this.openDialog(dialogId);
			}).catch(() => {});

			return true;
		},
		joinParentChat(messageId, dialogId)
		{
			return new Promise((resolve, reject) =>
			{
				if (!messageId || !dialogId)
				{
					return reject();
				}

				if (typeof this.tempJoinChat === 'undefined')
				{
					this.tempJoinChat = {};
				}
				else if (this.tempJoinChat['wait'])
				{
					return reject();
				}

				this.tempJoinChat['wait'] = true;

				this.getRestClient().callMethod(RestMethod.imChatParentJoin, {
					'DIALOG_ID': dialogId,
					'MESSAGE_ID': messageId
				}).then(() => {
					this.tempJoinChat['wait'] = false;
					this.tempJoinChat[dialogId] = true;

					return resolve(dialogId);
				}).catch(() => {
					this.tempJoinChat['wait'] = false;

					return reject();
				});
			});
		}
	}
};