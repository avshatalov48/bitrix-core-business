import {Dom, Loc, Type} from 'main.core';

import {getConst} from '../utils/core-proxy';
import {Parser} from '../parser';

const {FileType, FileIconType, AttachDescription} = getConst();


export const ParserIcon = {
	getIcon(icon: $Values<typeof FileIconType>, fallbackText: string = ''): string
	{
		return fallbackText;
		/*
		if (!FileIconType[icon])
		{
			return fallbackText;
		}

		return Dom.create({
			tag: 'span',
			attrs: {
				className: `bx-im-icon --${icon}`,
			},
		}).outerHTML;
		 */
	},

	addIconToShortText(config: {
		text: string,
		attach: boolean | string | Array,
		files: boolean | Array
	}): string
	{
		let {text} = config;
		const {attach, files} = config;

		if (Type.isArray(files) && files.length > 0)
		{
			text = this.getTextForFile(text, files);
		}
		else if (
			attach === true
			|| (Type.isArray(attach) && attach.length > 0)
			|| Type.isStringFilled(attach)
		)
		{
			text = this.getTextForAttach(text, attach);
		}

		return text.trim();
	},

	getQuoteBlock(): string
	{
		const icon = this.getIcon(FileIconType.quote);
		if (icon)
		{
			return icon;
		}

		return `[${Loc.getMessage('IM_PARSER_ICON_TYPE_QUOTE')}]`;
	},

	getCodeBlock(): string
	{
		const icon = this.getIcon(FileIconType.code);
		if (icon)
		{
			return icon;
		}

		return `[${Loc.getMessage('IM_PARSER_ICON_TYPE_CODE')}]`;
	},

	getImageBlock(): string
	{
		const icon = this.getIcon(FileIconType.image);
		if (icon)
		{
			return icon;
		}

		return `[${Loc.getMessage('IM_PARSER_ICON_TYPE_IMAGE')}]`;
	},

	getFileBlock(): string
	{
		const icon = this.getIcon(FileIconType.file);
		if (icon)
		{
			return icon;
		}

		return `[${Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}]`;
	},

	getTextForFile(text: string, files: boolean | Array): string
	{
		if (Type.isArray(files) && files.length > 0)
		{
			const [firstFile] = files;
			text = this.getIconTextForFile(text, firstFile);
		}
		else if (files === true)
		{
			text = this.getIconTextForFileType(text, FileIconType.file);
		}

		return text;
	},

	getTextForAttach(text: string, attach: boolean | string | Array): string
	{
		let attachDescription = '';
		if (Type.isArray(attach) && attach.length > 0)
		{
			const [firstAttach] = attach;
			if (Type.isStringFilled(firstAttach.DESCRIPTION))
			{
				attachDescription = firstAttach.DESCRIPTION;
			}
		}
		else if (Type.isStringFilled(attach))
		{
			attachDescription = attach;
		}

		if (Type.isStringFilled(attachDescription))
		{
			if (attachDescription === AttachDescription.SKIP_MESSAGE)
			{
				attachDescription = '';
			}
			else
			{
				attachDescription = Parser.purifyText(attachDescription, {showPhraseMessageWasDeleted: false});
			}
		}
		else
		{
			const icon = this.getIcon(FileIconType.attach);
			if (icon)
			{
				attachDescription = `${icon} ${Loc.getMessage('IM_PARSER_ICON_TYPE_ATTACH')}`;
			}
			else
			{
				attachDescription = `[${Loc.getMessage('IM_PARSER_ICON_TYPE_ATTACH')}]`;
			}
		}

		return `${text} ${attachDescription}`.trim();
	},

	getIconTextForFileType(text: string, type: $Values<typeof FileIconType> = FileIconType.file): string
	{
		let result = text;
		const icon = this.getIcon(type);
		const iconText = Loc.getMessage(`IM_PARSER_ICON_TYPE_${type.toUpperCase()}`);
		if (icon)
		{
			const withText = text.replace(/(\s|\n)/gi, '').length > 0;
			const textDescription = withText? text: iconText;
			result = `${icon} ${textDescription}`;
		}
		else
		{
			result = `[${iconText}] ${text}`;
		}

		return result.trim();
	},

	getIconTextForFile(text: string, file: Object): string
	{
		const withText = text.replace(/(\s|\n)/gi, '').length > 0;

		// todo: remove this hack after fix receiving messages with files on P&P
		if (!file || !file.type)
		{
			return text;
		}

		if (file.type === FileType.image)
		{
			return this.getIconTextForFileType(text, FileIconType.image);
		}
		else if (file.type === FileType.audio)
		{
			return this.getIconTextForFileType(text, FileIconType.audio);
		}
		else if (file.type === FileType.video)
		{
			return this.getIconTextForFileType(text, FileIconType.video);
		}
		else
		{
			const icon = this.getIcon(FileIconType.file);
			if (icon)
			{
				const textDescription = withText? text: '';
				text = `${icon} ${file.name} ${textDescription}`;
			}
			else
			{
				text = `${Loc.getMessage('IM_PARSER_ICON_TYPE_FILE')}: ${file.name} ${text}`;
			}

			return text.trim();
		}
	}
};