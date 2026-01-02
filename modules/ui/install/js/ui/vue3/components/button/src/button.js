import { Type, Dom } from 'main.core';
import {
	Button as UIButton,
	ButtonColor, ButtonSize,
	ButtonState, ButtonIcon,
	AirButtonStyle,
	ButtonCounterColor,
	ButtonTag,
} from 'ui.buttons';
import { Set as IconSet, Outline } from 'ui.icon-set.api.core';
import type { BitrixVueComponentProps } from 'ui.vue3';

const allIcons = new Set([ButtonIcon, IconSet, Outline].flatMap((it) => Object.values(it)));
const iconValidator = (val): boolean => Type.isNil(val) || allIcons.has(val);

// @vue/component
export const Button: BitrixVueComponentProps | { button: ?UIButton } = {
	name: 'UiButton',
	props: {
		class: {
			type: String,
			default: undefined,
		},
		text: {
			type: String,
			default: '',
		},
		link: {
			type: String,
			default: '',
		},
		tag: {
			type: String,
			default: '',
		},
		size: {
			type: String,
			default: undefined,
			validator(val): boolean
			{
				return Type.isNil(val) || Object.values(ButtonSize).includes(val);
			},
		},
		state: {
			type: String,
			default: undefined,
			validator(val): boolean
			{
				return Type.isNil(val) || Object.values(ButtonState).includes(val);
			},
		},
		style: {
			type: String,
			required: false,
			default: null,
			validator(val): boolean
			{
				return Type.isNil(val) || Object.values(AirButtonStyle).includes(val);
			},
		},
		noCaps: {
			type: Boolean,
			default: true,
		},
		disabled: Boolean,
		loading: Boolean,
		dropdown: Boolean,
		wide: Boolean,
		collapsed: Boolean,
		id: {
			type: String,
			default: '',
		},
		type: {
			type: String,
			required: false,
			default: 'button',
			validator: (type) => ['button', 'submit', 'reset'].includes(type),
		},
		dataset: {
			type: Object,
			default: () => ({}),
		},
		leftIcon: {
			type: String,
			default: null,
			validator: iconValidator,
		},
		rightIcon: {
			type: String,
			default: null,
			validator: iconValidator,
		},
		collapsedIcon: {
			type: String,
			default: null,
			validator: iconValidator,
		},
		leftCounterColor: {
			type: String,
			required: false,
			default: null,
		},
		rightCounterColor: {
			type: String,
			required: false,
			default: null,
		},
		leftCounterValue: {
			type: Number,
			default: 0,
		},
		rightCounterValue: {
			type: Number,
			default: 0,
		},
		removeLeftCorners: {
			type: Boolean,
			default: false,
		},
		removeRightCorners: {
			type: Boolean,
			default: false,
		},
		shimmer: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['click'],
	data(): Object
	{
		return {
			isMounted: false,
		};
	},
	watch: {
		text(text): void
		{
			this.button?.setText(text);
		},
		size(size): void
		{
			this.button?.setSize(size);
		},
		state(state): void
		{
			this.button?.setState(state);
		},
		icon(icon): void
		{
			this.button?.setIcon(icon);
		},
		collapsedIcon(collapsedIcon: boolean): void
		{
			this.button?.setCollapsedIcon(collapsedIcon);
		},
		disabled(disabled): void
		{
			this.button?.setDisabled(!disabled);
			this.button?.setDisabled(Boolean(disabled));
		},
		loading: {
			handler(loading): void
			{
				if (loading !== this.button?.isWaiting())
				{
					this.button?.setWaiting(loading);
				}
			},
			immediate: true,
		},
		leftIcon(icon): void
		{
			this.button?.setIcon(icon, 'left');
		},
		rightIcon(icon): void
		{
			this.button?.setIcon(icon, 'right');
		},
		leftCounterColor(color): void
		{
			this.button.getLeftCounter()?.setColor(color);
		},
		rightCounterColor(color): void
		{
			this.button.getRightCounter()?.setColor(color);
		},
		leftCounterValue(value): void
		{
			if (value === 0)
			{
				this.button.setLeftCounter(null);
			}
			else if (value > 0 && this.button.getLeftCounter())
			{
				this.button.getLeftCounter().setValue(value);
			}
			else if (value > 0)
			{
				this.button.setLeftCounter({
					value,
					color: this.leftCounterColor,
				});
			}
		},
		rightCounterValue(value: number): void
		{
			if (value === 0)
			{
				this.button.setRightCounter(null);
			}
			else if (value > 0 && this.button.getRightCounter())
			{
				this.button.getRightCounter().setValue(value);
			}
			else if (value > 0)
			{
				this.button.setRightCounter({
					value,
					color: this.leftCounterColor,
				});
			}
		},
		wide(wide: boolean): void
		{
			this.button.setWide(wide);
		},
		style(style: string): void
		{
			this.button.setStyle(style);
		},
		dropdown(dropdown: boolean): void
		{
			this.button.setDropdown(dropdown);
		},
		noCaps(noCaps: boolean): void
		{
			this.button.setNoCaps(noCaps);
		},
		collapsed(collapsed: boolean): void
		{
			this.button.setCollapsed(collapsed);
		},
		removeLeftCorners(remove: boolean): void
		{
			this.button?.setLeftCorners(remove === false);
		},
		removeRightCorners(remove: boolean): void
		{
			this.button?.setRightCorners(remove === false);
		},
		shimmer(shimmer: boolean): void
		{
			if (shimmer)
			{
				this.button?.startShimmer();
			}
			else
			{
				this.button?.stopShimmer();
			}
		},
		type(type): void
		{
			Dom.attr(this.button?.getContainer(), 'type', type);
		},
	},
	created(): void
	{
		const button = new UIButton({
			id: this.id,
			className: this.class,
			text: this.text,
			link: this.link,
			tag: this.tag,
			size: this.size,
			useAirDesign: true,
			noCaps: this.noCaps,
			collapsedIcon: this.collapsedIcon,
			onclick: () => {
				this.$emit('click');
			},
			dataset: this.dataset,
			dropdown: this.dropdown,
			disabled: this.disabled,
			style: this.style,
			wide: this.wide,
			removeLeftCorners: this.removeLeftCorners,
			removeRightCorners: this.removeRightCorners,
		});

		if (this.collapsed)
		{
			button.setCollapsed(true);
		}

		if (this.leftIcon)
		{
			button.setIcon(this.leftIcon, 'left');
		}

		if (this.rightIcon)
		{
			button.setIcon(this.leftIcon, 'right');
		}

		if (this.leftCounterValue)
		{
			button.setLeftCounter({
				value: this.leftCounterValue,
				color: this.leftCounterColor,
			});
		}
		else
		{
			button.setLeftCounter(null);
		}

		if (this.rightCounterValue)
		{
			button.setRightCounter({
				value: this.rightCounterValue,
				color: this.rightCounterColor,
			});
		}

		if (this.shimmer)
		{
			button.startShimmer();
		}

		this.button = button;
	},
	mounted(): void
	{
		const button = this.button?.render();

		Dom.attr(button, 'type', this.type);

		this.$refs.button.after(button);

		this.isMounted = true;
	},
	unmounted(): void
	{
		this.button?.getContainer()?.remove();
	},
	template: `
		<button v-if="!isMounted" ref="button"></button>
	`,
};

export { ButtonColor, ButtonSize, ButtonIcon, AirButtonStyle, ButtonCounterColor, ButtonState, ButtonTag };
