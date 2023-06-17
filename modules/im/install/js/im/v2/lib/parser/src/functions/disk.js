import {Loc} from 'main.core';

import {getConst} from '../utils/core-proxy';
import {ParserIcon} from './icon';

const {FileIconType} = getConst();

export const ParserDisk = {

	decode(text): string
	{
		const icon = ParserIcon.getIcon(FileIconType.file);

		let diskText;
		if (icon)
		{
			diskText = `${icon} ${Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}`;
		}
		else
		{
			diskText = `[${Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}]`;
		}

		text = text.replace(/\[disk=\d+]/gi, diskText);

		return text;
	},

	purify(text): string
	{
		return this.decode(text);
	},
};
