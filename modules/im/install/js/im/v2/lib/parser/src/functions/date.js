import { Reflection, Text } from 'main.core';

export const ParserDate = {
	decode(text: string): string
	{
		return handleTimestampCode(text);
	},

	purify(text: string): string
	{
		return handleTimestampCode(text);
	},
};

const handleTimestampCode = (text: string): string => {
	// [timestamp=1645844720 format=SHORT_TIME_FORMAT]
	const regex = /\[timestamp=(?<timestamp>\d+)\s+format=(?<format>[_a-z]+)]/gi;

	return text.replaceAll(regex, (initialText, ...args) => {
		const { timestamp, format } = args.at(-1);

		const DateFormatter = Reflection.getClass('BX.Messenger.v2.Lib.DateFormatter');
		const DateFormat = Reflection.getClass('BX.Messenger.v2.Lib.DateFormat');
		const DateCode = Reflection.getClass('BX.Messenger.v2.Lib.DateCode');
		if (!DateFormatter)
		{
			return initialText;
		}

		const timestampInMilliseconds = Number(timestamp) * 1000;
		const date = new Date(timestampInMilliseconds);

		const preparedFormat = Text.toCamelCase(format);
		const availableFormats = Object.keys(DateFormat);
		if (!availableFormats.includes(preparedFormat))
		{
			return initialText;
		}

		return DateFormatter.formatByCode(date, DateCode[preparedFormat]);
	});
};
