import { Type } from 'main.core';
import {
	BBCodeNewLineNode,
	BBCodeTabNode,
	BBCodeNode,
	type BBCodeRootNode,
	type BBCodeTextNode,
	type BBCodeElementNode,
	type BBCodeScheme,
	type BBCodeContentNode,
} from 'ui.bbcode.model';

import { BBCodeParser } from 'ui.bbcode.parser';
import { TextEditor } from 'ui.text-editor';
import type { BBCodeTextNodeContent } from '../../../bbcode/model/src/nodes/text-node';

import {
	type BBCodeChildConversion,
	type BBCodeConversionOutput,
	type BBCodeConversionFn,
	type BBCodeImportMap,
	type BBCodeConversion,
} from './types';

import {
	$isElementNode,
	$isLineBreakNode,
	$createLineBreakNode,
	$createTabNode,
	$createTextNode,
	$createParagraphNode,
	$isDecoratorNode,
	type LexicalNode,
	type ElementNode,
	type ParagraphNode,
} from 'ui.lexical.core';

export function $importFromBBCode(bbcode: string, editor: TextEditor, normalize: boolean = true): Array<LexicalNode>
{
	const scheme: BBCodeScheme = editor.getBBCodeScheme();
	const parser: BBCodeParser = new BBCodeParser({ scheme });
	const ast: BBCodeRootNode = parser.parse(bbcode);
	const elements: BBCodeContentNode = ast.getChildren();

	// console.log(ast);

	let lexicalNodes = [];
	for (const element of elements)
	{
		const nodes = $createNodesFromBBCode(element, editor);
		if (nodes !== null)
		{
			lexicalNodes = [...lexicalNodes, ...nodes];
		}
	}

	return normalize ? $normalizeTextNodes(lexicalNodes) : lexicalNodes;
}

function $createNodesFromBBCode(
	node: BBCodeContentNode,
	editor: TextEditor,
	forChildMap: Map<string, BBCodeChildConversion> = new Map(),
	parentLexicalNode: LexicalNode | null = null,
): Array<LexicalNode>
{
	if (node instanceof BBCodeNewLineNode)
	{
		return [$createLineBreakNode()];
	}

	if (node instanceof BBCodeTabNode)
	{
		return [$createTabNode()];
	}

	let lexicalNodes: Array<LexicalNode> = [];
	let currentLexicalNode = null;

	const transformFunction: BBCodeConversionFn | null = getConversionFunction(node, editor);
	const transformOutput: BBCodeConversionOutput | null = transformFunction ? transformFunction(node) : null;
	let postTransform = null;
	if (transformOutput !== null)
	{
		postTransform = transformOutput.after;
		const transformNodes = transformOutput.node;
		currentLexicalNode = Array.isArray(transformNodes) ? transformNodes[transformNodes.length - 1] : transformNodes;
		if (currentLexicalNode !== null)
		{
			for (const [, forChildFunction] of forChildMap)
			{
				currentLexicalNode = forChildFunction(currentLexicalNode, parentLexicalNode);
				if (!currentLexicalNode)
				{
					break;
				}
			}

			if (currentLexicalNode)
			{
				lexicalNodes.push(...(Array.isArray(transformNodes) ? transformNodes : [currentLexicalNode]));
			}
		}

		if (Type.isFunction(transformOutput.forChild))
		{
			forChildMap.set(node.getName(), transformOutput.forChild);
		}
	}

	const children = node.getChildren();
	let childLexicalNodes = [];
	for (const child of children)
	{
		childLexicalNodes.push(
			...$createNodesFromBBCode(
				child,
				editor,
				new Map(forChildMap),
				currentLexicalNode,
			),
		);
	}

	if (Type.isFunction(postTransform))
	{
		childLexicalNodes = postTransform(childLexicalNodes);
	}

	// Unknown node
	if (transformOutput === null)
	{
		if (node.getType() === BBCodeNode.ELEMENT_NODE)
		{
			const elementNode: BBCodeElementNode = node;
			if (elementNode.isVoid())
			{
				childLexicalNodes = [$createTextNode(elementNode.getOpeningTag()), ...childLexicalNodes];
			}
			else
			{
				childLexicalNodes = [
					$createTextNode(elementNode.getOpeningTag()),
					...childLexicalNodes,
					$createTextNode(elementNode.getClosingTag()),
				];
			}
		}
		else
		{
			childLexicalNodes = [$createTextNode(node.toString()), ...childLexicalNodes];
		}
	}

	if (currentLexicalNode === null)
	{
		// If it hasn't been converted to a LexicalNode, we hoist its children
		// up to the same level as it.
		lexicalNodes = [...lexicalNodes, ...childLexicalNodes];
	}
	else if ($isElementNode(currentLexicalNode))
	{
		// If the current node is a ElementNode after conversion,
		// we can append all the children to it.
		currentLexicalNode.append(...childLexicalNodes);
	}

	return lexicalNodes;
}

