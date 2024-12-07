import { Dom, Type } from 'main.core';
import {
	NodeFormatter,
	type ConvertCallbackOptions,
	type NodeFormatterOptions,
} from 'ui.bbcode.formatter';
import type { HtmlFormatterOptions } from '../../html-formatter';

export class ProjectNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'project',
			convert({ node, formatter }: ConvertCallbackOptions): HTMLAnchorElement | HTMLSpanElement {
				const mentionSettings: HtmlFormatterOptions['mention'] = formatter.getMentionSettings();
				if (Type.isStringFilled(mentionSettings?.urlTemplate?.project))
				{
					const urlTemplate: string = mentionSettings.urlTemplate.project;
					const projectUrl: string = urlTemplate.replaceAll('#group_id#', node.getValue());

					return Dom.create({
						tag: 'a',
						attrs: {
							href: projectUrl,
							className: 'ui-typography-mention',
						},
						dataset: {
							mentionEntityId: 'project',
							mentionId: node.getValue(),
						},
					});
				}

				return Dom.create({
					tag: 'span',
					attrs: {
						className: 'ui-typography-mention',
					},
					dataset: {
						mentionEntityId: 'project',
						mentionId: node.getValue(),
					},
				});
			},
			...options,
		});
	}
}
