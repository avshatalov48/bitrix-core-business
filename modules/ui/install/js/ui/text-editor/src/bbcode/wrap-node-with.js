/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
import { BBCodeElementNode, BBCodeNode } from 'ui.bbcode.model';
import { type TextEditor } from '../text-editor';

export function wrapNodeWith(node: BBCodeNode, tag: string, editor: TextEditor): BBCodeElementNode
{
	const scheme = editor.getBBCodeScheme();
	const elementNode = scheme.createElement({ name: tag });
	elementNode.appendChild(node);

	return elementNode;
}
