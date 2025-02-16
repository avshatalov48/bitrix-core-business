import './css/rich-menu-item.css';
import { Hint } from 'ui.vue3.components.hint';

export const RichMenuItemIcon = Object.freeze({
	check: 'check',
	copy: 'copy',
	'opened-eye': 'opened-eye',
	pencil: 'pencil',
	'red-lock': 'red-lock',
	role: 'role',
	settings: 'settings',
	'trash-bin': 'trash-bin',
});

// @vue/component
export const RichMenuItem = {
	name: 'RichMenuItem',
	components: { Hint },
	props:
		{
			icon: {
				type: String,
				required: false,
				default: '',
				validator(value: string): boolean {
					return value === '' || Object.keys(RichMenuItemIcon).includes(value);
				},
			},
			title: {
				type: String,
				required: true,
			},
			subtitle: {
				type: String,
				required: false,
				default: '',
			},
			hint: {
				type: String,
				required: false,
				default: '',
			},
			disabled: {
				type: Boolean,
				required: false,
				default: false,
			},
			counter: {
				type: Number,
				required: false,
				default: 0,
			},
		},
	computed:
		{
			formattedCounter(): string
			{
				if (this.counter === 0)
				{
					return '';
				}

				return this.counter > 99 ? '99+' : String(this.counter);
			},
		},
	template: `
		<div class="ui-rich-menu-item__container" :class="{'--disabled': disabled}">
			<div class="ui-rich-menu-item__content" :class="{'--with-icon': !!icon}">
				<div v-if="icon" class="ui-rich-menu-item__icon" :class="'--' + icon"></div>
				<div class="ui-rich-menu-item__text-content" :class="{'--with-subtitle': !!subtitle}">
					<div class="ui-rich-menu-item__title">
						<div class="ui-rich-menu-item__title_text">{{ title }}</div>
						<slot name="after-title"></slot>
						<div v-if="counter" class="ui-rich-menu-item__title_counter">{{ formattedCounter }}</div>
					</div>
					<div v-if="subtitle" :title="subtitle" class="ui-rich-menu-item__subtitle">{{ subtitle }}</div>
					<slot name="below-content"></slot>
				</div>
				<Hint v-if="hint" :text="hint"/>
			</div>
		</div>
	`,
};
