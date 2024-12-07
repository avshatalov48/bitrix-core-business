import { type TextEditorOptions } from 'ui.text-editor';
import { NewLineMode } from '../constants';
import { TextEditor } from '../text-editor';

/**
 * @memberof BX.UI.TextEditor
 */
export class BasicEditor extends TextEditor
{
	static getDefaultOptions(): TextEditorOptions
	{
		return {
			plugins: [
				'RichText',
				'Paragraph',
				'Clipboard',
				'Bold',
				'Underline',
				'Italic',
				'Strikethrough',
				'TabIndent',
				'List',
				'Mention',
				'Link',
				'AutoLink',
				'Image',
				'Copilot',
				'History',
				'BlockToolbar',
				'FloatingToolbar',
				'Toolbar',
				'Placeholder',
				'File',
			],
			toolbar: [
				'bold', 'italic', 'underline', 'strikethrough',
				'|',
				'numbered-list', 'bulleted-list',
				'|',
				'link', 'copilot',
			],
			newLineMode: NewLineMode.MIXED,
		};
	}
}
