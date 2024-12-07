import type { LexicalNode, TextNode } from 'ui.lexical.core';
import {
	type BBCodeImportConversion,
	type BBCodeExportConversion,
} from '../../bbcode';

import { registerLexicalTextEntity } from 'ui.lexical.text';

import { HashtagNode, $createHashtagNode } from './hashtag-node';
import BasePlugin from '../base-plugin';

import { type TextEditor } from '../../text-editor';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

export class HashtagPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerListeners();
	}

	static getName(): string
	{
		return 'Hashtag';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [HashtagNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return null;
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			hashtag: (lexicalNode: TextNode, node: Node): Node | null => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createText(lexicalNode.getTextContent()),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			bbcodeMap: {
				hashtag: '#text',
			},
		};
	}

	#registerListeners(): void
	{
		const createHashtagNode = (textNode: TextNode): HashtagNode => {
			return $createHashtagNode(textNode.getTextContent());
		};

		const getHashtagMatch = (text: string) => {
			const match: RegExpMatchArray = /(?<=\s+|^)#([^\s,.<>[\]]+)/is.exec(text);
			if (match === null)
			{
				return null;
			}

			const hashtagLength = match[0].length;
			const startOffset = match.index;
			const endOffset = startOffset + hashtagLength;

			return {
				end: endOffset,
				start: startOffset,
			};
		};

		this.cleanUpRegister(
			...registerLexicalTextEntity(
				this.getLexicalEditor(),
				getHashtagMatch,
				HashtagNode,
				createHashtagNode,
			),
		);
	}
}
