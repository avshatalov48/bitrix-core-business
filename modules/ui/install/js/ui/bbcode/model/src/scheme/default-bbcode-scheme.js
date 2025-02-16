import { Type } from 'main.core';
import type { BBCodeToStringOptions } from '../nodes/root-node';
import { BBCodeScheme } from './bbcode-scheme';
import { BBCodeTagScheme } from './node-schemes/tag-scheme';
import { type BBCodeContentNode, BBCodeNode } from '../nodes/node';
import { typeof BBCodeElementNode } from '../nodes/element-node';
import type { BBCodeSchemeOptions } from './bbcode-scheme';

export type DefaultBBCodeSchemeOptions = BBCodeSchemeOptions & {
	fileTag: 'disk' | 'file' | 'none',
};

export class DefaultBBCodeScheme extends BBCodeScheme
{
	constructor(options: DefaultBBCodeSchemeOptions = {})
	{
		const tagSchemes = [
			new BBCodeTagScheme({
				name: ['b', 'u', 'i', 's'],
				group: ['#inline', '#format'],
				allowedChildren: ['#text', '#linebreak', '#inline'],
				canBeEmpty: false,
			}),
			new BBCodeTagScheme({
				name: ['img'],
				group: ['#inlineBlock'],
				allowedChildren: ['#text'],
				canBeEmpty: false,
			}),
			new BBCodeTagScheme({
				name: ['url'],
				group: ['#inline'],
				allowedChildren: ['#text', '#format', 'img'],
				canBeEmpty: false,
				stringify(node: BBCodeElementNode): BBCodeElementNode {
					const openingTag = node.getOpeningTag();
					const closingTag = node.getClosingTag();
					const content = node.getContent();

					return `${openingTag}${content}${closingTag}`;
				},
			}),
			new BBCodeTagScheme({
				name: 'p',
				group: ['#block'],
				allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock'],
				stringify: BBCodeTagScheme.defaultBlockStringifier,
				allowedIn: ['#root', '#shadowRoot'],
			}),
			new BBCodeTagScheme({
				name: 'list',
				group: ['#block'],
				allowedChildren: ['*'],
				stringify: BBCodeTagScheme.defaultBlockStringifier,
				allowedIn: ['#root', '#shadowRoot'],
				canBeEmpty: false,
				onNotAllowedChildren: ({ node, children }): BBCodeElementNode => {
					const notAllowedChildren: Set<string> = new Set(['#tab', '#linebreak']);
					const bePropagated: Array<BBCodeContentNode> = [];
					children.forEach((child: BBCodeContentNode) => {
						if (
							notAllowedChildren.has(child.getName())
							|| (
								child.getName() === '#text'
								&& /^\s+$/.test(child.getContent())
							)
						)
						{
							child.remove();
						}
						else
						{
							bePropagated.push(child);
						}
					});

					node.propagateChild(...bePropagated);
				},
			}),
			new BBCodeTagScheme({
				name: ['*'],
				allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock'],
				stringify: (node: BBCodeElementNode, scheme: BBCodeScheme, toStringOptions: BBCodeToStringOptions) => {
					const openingTag: string = node.getOpeningTag();
					const content: string = node.getContent(toStringOptions).trim();

					return `${openingTag}${content}`;
				},
				allowedIn: ['list'],
				onNotAllowedChildren: ({ node, children }): BBCodeElementNode => {
					const bePropagated: Array<BBCodeContentNode> = [];
					children.forEach((child: BBCodeContentNode) => {
						if (child.getName() === '#tab')
						{
							child.remove();
						}
						else
						{
							bePropagated.push(child);
						}
					});

					node.propagateChild(...bePropagated);
				},
			}),
			new BBCodeTagScheme({
				name: 'table',
				group: ['#block'],
				allowedChildren: ['tr'],
				stringify: BBCodeTagScheme.defaultBlockStringifier,
				allowedIn: ['#root', 'td', 'th', 'quote', 'spoiler'],
				canBeEmpty: false,
			}),
			new BBCodeTagScheme({
				name: 'tr',
				allowedChildren: ['th', 'td'],
				allowedIn: ['table'],
				canBeEmpty: false,
			}),
			new BBCodeTagScheme({
				name: ['th', 'td'],
				group: ['#shadowRoot'],
				allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock', '#block'],
				allowedIn: ['tr'],
			}),
			new BBCodeTagScheme({
				name: 'quote',
				group: ['#block', '#shadowRoot'],
				allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock', '#block'],
				allowedIn: ['#root', '#shadowRoot'],
			}),
			new BBCodeTagScheme({
				name: 'code',
				group: ['#block'],
				stringify: BBCodeTagScheme.defaultBlockStringifier,
				allowedChildren: ['#text', '#linebreak', '#tab'],
				allowedIn: ['#root', '#shadowRoot'],
				convertChild: (child: BBCodeContentNode, scheme: BBCodeScheme, toStringOptions: BBCodeToStringOptions): BBCodeContentNode => {
					if (['#linebreak', '#tab', '#text'].includes(child.getName()))
					{
						return child;
					}

					return scheme.createText(child.toString(toStringOptions));
				},
			}),
			new BBCodeTagScheme({
				name: 'video',
				group: ['#inlineBlock'],
				allowedChildren: ['#text'],
				allowedIn: ['#root', '#shadowRoot', 'p'],
				canBeEmpty: false,
			}),
			new BBCodeTagScheme({
				name: 'spoiler',
				group: ['#block', '#shadowRoot'],
				allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock', '#block'],
				allowedIn: ['#root', '#shadowRoot'],
			}),
			new BBCodeTagScheme({
				name: ['user', 'project', 'department'],
				group: ['#inline', '#mention'],
				allowedChildren: ['#text', '#format'],
				canBeEmpty: false,
			}),
			new BBCodeTagScheme({
				name: ['#root'],
			}),
			new BBCodeTagScheme({
				name: ['#fragment'],
			}),
			new BBCodeTagScheme({
				name: ['#text'],
			}),
			new BBCodeTagScheme({
				name: ['#linebreak'],
			}),
			new BBCodeTagScheme({
				name: ['#tab'],
				stringify: () => {
					return '';
				},
			}),
		];

		if (options?.fileTag !== 'none')
		{
			tagSchemes.push(
				new BBCodeTagScheme({
					name: options?.fileTag === 'file' ? 'file' : 'disk',
					group: ['#inline'],
					void: true,
				}),
			);
		}

		super({
			tagSchemes,
			outputTagCase: BBCodeScheme.Case.LOWER,
			unresolvedNodesHoisting: true,
		});

		if (Type.isPlainObject(options))
		{
			this.setTagSchemes(options.tagSchemes);
			this.setOutputTagCase(options.outputTagCase);
			this.setUnresolvedNodesHoisting(options.unresolvedNodesHoisting);
		}
	}
}
