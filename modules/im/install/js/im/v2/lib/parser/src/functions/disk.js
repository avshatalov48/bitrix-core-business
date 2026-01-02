import { Loc } from 'main.core';

export const ParserDisk = {
	decode(text: string): string
	{
		const diskText = `[${Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}]`;

		return text.replaceAll(/\[disk=\d+]/gi, diskText);
	},
	purify(text: string): string
	{
		return this.decode(text);
	},
};
