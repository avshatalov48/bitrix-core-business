import { Type } from 'main.core';

import {
	TextNode,
	$createTextNode,
	$isElementNode,
	$isLineBreakNode,
	$isTextNode,
	type LexicalNode,
	type ElementNode,
} from 'ui.lexical.core';

import {
	$createAutoLinkNode,
	$isAutoLinkNode,
	$isLinkNode,
	AutoLinkNode,
	type LinkAttributes,
} from 'ui.lexical.link';

import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';

import type { BBCodeExportConversion, BBCodeExportOutput } from '../../bbcode';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

const URL_REGEX = (
	/((https?:\/\/(www\.)?)|(www\.))[\w#%+.:=@~-]{1,256}\.[\d()A-Za-z]{1,6}\b([\w#%&()+./:=?@[\]~-]*)(?<![%()+.:\]-])/
);

const EMAIL_REGEX = (
	/(([^\s"(),.:;<>@[\\\]]+(\.[^\s"(),.:;<>@[\\\]]+)*)|(".+"))@((\[(?:\d{1,3}\.){3}\d{1,3}])|(([\dA-Za-z-]+\.)+[A-Za-z]{2,}))/
);

const MATCHERS = [
	createLinkMatcherWithRegExp(URL_REGEX, (text: string) => {
		return text.startsWith('http') ? text : `https://${text}`;
	}),
	createLinkMatcherWithRegExp(EMAIL_REGEX, (text: string) => {
		return `mailto:${text}`;
	}),
];

type ChangeHandler = (url: string | null, prevUrl: string | null) => void;

type LinkMatcherResult = {
	attributes?: LinkAttributes;
	index: number;
	length: number;
	text: string;
	url: string;
};

export type LinkMatcher = (text: string) => LinkMatcherResult | null;

export class AutoLinkPlugin extends BasePlugin
{
	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerListeners();
	}

	static getName(): string
	{
		return 'AutoLink';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [AutoLinkNode];
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			autolink: (): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({ name: 'url' }),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: AutoLinkNode,
			}],
			bbcodeMap: {
				autolink: 'url',
			},
		};
	}

	#registerListeners(): void
	{
		const onChange = (url: string | null, prevUrl: string | null) => {};

		this.cleanUpRegister(
			this.getEditor().registerNodeTransform(TextNode, (textNode: TextNode) => {
				const parent = textNode.getParentOrThrow();
				const previous = textNode.getPreviousSibling();
				if ($isAutoLinkNode(parent))
				{
					handleLinkEdit(parent, MATCHERS, onChange);
				}
				else if (!$isLinkNode(parent))
				{
					if (
						textNode.isSimpleText()
						&& (startsWithSeparator(textNode.getTextContent()) || !$isAutoLinkNode(previous))
					)
					{
						handleLinkCreation(textNode, MATCHERS, onChange);
					}

					handleBadNeighbors(textNode, MATCHERS, onChange);
				}
			}),
		);
	}
}

function createLinkMatcherWithRegExp(
	regExp: RegExp,
	urlTransformer: (text: string) => string = (text) => text,
): LinkMatcherResult
{
	return (text: string) => {
		const match = regExp.exec(text);
		if (match === null)
		{
			return null;
		}

		return {
			index: match.index,
			length: match[0].length,
			text: match[0],
			url: urlTransformer(text),
		};
	};
}

function findFirstMatch(text: string, matchers: Array<LinkMatcher>): LinkMatcherResult | null
{
	for (const matcher of matchers)
	{
		const match = matcher(text);
		if (match)
		{
			return match;
		}
	}

	return null;
}

const PUNCTUATION_OR_SPACE = /[\s(),.;[\]]/;

function isSeparator(char: string): boolean
{
	return PUNCTUATION_OR_SPACE.test(char);
}

function endsWithSeparator(textContent: string): boolean
{
	return isSeparator(textContent[textContent.length - 1]);
}

function startsWithSeparator(textContent: string): boolean
{
	return isSeparator(textContent[0]);
}

function startsWithFullStop(textContent: string): boolean
{
	return /^\.[\dA-Za-z]+/.test(textContent);
}

function isPreviousNodeValid(node: LexicalNode): boolean
{
	let previousNode = node.getPreviousSibling();
	if ($isElementNode(previousNode))
	{
		previousNode = previousNode.getLastDescendant();
	}

	return (
		previousNode === null
		|| $isLineBreakNode(previousNode)
		|| ($isTextNode(previousNode) && endsWithSeparator(previousNode.getTextContent()))
	);
}

function isNextNodeValid(node: LexicalNode): boolean
{
	let nextNode = node.getNextSibling();
	if ($isElementNode(nextNode))
	{
		nextNode = nextNode.getFirstDescendant();
	}

	return (
		nextNode === null
		|| $isLineBreakNode(nextNode)
		|| ($isTextNode(nextNode) && startsWithSeparator(nextNode.getTextContent()))
	);
}

