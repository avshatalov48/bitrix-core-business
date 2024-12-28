import { Dom } from 'main.core';
import { Switcher as UISwitcher, type SwitcherOptions } from 'ui.switcher';

/*
Example:

<Switcher
	:is-checked="myBoolData"
	@check="myBoolData = true"
	@uncheck="myBoolData = false"
	:options="{
		size: 'extra-small',
		color: 'green',
	}"
/>
 */

export const Switcher = {
	name: 'Switcher',
	emits: ['check', 'uncheck'],
	props: {
		isChecked: {
			type: Boolean,
			required: true,
		},
		options: {
			/** @type SwitcherOptions */
			type: Object,
			default: {},
		},
	},
	switcher: null,
	mounted()
	{
		this.renderSwitcher();
	},
	watch: {
		isChecked(): void {
			this.switcher.check(this.isChecked, false);
		},
		options(newOptions, oldOptions): void {
			if (this.isOptionsEqual(newOptions, oldOptions))
			{
				return;
			}

			// re-render switcher since options has changed
			this.switcher = null;
			Dom.clean(this.$refs.container);
			this.renderSwitcher();
		},
	},
	methods: {
		renderSwitcher(): void {
			this.switcher = new UISwitcher(
				{
					...this.options,
					checked: this.isChecked,
					handlers: {
						// checked for when the switcher is made off and unchecked for when the switcher is made on
						// it looks like a bug, but I'm not sure
						checked: () => {
							// switch it back until the state is muted and we reactively change it to a new state
							this.switcher.check(this.isChecked, false);
							this.$emit('uncheck');
						},
						unchecked: () => {
							// switch it back until the state is muted and we reactively change it to a new state
							this.switcher.check(this.isChecked, false);
							this.$emit('check');
						},
					},
				},
			);

			this.switcher.renderTo(this.$refs.container);
		},
		isOptionsEqual(newOptions: SwitcherOptions, oldOptions: SwitcherOptions): boolean {
			if (Object.keys(newOptions).length !== Object.keys(oldOptions).length)
			{
				return false;
			}

			for (const [key, value] of Object.entries(newOptions))
			{
				if (!Object.hasOwn(oldOptions, key))
				{
					return false;
				}

				if (value !== oldOptions[key])
				{
					return false;
				}
			}

			for (const [key, value] of Object.entries(oldOptions))
			{
				if (!Object.hasOwn(newOptions, key))
				{
					return false;
				}

				if (value !== newOptions[key])
				{
					return false;
				}
			}

			return true;
		},
	},
	template: '<a ref="container"></a>',
};
