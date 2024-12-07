import { Dom, Type } from 'main.core';
import {
	NodeFormatter,
	type ConvertCallbackOptions,
	type NodeFormatterOptions,
} from 'ui.bbcode.formatter';
import type { HtmlFormatterOptions } from '../../html-formatter';

export class DepartmentNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'department',
			convert({ node, formatter }: ConvertCallbackOptions): HTMLAnchorElement | HTMLSpanElement {
				const mentionSettings: HtmlFormatterOptions['mention'] = formatter.getMentionSettings();
				if (Type.isStringFilled(mentionSettings?.urlTemplate?.department))
				{
					const urlTemplate: string = mentionSettings.urlTemplate.department;
					const departmentUrl: string = urlTemplate.replaceAll('#ID#', node.getValue());

					return Dom.create({
						tag: 'a',
						attrs: {
							href: departmentUrl,
							className: 'ui-typography-mention',
						},
						dataset: {
							mentionEntityId: 'department',
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
						mentionEntityId: 'department',
						mentionId: node.getValue(),
					},
				});
			},
			...options,
		});
	}
}