export function shouldWrapInParagraph(lexicalNode: LexicalNode | ElementNode): boolean
{
	if ($isElementNode(lexicalNode) && lexicalNode.isInline() === false)
	{
		return false;
	}

	return !($isDecoratorNode(lexicalNode) && lexicalNode.isInline() === false);
}

export function $normalizeTextNodes(lexicalNodes: Array<LexicalNode>): Array<LexicalNode>
{
	const result = [];
	let currentParagraph = null;
	let lineBreaks = 0;

	for (const lexicalNode of lexicalNodes)
	{
		if ($isLineBreakNode(lexicalNode))
		{
			lineBreaks++;

			continue;
		}

		if (shouldWrapInParagraph(lexicalNode))
		{
			if (currentParagraph === null || lineBreaks >= 2)
			{
				result.push(...$createEmptyParagraphs(lineBreaks - 2));
				currentParagraph = $createParagraphNode();
				result.push(currentParagraph);
			}
			else if (lineBreaks === 1)
			{
				currentParagraph.append($createLineBreakNode());
			}

			currentParagraph.append(lexicalNode);
		}
		else
		{
			if (lineBreaks > 2)
			{
				result.push(...$createEmptyParagraphs(lineBreaks - 2));
			}

			result.push(lexicalNode);
			currentParagraph = null;
		}

		lineBreaks = 0;
	}

	if (result.length === 0)
	{
		return [$createParagraphNode()];
	}

	return result;
}

function $createEmptyParagraphs(count: number = 1): Array<ParagraphNode>
{
	const result = [];
	for (let i = 0; i < count; i++)
	{
		result.push($createParagraphNode());
	}

	return result;
}

function getConversionFunction(node: BBCodeNode, editor: TextEditor): BBCodeConversionFn | null
{
	const nodeName: string = node.getName();
	let currentConversion: BBCodeConversion | null = null;
	const importMap: BBCodeImportMap = editor.getBBCodeImportMap();
	const conversions = importMap.get(nodeName.toLowerCase());
	if (conversions !== undefined)
	{
		for (const conversion of conversions)
		{
			const bbCodeConversion: BBCodeConversion = conversion(node);
			if (
				bbCodeConversion !== null
				&& (currentConversion === null || currentConversion.priority < bbCodeConversion.priority)
			)
			{
				currentConversion = bbCodeConversion;
			}
		}
	}

	if (currentConversion === null)
	{
		if (nodeName === '#text')
		{
			return convertTextNode;
		}

		return null;
	}

	return currentConversion.conversion;
}

function convertTextNode(textNode: BBCodeTextNode): BBCodeConversionOutput
{
	let textContent: BBCodeTextNodeContent = textNode.getContent();
	textContent = textContent
		.replaceAll(/\r?\n|\t/gm, ' ')
		.replace('\r', '')
	;

	if (textNode.getParent().getName() !== 'code')
	{
		textContent = textContent.replaceAll(/\s+/g, ' ');
	}

	if (textContent === '')
	{
		return { node: null };
	}

	return { node: $createTextNode(textContent) };
}
