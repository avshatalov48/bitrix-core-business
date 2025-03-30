import { Type } from 'main.core';
import { Button as UIButton, ButtonColor, ButtonSize, ButtonState, ButtonIcon } from 'ui.buttons';

export const Button = {
	name: 'UiButton',
	emits: ['click'],
	props: {
		text: {
			type: String,
			default: '',
		},
		size: String,
		state: {
			type: String,
			default: undefined,
			validator(val): boolean
			{
				return Type.isUndefined(val) || Object.values(ButtonState).includes(val);
			},
		},
		id: String,
		color: String,
		round: Boolean,
		icon: String,
		noCaps: Boolean,
		disabled: Boolean,
		clocking: Boolean,
		waiting: Boolean,
		dataset: Object,
		buttonClass: String,
	},
	created(): void
	{
		this.button = new UIButton({
			id: this.id,
			text: this.text,
			size: this.size,
			color: this.color,
			round: this.round,
			icon: this.icon,
			noCaps: this.noCaps,
			onclick: () => {
				this.$emit('click');
			},
			dataset: this.dataset,
			className: this.buttonClass,
		});
	},
	mounted(): void
	{
		const button = this.button?.render();

		const slot = this.$refs.button.firstElementChild;
		if (slot)
		{
			button.append(slot);
		}

		this.$refs.button.replaceWith(button);
	},
	watch: {
		text: {
			handler(text): void
			{
				this.button?.setText(text);
			},
		},
		size: {
			handler(size): void
			{
				this.button?.setSize(size);
			},
		},
		color: {
			handler(color): void
			{
				this.button?.setColor(color);
			},
		},
		state: {
			handler(state): void
			{
				this.button?.setState(state);
			},
		},
		icon: {
			handler(icon): void
			{
				this.button?.setIcon(icon);
			},
		},
		disabled: {
			handler(disabled): void
			{
				this.button?.setDisabled(Boolean(disabled));
			},
			immediate: true,
			flush: 'sync',
		},
		waiting: {
			handler(waiting): void
			{
				if (waiting !== this.button?.isWaiting())
				{
					this.button?.setWaiting(waiting);
				}
			},
			immediate: true,
		},
		clocking: {
			handler(clocking): void
			{
				if (clocking !== this.button?.isClocking())
				{
					this.button?.setClocking(clocking);
				}
			},
			immediate: true,
		},
	},
	template: `
		<span>
			<button ref="button">
				<slot></slot>
			</button>
		</span>
	`,
};

export { ButtonColor, ButtonSize, ButtonIcon };
