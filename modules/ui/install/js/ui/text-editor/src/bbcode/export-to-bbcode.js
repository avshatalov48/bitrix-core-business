/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */

import { Type } from 'main.core';
import type {
	BBCodeScheme,
	BBCodeRootNode,
	BBCodeFragmentNode,
	BBCodeTextNode,
} from 'ui.bbcode.model';

import { TextEditor } from 'ui.text-editor';

import {
	$isElementNode,
	$isTextNode,
	$isLineBreakNode,
	$isTabNode,
	type LexicalNode, ElementNode,
} from 'ui.lexical.core';

import { trimEmptyParagraphs } from './trim-empty-paragraphs';

import type { BBCodeExportOutput, BBCodeExportMap, BBCodeExportFn } from './types';

export function $exportToBBCode(lexicalNode: LexicalNode | ElementNode, editor: TextEditor): BBCodeRootNode
{
	const scheme: BBCodeScheme = editor.getBBCodeScheme();
	const root: BBCodeRootNode = scheme.createRoot();
	const topLevelChildren = trimEmptyParagraphs(lexicalNode.getChildren());

	for (const topLevelNode of topLevelChildren)
	{
		$appendNodesToBBCode(topLevelNode, root, editor);
		// root.appendChild(scheme.createNewLine());
	}

	return root;
}

function $appendNodesToBBCode(currentNode: LexicalNode | ElementNode, parentNode: Node, editor: TextEditor): void
{
	const { node, after }: BBCodeExportOutput = getExportFunction(currentNode, editor);
	if (!node)
	{
		return;
	}

	const scheme: BBCodeScheme = editor.getBBCodeScheme();
	const fragment: BBCodeFragmentNode = scheme.createFragment();
	const children = $isElementNode(currentNode) ? currentNode.getChildren() : [];
	for (const childNode of children)
	{
		$appendNodesToBBCode(childNode, fragment, editor);
	}

	node.appendChild(fragment);
	parentNode.appendChild(node);

	if (Type.isFunction(after))
	{
		const newElement = after.call(currentNode, node);
		if (newElement)
		{
			node.getParent().replaceChild(node, newElement);
		}
	}
}

const formats = [
	'bold',
	'italic',
	'strikethrough',
	'underline',
];

function getExportFunction(lexicalNode: LexicalNode, editor: TextEditor): BBCodeExportOutput
{
	const type = lexicalNode.getType();
	const exportMap: BBCodeExportMap = editor.getBBCodeExportMap();
	const exportFn: BBCodeExportFn = exportMap.get(type);
	if (Type.isFunction(exportFn))
	{
		return exportFn(lexicalNode);
	}

	const scheme: BBCodeScheme = editor.getBBCodeScheme();
	if ($isTextNode(lexicalNode) && lexicalNode.getType() === 'text')
	{
		const node: BBCodeTextNode = scheme.createText({
			encode: false,
			content: lexicalNode.getTextContent(),
		});

		if (lexicalNode.getFormat() === 0)
		{
			return { node };
		}

		let currentNode: BBCodeTextNode = node;
		formats.forEach((format: string): void => {
			const formatFn: BBCodeExportFn = exportMap.get(`text:${format}`);
			if (Type.isFunction(formatFn))
			{
				currentNode = formatFn(lexicalNode, currentNode) || currentNode;
			}
		});

		return {
			node: currentNode,
		};
	}

	if ($isLineBreakNode(lexicalNode))
	{
		return {
			node: scheme.createNewLine(),
		};
	}

	if ($isTabNode(lexicalNode))
	{
		return {
			node: scheme.createTab(),
		};
	}

	if ($isTextNode(lexicalNode) || $isElementNode(lexicalNode))
	{
		const node: BBCodeTextNode = scheme.createText({
			encode: false,
			content: lexicalNode.getTextContent(),
		});

		return { node };
	}

	return { node: null };
}
