import { Type, Extension } from 'main.core';
import {
	Formatter,
	NodeFormatter,
	type FormatterOptions,
	type UnknownNodeCallbackOptions,
	type BeforeConvertCallbackOptions,
} from 'ui.bbcode.formatter';
import { BBCodeScheme, BBCodeFragmentNode } from 'ui.bbcode.model';
import * as NodeFormatters from './node-formatters';

import 'ui.typography';

export type HtmlFormatterOptions = FormatterOptions & {
	linkSettings?: {
		allowedSchemes?: string,
		defaultScheme?: 'http',
		defaultTarget?: '_self' | '_blank' | '_parent' | '_top',
		shortLink: {
			enabled?: boolean,
			maxLength?: number,
			lastFragmentLength?: number,
		},
		attributes?: {
			[key: string]: string,
		},
	},
	mention: {
		urlTemplate: {
			user: string,
			project: string,
			department: string,
		},
	},
	fileMode: 'disk' | 'file',
	containerMode: 'none' | 'void' | 'collapsed',
};

const globalSettings = Extension.getSettings('ui.bbcode.formatter.html-formatter');

/**
 * @memberOf BX.UI.BBCode.Formatter
 */
export class HtmlFormatter extends Formatter
{
	#linkSettings: HtmlFormatterOptions['linkSettings'];
	#mentionSettings: HtmlFormatterOptions['mention'];
	#fileMode: HtmlFormatterOptions['fileMode'];
	#containerMode: HtmlFormatterOptions['container'];

	constructor(options: HtmlFormatterOptions = {})
	{
		super();

		this.setLinkSettings({
			...globalSettings.linkSettings,
			...options.linkSettings,
		});

		this.setMentionSettings({
			...globalSettings.mention,
			...options.mention,
		});

		this.#fileMode = ['file', 'disk'].includes(options.fileMode) ? options.fileMode : null;

		const defaultFormatters = Object.values(NodeFormatters).map((FormatterClass: NodeFormatter) => {
			return new FormatterClass({ formatter: this });
		});

		this.setContainerMode(options.containerMode);
		this.setNodeFormatters(defaultFormatters);
		this.setNodeFormatters(options.formatters);
	}

	isShortLinkEnabled(): boolean
	{
		const { shortLink } = this.getLinkSettings();

		return (
			Type.isPlainObject(shortLink)
			&& shortLink.enabled === true
			&& Type.isInteger(shortLink.maxLength)
		);
	}

	setLinkSettings(settings: HtmlFormatterOptions['linkSettings'])
	{
		this.#linkSettings = { ...settings };
	}

	getLinkSettings(): HtmlFormatterOptions['linkSettings']
	{
		return this.#linkSettings;
	}

	getFileMode(): HtmlFormatterOptions['fileMode'] | null
	{
		return this.#fileMode;
	}

	setMentionSettings(settings: HtmlFormatterOptions['mention'])
	{
		this.#mentionSettings = { ...settings };
	}

	getMentionSettings(): HtmlFormatterOptions['mention']
	{
		return this.#mentionSettings;
	}

	setContainerMode(mode: HtmlFormatterOptions['containerMode']): void
	{
		this.#containerMode = mode;
	}

	getContainerMode(): HtmlFormatterOptions['container']
	{
		return this.#containerMode;
	}

	isElement(source): boolean
	{
		if (source.nodeType === Node.DOCUMENT_FRAGMENT_NODE)
		{
			return true;
		}

		if (source.nodeType !== Node.ELEMENT_NODE || this.#isVoidElement(source))
		{
			return false;
		}

		return Type.isUndefined(source.dataset.decorator);
	}

	#isVoidElement(source): boolean
	{
		return ['img', 'br', 'hr', 'input'].includes(source.tagName.toLowerCase());
	}

	getDefaultUnknownNodeCallback(options): (UnknownNodeCallbackOptions) => NodeFormatter | null
	{
		return () => {
			return new NodeFormatter({
				name: 'unknown',
				before({ node }: BeforeConvertCallbackOptions): BBCodeFragmentNode {
					const scheme: BBCodeScheme = node.getScheme();

					if (node.isVoid())
					{
						return scheme.createFragment({
							children: [
								scheme.createText(node.getOpeningTag()),
							],
						});
					}

					return scheme.createFragment({
						children: [
							scheme.createText(node.getOpeningTag()),
							...node.getChildren(),
							scheme.createText(node.getClosingTag()),
						],
					});
				},
				convert(): DocumentFragment {
					return document.createDocumentFragment();
				},
			});
		};
	}
}
