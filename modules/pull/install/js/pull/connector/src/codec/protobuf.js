import { getDateForLog, isArray } from '../../../util/src/util';
import * as Models from './models';
import type { MessageCodec } from './messagecodec';
import type { ChannelManager } from './channelmanager';

type ProtobufCodecOptions = {
	channelManager: ChannelManager
}

export class ProtobufCodec implements MessageCodec
{
	constructor(options: ProtobufCodecOptions)
	{
		this.channelManager = options.channelManager;
	}

	extractMessages(pullEvent)
	{
		const result = [];
		try
		{
			const responseBatch = Models.ResponseBatch.decode(new Uint8Array(pullEvent));
			for (let i = 0; i < responseBatch.responses.length; i++)
			{
				const response = responseBatch.responses[i];
				if (response.command !== 'outgoingMessages')
				{
					continue;
				}

				const messages = response.outgoingMessages.messages;
				for (const message of messages)
				{
					let messageFields = {};
					try
					{
						messageFields = JSON.parse(message.body);
					}
					catch (e)
					{
						console.error(`${getDateForLog()}: Pull: Could not parse message body`, e);
						continue;
					}

					if (!messageFields.extra)
					{
						messageFields.extra = {};
					}
					messageFields.extra.sender = {
						type: message.sender.type,
					};

					if (message.sender.id instanceof Uint8Array)
					{
						messageFields.extra.sender.id = decodeId(message.sender.id);
					}

					const compatibleMessage = {
						mid: decodeId(message.id),
						text: messageFields,
					};

					result.push(compatibleMessage);
				}
			}
		}
		catch (e)
		{
			console.error(`${getDateForLog()}: Pull: Could not parse message`, e);
		}

		return result;
	}

	async encodeMessageBatch(messageBatch)
	{
		const userIds = {};
		for (const element of messageBatch)
		{
			if (element.userList)
			{
				for (let j = 0; j < element.userList.length; j++)
				{
					userIds[element.userList[j]] = true;
				}
			}
		}
		const publicIds = await this.channelManager.getPublicIds(Object.keys(userIds));

		return this.encodeMessageBatchInternal(messageBatch, publicIds);
	}

	encodeMessageBatchInternal(messageBatch, publicIds): Uint8Array
	{
		const messages = [];
		messageBatch.forEach((messageFields) => {
			const messageBody = messageFields.body;

			let receivers = [];
			if (messageFields.userList)
			{
				receivers = this.createMessageReceivers(messageFields.userList, publicIds);
			}

			if (messageFields.channelList)
			{
				if (!isArray(messageFields.channelList))
				{
					throw new TypeError('messageFields.publicChannels must be an array');
				}
				messageFields.channelList.forEach((publicChannel) => {
					let publicId = '';
					let signature = '';
					if (typeof (publicChannel) === 'string' && publicChannel.includes('.'))
					{
						const fields = publicChannel.toString().split('.');
						publicId = fields[0];
						signature = fields[1];
					}
					else if (typeof (publicChannel) === 'object' && ('publicId' in publicChannel) && ('signature' in publicChannel))
					{
						publicId = publicChannel.publicId;
						signature = publicChannel.signature;
					}
					else
					{
						throw new Error('Public channel MUST be either a string, formatted like "{publicId}.{signature}" or an object with fields \'publicId\' and \'signature\'');
					}

					receivers.push(Models.Receiver.create({
						id: this.encodeId(publicId),
						signature: this.encodeId(signature),
					}));
				});
			}

			const message = Models.IncomingMessage.create({
				receivers,
				body: JSON.stringify(messageBody),
				expiry: messageFields.expiry || 0,
			});
			messages.push(message);
		});

		const requestBatch = Models.RequestBatch.create({
			requests: [{
				incomingMessages: {
					messages,
				},
			}],
		});

		return Models.RequestBatch.encode(requestBatch).finish();
	}

	createMessageReceivers(users, publicIds): Models.Receiver[]
	{
		const result = [];
		for (const userId of users)
		{
			if (!publicIds[userId] || !publicIds[userId].publicId)
			{
				throw new Error(`Could not determine public id for user ${userId}`);
			}

			result.push(Models.Receiver.create({
				id: this.encodeId(publicIds[userId].publicId),
				signature: this.encodeId(publicIds[userId].signature),
			}));
		}

		return result;
	}

	/**
	 * Converts message id from hex-encoded string to byte[]
	 * @param {string} id Hex-encoded string.
	 * @return {Uint8Array}
	 */
	encodeId(id: string): Uint8Array
	{
		if (!id)
		{
			return new Uint8Array();
		}

		const result = [];
		for (let i = 0; i < id.length; i += 2)
		{
			result.push(parseInt(id.slice(i, i + 2), 16));
		}

		return new Uint8Array(result);
	}
}

/**
 * Converts message id from byte[] to string
 */
function decodeId(encodedId: Uint8Array): string
{
	if (!(encodedId instanceof Uint8Array))
	{
		throw new TypeError('encodedId should be an instance of Uint8Array');
	}

	let result = '';
	for (const element of encodedId)
	{
		const hexByte = element.toString(16);
		if (hexByte.length === 1)
		{
			result += '0';
		}
		result += hexByte;
	}

	return result;
}