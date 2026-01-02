import { Reaction as JsReaction, ReactionEvent } from './reaction';
import { ReactionName } from './enums/reaction-name';

// @vue/component
export const Reaction = {
	emits: ['animationFinish'],
	props: {
		name: {
			required: true,
			type: String,
			validator: (value: string) => Object.values(ReactionName).includes(value),
		},
		size: {
			type: Number,
			required: false,
			default: 16,
		},
		animate: {
			type: Boolean,
			required: false,
			default: false,
		},
		infiniteAnimate: {
			type: Boolean,
			required: false,
			default: false,
		},
	},
	data(): { reaction: ?JsReaction }
	{
		return {
			reaction: null,
		};
	},
	watch: {
		animate(newValue)
		{
			if (newValue)
			{
				this.reaction?.playAnimation();
			}
			else
			{
				this.reaction?.pauseAnimation();
			}
		},
		infiniteAnimate(newValue)
		{
			this.reaction.setInfiniteAnimate(newValue);
		},
		size(newSize)
		{
			this.reaction?.setSize(newSize);
		},
	},
	mounted()
	{
		this.reaction = new JsReaction({
			name: this.name,
			size: this.size,
			animation: {
				animate: this.animate,
				infinite: this.infiniteAnimate,
			},
		});

		this.reaction.subscribe(ReactionEvent.animationFinish, () => {
			this.$emit(ReactionEvent.animationFinish);
		});

		this.reaction.renderOnNode(this.$el);
	},
	unmounted()
	{
		if (this.reaction)
		{
			this.reaction.destroy();
			this.reaction = null;
		}
	},
	methods: {
		playAnimation()
		{
			this.reaction?.playAnimation();
		},
		pauseAnimation()
		{
			this.reaction?.pauseAnimation();
		},
	},
	template: `
		<span></span>
	`,
};
