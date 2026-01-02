import { Line as skeleton } from 'ui.system.skeleton';

// @vue/component
export const BLine = {
	props: {
		width: {
			type: Number,
			default: undefined,
		},
		height: {
			type: Number,
			default: undefined,
		},
		radius: {
			type: Number,
			default: undefined,
		},
	},
	data(): Object
	{
		return {
			isMounted: false,
		};
	},
	mounted(): void
	{
		this.$refs.line.after(this.render());

		this.isMounted = true;
	},
	updated(): void
	{
		this.render();
	},
	methods: {
		render(): HTMLElement
		{
			const line = skeleton(this.width, this.height, this.radius);
			this.line?.replaceWith(line);
			this.line = line;

			return line;
		},
	},
	template: `
		<div v-if="!isMounted" ref="line"></div>
	`,
};
