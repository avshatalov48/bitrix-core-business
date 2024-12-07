import { Dom } from 'main.core';
import { HtmlFormatter } from './html-formatter';

export const HtmlFormatterComponent = {
	props: {
		bbcode: {
			type: String,
			required: false,
			default: '',
		},
	},
	beforeCreate(): void
	{
		this.htmlFormatter = null;
	},
	mounted(): void
	{
		this.format(this.bbcode);
	},
	unmounted(): void
	{
		this.htmlFormatter = null;
	},
	watch: {
		bbcode(newValue): void
		{
			this.format(newValue);
		},
	},
	methods: {
		format(bbcode: string): void
		{
			const result = this.getHtmlFormatter().format({ source: bbcode });
			const container = this.$refs.content;

			Dom.clean(container);
			// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
			container.appendChild(result);
			// container.parentNode.replaceChild(result, container);
		},
		getHtmlFormatter(): HtmlFormatter
		{
			if (this.htmlFormatter !== null)
			{
				return this.htmlFormatter;
			}

			this.htmlFormatter = new HtmlFormatter();

			return this.htmlFormatter;
		},
	},

	template: '<div class="ui-typography-container" ref="content"></div>',
};
