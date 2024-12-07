import { Type } from 'main.core';
import { $isTextNode, LexicalNode, type TextNode } from 'ui.lexical.core';
import { $isLinkNode, type LinkNode } from 'ui.lexical.link';
import { $isCodeNode, $isCodeTokenNode, type CodeNode, type CodeTokenNode } from '../plugins/code';
import {
	$isFileNode,
	$isFileImageNode,
	$isFileVideoNode,
	type FileNode,
	type FileImageNode,
	type FileVideoNode,
} from '../plugins/file';

import { $isImageNode, type ImageNode } from '../plugins/image';
import { $isMentionNode, type MentionNode } from '../plugins/mention';
import { $isSmileyNode, type SmileyNode } from '../plugins/smiley';
import { $isVideoNode, type VideoNode } from '../plugins/video';

import { DETAIL_PREDICATES, MODE_PREDICATES, NON_SINGLE_WIDTH_CHARS_REPLACEMENT } from './constants';
import { printFormatProperties } from './print-format-properties';

export function printNode(node: LexicalNode): string
{
	if ($isCodeTokenNode(node))
	{
		const codeTokenNode: CodeTokenNode = node;

		return `{ ${codeTokenNode.__highlightType}: "${normalize(codeTokenNode.getTextContent())}" }`;
	}

	if ($isCodeNode(node))
	{
		const codeTokenNode: CodeNode = node;

		return `{ children: ${codeTokenNode.getChildrenSize()} }`;
	}

	if ($isTextNode(node))
	{
		const text = node.getTextContent();
		const title = text.length === 0 ? '(empty)' : `"${normalize(text)}"`;
		const properties = printAllTextNodeProperties(node);

		return [title, properties.length > 0 ? `{ ${properties} }` : null]
			.filter(Boolean)
			.join(' ')
			.trim();
	}

	if ($isFileImageNode(node))
	{
		const fileImageNode: FileImageNode = node;

		return `{ id: ${fileImageNode.getId()}, width: ${fileImageNode.getWidth()}, height: ${fileImageNode.getHeight()} }`;
	}

	if ($isFileNode(node))
	{
		const fileNode: FileNode = node;

		return `{ id: ${fileNode.getId()} }`;
	}

	if ($isFileVideoNode(node))
	{
		const fileVideoNode: FileVideoNode = node;

		return `{ id: ${fileVideoNode.getId()} }`;
	}

	if ($isSmileyNode(node))
	{
		const smileyNode: SmileyNode = node;

		return `{ typing: ${smileyNode.getTyping()}, width: ${smileyNode.getWidth()}, height: ${smileyNode.getHeight()} }`;
	}

	if ($isVideoNode(node))
	{
		const videoNode: VideoNode = node;

		return `{ width: ${videoNode.getWidth()}, height: ${videoNode.getHeight()} }`;
	}

	if ($isMentionNode(node))
	{
		const mentionNode: MentionNode = node;

		return `{ entityId: ${mentionNode.getEntityId()}, id: ${mentionNode.getId()} }`;
	}

	if ($isImageNode(node))
	{
		const imageNode: ImageNode = node;

		return `{ width: ${imageNode.getWidth()}, height: ${imageNode.getHeight()} }`;
	}

	if ($isLinkNode(node))
	{
		const linkNode: LinkNode = node;
		const link = linkNode.getURL();
		const title = link.length === 0 ? '(empty)' : `"${normalize(link)}"`;
		const properties = printAllLinkNodeProperties(linkNode);

		return [title, properties.length > 0 ? `{ ${properties} }` : null]
			.filter(Boolean)
			.join(' ')
			.trim();
	}

	return '';
}

function normalize(text: string)
{
	return Object.entries(NON_SINGLE_WIDTH_CHARS_REPLACEMENT).reduce(
		(acc, [key, value]) => acc.replace(new RegExp(key, 'g'), String(value)),
		text,
	);
}

function printAllTextNodeProperties(node: TextNode): string
{
	return [
		printFormatProperties(node),
		printDetailProperties(node),
		printModeProperties(node),
	]
		.filter(Boolean)
		.join(', ');
}

function printAllLinkNodeProperties(node: LinkNode): string
{
	return [
		printTargetProperties(node),
		printRelProperties(node),
		printTitleProperties(node),
	]
		.filter(Boolean)
		.join(', ');
}

function printTargetProperties(node: LinkNode): string
{
	let str = node.getTarget();
	if (!Type.isNil(str))
	{
		str = `target: ${str}`;
	}

	return str;
}

function printRelProperties(node: LinkNode): string
{
	let str = node.getRel();
	if (!Type.isNil(str))
	{
		str = `rel: ${str}`;
	}

	return str;
}

function printTitleProperties(node: LinkNode): string
{
	let str = node.getTitle();
	if (!Type.isNil(str))
	{
		str = `title: ${str}`;
	}

	return str;
}

function printDetailProperties(nodeOrSelection: TextNode): string
{
	let str = DETAIL_PREDICATES.map((predicate) => predicate(nodeOrSelection))
		.filter(Boolean)
		.join(', ')
		.toLocaleLowerCase();

	if (str !== '')
	{
		str = `detail: ${str}`;
	}

	return str;
}

function printModeProperties(nodeOrSelection: TextNode): string
{
	let str = MODE_PREDICATES.map((predicate) => predicate(nodeOrSelection))
		.filter(Boolean)
		.join(', ')
		.toLocaleLowerCase();

	if (str !== '')
	{
		str = `mode: ${str}`;
	}

	return str;
}
