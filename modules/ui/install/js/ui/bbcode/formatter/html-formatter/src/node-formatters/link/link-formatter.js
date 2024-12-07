import { Dom, Type } from 'main.core';
import {
	NodeFormatter,
	type NodeFormatterOptions,
	type AfterCallbackOptions,
	type ConvertCallbackOptions,
	type ValidateCallbackOptions,
} from 'ui.bbcode.formatter';
import type { BBCodeContentNode } from 'ui.bbcode.model';
import {
	BBCodeElementNode,
	BBCodeRootNode,
	BBCodeScheme,
} from 'ui.bbcode.model';

export class LinkNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		super({
			name: 'url',
			validate({ node }: ValidateCallbackOptions): boolean {
				const nodeValue: string = LinkNodeFormatter.fetchNodeValue(node);

				return !LinkNodeFormatter.startsWithJavascriptScheme(nodeValue);
			},
			before({ node, formatter }: AfterCallbackOptions): BBCodeElementNode {
				if (formatter.isShortLinkEnabled())
				{
					const isIncludeImg: boolean = node.getChildren().some((node: BBCodeContentNode) => {
						return node.getName() === 'img';
					});

					if (!isIncludeImg)
					{
						const scheme: BBCodeScheme = node.getScheme();
						const nodeContentLength: number = node.getPlainTextLength();
						const { shortLink } = formatter.getLinkSettings();
						if (nodeContentLength > shortLink.maxLength)
						{
							const sourceHref: string = LinkNodeFormatter.fetchNodeValue(node);

							const nodeRoot: BBCodeRootNode = scheme.createRoot({
								children: node.getChildren(),
							});
							const [left, right] = nodeRoot.split({
								offset: shortLink.maxLength - shortLink.lastFragmentLength,
							});
							const sourceRightFragmentLength: number = right.getPlainTextLength();
							const newLink: BBCodeElementNode = node.clone();
							newLink.setValue(sourceHref);

							if (sourceRightFragmentLength > shortLink.lastFragmentLength)
							{
								newLink.appendChild(
									...left.getChildren(),
									scheme.createText('...'),
								);

								const [, lastFragment] = right.split({
									offset: sourceRightFragmentLength - shortLink.lastFragmentLength,
								});

								newLink.appendChild(...lastFragment.getChildren());

								return newLink;
							}

							newLink.setChildren([
								...left.getChildren(),
								scheme.createText('...'),
								...right.getChildren(),
							]);

							return newLink;
						}
					}
				}

				return node;
			},
			convert({ node, formatter }: ConvertCallbackOptions): HTMLLinkElement | HTMLUnknownElement {
				const sourceHref: string = (() => {
					const value: ?string = node.getValue();
					if (Type.isStringFilled(value))
					{
						return value;
					}

					return node.getContent();
				})();
				const nodeAttributes: {[key: string]: string} = node.getAttributes();
				const { defaultTarget = '_blank', attributes } = formatter.getLinkSettings();

				return Dom.create({
					tag: 'a',
					attrs: {
						...nodeAttributes,
						...attributes,
						href: sourceHref,
						target: defaultTarget,
						className: 'ui-typography-link',
					},
				});
			},
			...options,
		});
	}

	static fetchNodeValue(node: BBCodeElementNode): string
	{
		const value: ?string = node.getValue();
		if (Type.isStringFilled(value))
		{
			return value;
		}

		return node.toPlainText();
	}

	static startsWithJavascriptScheme(sourceHref: string): boolean
	{
		if (Type.isStringFilled(sourceHref))
		{
			// eslint-disable-next-line no-control-regex
			const regexp = /^[\u0000-\u001F ]*j[\t\n\r]*a[\t\n\r]*v[\t\n\r]*a[\t\n\r]*s[\t\n\r]*c[\t\n\r]*r[\t\n\r]*i[\t\n\r]*p[\t\n\r]*t[\t\n\r]*:/i;

			return regexp.test(sourceHref);
		}

		return false;
	}
}
