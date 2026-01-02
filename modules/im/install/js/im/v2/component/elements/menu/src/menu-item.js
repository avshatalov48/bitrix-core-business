import './css/menu-item.css';

export const MenuItemIcon = {
	chat: 'chat',
	channel: 'channel',
	collab: 'collab',
	conference: 'conference',
	upload: 'upload',
	file: 'file',
	task: 'task',
	meeting: 'meeting',
	summary: 'summary',
	vote: 'vote',
	aiText: 'ai-text',
	aiImage: 'ai-image',
	copilot: 'copilot',
	calendarSlot: 'calendar-slot',
	documentSign: 'document-sign',
	b24: 'b24',
};

// @vue/component
export const MenuItem = {
	name: 'MenuItem',
	props:
	{
		icon: {
			type: String,
			required: false,
			default: '',
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
		withBottomBorder: {
			type: Boolean,
			required: false,
			default: false,
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
		containerClasses(): Record<string, boolean>
		{
			return {
				'--disabled': this.disabled,
				'--bottom-border': this.withBottomBorder,
			};
		},
	},
	template: `
		<div class="bx-im-menu-item__container" :class="containerClasses">
			<div class="bx-im-menu-item__content" :class="{'--with-icon': !!icon}">
				<div v-if="icon" class="bx-im-menu_item__icon" :class="'--' + icon"></div>
				<div class="bx-im-menu-item__text-content" :class="{'--with-subtitle': !!subtitle}">
					<div class="bx-im-menu-item__title">
						<div class="bx-im-menu-item__title_text">{{ title }}</div>
						<slot name="after-title"></slot>
						<div v-if="counter" class="bx-im-menu-item__title_counter">{{ formattedCounter }}</div>
					</div>
					<div v-if="subtitle" :title="subtitle" class="bx-im-menu-item__subtitle">{{ subtitle }}</div>
					<slot name="below-content"></slot>
				</div>
			</div>
			<slot name="after-content"></slot>
		</div>
	`,
};
