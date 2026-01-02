import { Dom } from 'main.core';
import { HtmlFormatter } from './html-formatter';

export const HtmlFormatterComponent = {
	props: {
		bbcode: {
			type: String,
			default: '',
		},
		/** @type HtmlFormatterOptions */
		options: {
			type: Object,
			default: undefined,
		},
		/** @type FormatterData */
		formatData: {
			type: Object,
			default: () => ({}),
		},
	},
	beforeCreate(): void
	{
		this.htmlFormatter = null;
	},
	mounted(): void
	{
		this.format();
	},
	unmounted(): void
	{
		this.htmlFormatter = null;
	},
	watch: {
		bbcode(): void
		{
			this.format();
		},
		formatData(): void
		{
			this.format();
		},
	},
	methods: {
		format(): void
		{
			const result = this.getHtmlFormatter().format({ source: this.bbcode, data: this.formatData });

			Dom.clean(this.$el);
			Dom.append(result, this.$el);
		},
		getHtmlFormatter(): HtmlFormatter
		{
			this.htmlFormatter ??= new HtmlFormatter(this.options);

			return this.htmlFormatter;
		},
	},
	template: `
		<div class="ui-typography-container"></div>
	`,
};
