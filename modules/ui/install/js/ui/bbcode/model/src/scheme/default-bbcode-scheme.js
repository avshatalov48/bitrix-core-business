import { Type } from 'main.core';
import { BBCodeScheme } from './bbcode-scheme';
import { BBCodeTagScheme } from './node-schemes/tag-scheme';
import { typeof BBCodeElementNode } from '../nodes/element-node';
import type { BBCodeContentNode } from '../nodes/node';
import type { BBCodeSchemeOptions } from './bbcode-scheme';

export class DefaultBBCodeScheme extends BBCodeScheme
{
	constructor(options: BBCodeSchemeOptions = {})
	{
		super({
			tagSchemes: [
				new BBCodeTagScheme({
					name: ['b', 'u', 'i', 's'],
					group: ['#inline', '#format'],
					allowedChildren: ['#text', '#linebreak', '#inline'],
					canBeEmpty: false,
				}),
				new BBCodeTagScheme({
					name: ['span'],
					group: ['#inline'],
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
				}),
				new BBCodeTagScheme({
					name: ['*'],
					allowedChildren: ['#text', '#linebreak', '#inline', '#inlineBlock'],
					stringify: (node: BBCodeElementNode) => {
						const openingTag: string = node.getOpeningTag();
						const content: string = node.getContent().trim();

						return `${openingTag}${content}`;
					},
					allowedIn: ['list'],
				}),
				new BBCodeTagScheme({
					name: 'table',
					group: ['#block'],
					allowedChildren: ['tr'],
					stringify: BBCodeTagScheme.defaultBlockStringifier,
					allowedIn: ['#root', 'quote', 'spoiler'],
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
					name: 'disk',
					group: ['#inline'],
					void: true,
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
			],
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