function isContentAroundIsValid(matchStart: number, matchEnd: number, text: string, node: TextNode): boolean
{
	const contentBeforeIsValid = matchStart > 0 ? isSeparator(text[matchStart - 1]) : isPreviousNodeValid(node);
	if (!contentBeforeIsValid)
	{
		return false;
	}

	// contentAfterIsValid
	return matchEnd < text.length ? isSeparator(text[matchEnd]) : isNextNodeValid(node);
}

function handleLinkCreation(node: TextNode, matchers: Array<LinkMatcher>, onChange: ChangeHandler): void
{
	const nodeText = node.getTextContent();
	let text = nodeText;
	let invalidMatchEnd = 0;
	let remainingTextNode = node;
	let match: LinkMatcherResult | null = findFirstMatch(text, matchers);
	while (match !== null)
	{
		const matchStart = match.index;
		const matchLength = match.length;
		const matchEnd = matchStart + matchLength;
		const isValid = isContentAroundIsValid(
			invalidMatchEnd + matchStart,
			invalidMatchEnd + matchEnd,
			nodeText,
			node,
		);

		if (isValid)
		{
			let linkTextNode = null;
			if (invalidMatchEnd + matchStart === 0)
			{
				[linkTextNode, remainingTextNode] = remainingTextNode.splitText(
					invalidMatchEnd + matchLength,
				);
			}
			else
			{
				[, linkTextNode, remainingTextNode] = remainingTextNode.splitText(
					invalidMatchEnd + matchStart,
					invalidMatchEnd + matchStart + matchLength,
				);
			}

			const attributes = Type.isPlainObject(match.attributes) ? { ...match.attributes } : {};
			if (!Type.isStringFilled(attributes.target))
			{
				attributes.target = '_blank';
			}

			const linkNode = $createAutoLinkNode(match.url, attributes);
			const textNode = $createTextNode(match.text);
			textNode.setFormat(linkTextNode.getFormat());
			textNode.setDetail(linkTextNode.getDetail());
			linkNode.append(textNode);
			linkTextNode.replace(linkNode);
			onChange(match.url, null);
			invalidMatchEnd = 0;
		}
		else
		{
			invalidMatchEnd += matchEnd;
		}

		text = text.slice(Math.max(0, matchEnd));
		match = findFirstMatch(text, matchers);
	}
}

function handleLinkEdit(linkNode: AutoLinkNode, matchers: Array<LinkMatcher>, onChange: ChangeHandler): void
{
	// Check children are simple text
	const children = linkNode.getChildren();
	const childrenLength = children.length;
	for (let i = 0; i < childrenLength; i++)
	{
		const child = children[i];
		if (!$isTextNode(child) || !child.isSimpleText())
		{
			replaceWithChildren(linkNode);
			onChange(null, linkNode.getURL());

			return;
		}
	}

	// Check text content fully matches
	const text = linkNode.getTextContent();
	const match = findFirstMatch(text, matchers);
	if (match === null || match.text !== text)
	{
		replaceWithChildren(linkNode);
		onChange(null, linkNode.getURL());

		return;
	}

	// Check neighbors
	if (!isPreviousNodeValid(linkNode) || !isNextNodeValid(linkNode))
	{
		replaceWithChildren(linkNode);
		onChange(null, linkNode.getURL());

		return;
	}

	const url = linkNode.getURL();
	if (url !== match.url)
	{
		linkNode.setURL(match.url);
		onChange(match.url, url);
	}

	if (match.attributes)
	{
		const rel = linkNode.getRel();
		if (rel !== match.attributes.rel)
		{
			linkNode.setRel(match.attributes.rel || null);
			onChange(match.attributes.rel || null, rel);
		}

		const target = linkNode.getTarget();
		if (target !== match.attributes.target)
		{
			linkNode.setTarget(match.attributes.target || null);
			onChange(match.attributes.target || null, target);
		}
	}
}

// Bad neighbours are edits in neighbor nodes that make AutoLinks incompatible.
// Given the creation preconditions, these can only be simple text nodes.
function handleBadNeighbors(textNode: TextNode, matchers: Array<LinkMatcher>, onChange: ChangeHandler): void
{
	const previousSibling = textNode.getPreviousSibling();
	const nextSibling = textNode.getNextSibling();
	const text = textNode.getTextContent();

	if ($isAutoLinkNode(previousSibling) && (!startsWithSeparator(text) || startsWithFullStop(text)))
	{
		previousSibling.append(textNode);
		handleLinkEdit(previousSibling, matchers, onChange);
		onChange(null, previousSibling.getURL());
	}

	if ($isAutoLinkNode(nextSibling) && !endsWithSeparator(text))
	{
		replaceWithChildren(nextSibling);
		handleLinkEdit(nextSibling, matchers, onChange);
		onChange(null, nextSibling.getURL());
	}
}

function replaceWithChildren(node: ElementNode): Array<LexicalNode>
{
	const children = node.getChildren();
	const childrenLength = children.length;

	for (let j = childrenLength - 1; j >= 0; j--)
	{
		node.insertAfter(children[j]);
	}

	node.remove();

	return children.map((child) => child.getLatest());
}
