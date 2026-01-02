import './loader.css';

export const LoaderSize = Object.freeze({
	S: 'S',
	M: 'M',
});

// @vue/component
export const Loader = {
	name: 'VoteLoader',
	props:
	{
		size: {
			type: String,
			default: LoaderSize.S,
		},
	},
	computed:
	{
		sizeClassName(): string
		{
			return `--size-${this.size.toLowerCase()}`;
		},
	},
	template: `
		<div class="vote-loader__container">
			<div class="vote-loader" :class="sizeClassName"></div>
		</div>
	`,
};
