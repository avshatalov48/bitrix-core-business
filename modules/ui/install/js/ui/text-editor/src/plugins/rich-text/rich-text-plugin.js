import { registerRichText } from 'ui.lexical.rich-text';
import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';

export class RichTextPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.cleanUpRegister(
			registerRichText(editor.getLexicalEditor()),
		);
	}

	static getName(): string
	{
		return 'RichText';
	}
}
