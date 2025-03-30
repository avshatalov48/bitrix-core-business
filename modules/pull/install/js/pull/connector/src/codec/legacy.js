import type { MessageCodec } from './messagecodec';

export class LegacyCodec implements MessageCodec
{
	async encodeMessageBatch(messageBatch)
	{
		return null;
	}

	extractMessages(pullEvent: string)
	{
		const result = [];
		const dataArray = pullEvent.match(/#!NGINXNMS!#(.*?)#!NGINXNME!#/gm);
		if (dataArray === null)
		{
			const text = '\n========= PULL ERROR ===========\n'
				+ 'Error type: parseResponse error parsing message\n'
				+ '\n'
				+ `Data string: ${pullEvent}\n`
				+ '================================\n\n';
			console.error(text);

			return result;
		}

		for (let i = 0; i < dataArray.length; i++)
		{
			dataArray[i] = dataArray[i].slice(12, -12);
			if (dataArray[i].length <= 0)
			{
				continue;
			}

			let data = {};
			try
			{
				data = JSON.parse(dataArray[i]);
			}
			catch
			{
				continue;
			}

			result.push(data);
		}

		return result;
	}
}
