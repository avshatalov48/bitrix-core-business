import { Loader as BaseLoader } from 'main.loader';

export const Loader = {
	data(): Object
	{
		return {
			loader: null,
		};
	},
	props: {
		config: {
			type: Object,
			required: false,
			default(): Object {
				return {};
			},
		},
	},
	mounted()
	{
		this.getLoaderInstance().show();
	},
	beforeUnmount()
	{
		if (!this.instance)
		{
			return;
		}

		this.destroyLoader();
	},
	methods: {
		getLoaderInstance(): BaseLoader
		{
			if (!this.instance)
			{
				this.instance = new BaseLoader(this.getLoaderConfig());
			}

			return this.instance;
		},
		getDefaultConfig(): Object
		{
			return {
				target: this.$refs.root,
				size: 110,
				color: '#2fc6f6',
				offset: {
					left: '0px',
					top: '0px',
				},
				mode: 'absolute',
			};
		},
		getLoaderConfig(): Object
		{
			const defaultConfig = this.getDefaultConfig();

			return { ...defaultConfig, ...this.config };
		},
		destroyLoader()
		{
			this.instance.destroy();
			this.instance = null;
		},
	},
	template: `
		<div ref="root" style="position: relative">
		</div>
	`,
};
