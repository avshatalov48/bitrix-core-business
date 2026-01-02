import { type TextEditor } from '../text-editor';

const paddings: WeakMap<TextEditor, { left: number, right: number }> = new WeakMap();

export function getEditorPaddings(editor: TextEditor): { left: number, right: number }
{
	if (paddings.has(editor))
	{
		return paddings.get(editor);
	}

	const scrollerContainer = editor.getEditableContainer();
	const computedStyle = window.getComputedStyle(scrollerContainer);
	const paddingLeft = parseFloat(computedStyle.paddingLeft) || 0;
	const paddingRight = parseFloat(computedStyle.paddingRight) || 0;

	const result = {
		left: paddingLeft,
		right: paddingRight,
	};

	paddings.set(editor, result);

	return result;
}
