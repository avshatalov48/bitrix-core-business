import { Loader as UILoader } from 'ui.loader';

type LoaderOptions = {
	target: HTMLElement,
	type: string,
	size: string,
}

//TODO: move to ui

// @vue/component
export const Loader = {
	props: {
		options: {
			type: Object,
			default: null,
		},
	},
	mounted(): void
	{
		this.loader = new UILoader(this.getOptions());
		this.loader.render();
		this.loader.show();
	},
	beforeUnmount(): void
	{
		this.loader?.hide?.();
		this.loader = null;
	},
	methods: {
		getOptions(): LoaderOptions
		{
			return { ...this.getDefaultOptions(), ...this.options };
		},
		getDefaultOptions(): LoaderOptions
		{
			return {
				target: this.$refs.loader,
				type: 'BULLET',
				size: 'xl',
			};
		},
	},
	template: `
		<div class="socialnetwork-collab-converter-wizard-loader-container" ref="loader"></div>
	`,
};
