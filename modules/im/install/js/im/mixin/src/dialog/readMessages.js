import { Logger } from "im.lib.logger";
import { RestMethod } from "im.const";

import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

export const DialogReadMessages = {
	data()
	{
		return {
			lastMessageToRead: null,
			messagesToRead: []
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.dialog.readMessage, this.onReadMessage);
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.readMessage, this.onReadMessage);
	},
	methods: {
		onReadMessage({data: event})
		{
			this.readMessage(event.id)
				.then(() => Logger.log('Read message complete'))
				.catch(() => Logger.error('Read message failed'));
		},
		readMessage(messageId = null, force = false, skipAjax = false)
		{
			if (messageId)
			{
				this.messagesToRead.push(parseInt(messageId));
			}

			this.timer.stop('readMessage', this.chatId, true);
			this.timer.stop('readMessageServer', this.chatId, true);

			if (force)
			{
				return this.readMessageRequest(skipAjax);
			}

			return new Promise((resolve, reject) => {
				this.timer.start('readMessage', this.chatId, .1, () => {
					this.readMessageRequest(skipAjax).then(result => resolve(result)).catch(reject)
				});
			});
		},
		readMessageRequest(skipAjax = false)
		{
			return new Promise((resolve, reject) =>
			{
				//get max message id from queue
				for (const messageId of this.messagesToRead)
				{
					if (!this.lastMessageToRead)
					{
						this.lastMessageToRead = messageId;
					}
					else if (this.lastMessageToRead < messageId)
					{
						this.lastMessageToRead = messageId;
					}
				}
				this.messagesToRead = [];

				const lastId = this.lastMessageToRead || 0;
				if (lastId <= 0)
				{
					return resolve({lastId: 0});
				}

				//read messages on front
				this.$store.dispatch('messages/readMessages', {
					chatId: this.chatId,
					readId: lastId
				}).then(result =>
				{
					//decrease counter
					return this.$store.dispatch('dialogues/decreaseCounter', {
						dialogId: this.dialogId,
						count: result.count
					});
				}).then(() => {
					if (skipAjax)
					{
						return resolve({lastId});
					}

					//read messages on server in .5s
					this.timer.start('readMessageServer', this.chatId, .5, () => {
						this.getRestClient().callMethod(RestMethod.imDialogRead, {
							'DIALOG_ID': this.dialogId,
							'MESSAGE_ID': lastId
						}).then(() => resolve({lastId})).catch(reject);
					});
				})
				.catch(reject);
			});
		}
	}
};