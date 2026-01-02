import { BIcon, Outline } from 'ui.icon-set.api.vue';
import 'ui.icon-set.outline';

import { ChipDesign, ChipSize } from './const';
export * from './const';
export * from './types';

import './chip.css';

// @vue/component
export const Chip = {
	name: 'UiChip',
	components: {
		BIcon,
	},
	props: {
		size: {
			type: String,
			default: ChipSize.Lg,
		},
		design: {
			type: String,
			default: ChipDesign.Outline,
		},
		icon: {
			type: String,
			default: null,
		},
		iconColor: {
			type: String,
			default: null,
		},
		iconBackground: {
			type: String,
			default: null,
		},
		image: {
			/** @type ChipImage */
			type: Object,
			default: null,
		},
		text: {
			type: String,
			default: '',
		},
		rounded: {
			type: Boolean,
			default: false,
		},
		withClear: {
			type: Boolean,
			default: false,
		},
		dropdown: {
			type: Boolean,
			default: false,
		},
		lock: {
			type: Boolean,
			default: false,
		},
		compact: {
			type: Boolean,
			default: true,
		},
		trimmable: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['click', 'clear'],
	setup(): Object
	{
		return {
			Outline,
		};
	},
	methods: {
		handleKeydown(event: KeyboardEvent): void
		{
			if (event.key === 'Enter' && !(event.ctrlKey || event.metaKey))
			{
				this.$emit('click');
			}
		},
	},
	template: `
		<div
			class="ui-chip"
			:class="[
				'--' + design,
				'--' + size,
				{
					'--rounded': rounded,
					'--compact': compact,
					'--trimmable': trimmable,
					'--lock': lock,
					'--with-right-icon': withClear || dropdown,
					'--no-text': text.length === 0,
				},
			]"
			tabindex="0"
			@keydown="handleKeydown"
			@click="$emit('click')"
		>
			<img v-if="image" class="ui-chip-icon --image" :src="image.src" :alt="image.alt">
			<div
				v-if="icon"
				class="ui-chip-icon"
				:class="{ '--with-background': Boolean(iconBackground) }"
				:style="{ '--icon-background': iconBackground }"
			>
				<BIcon :name="icon" :color="iconColor"/>
			</div>
			<div class="ui-chip-text">{{ text }}</div>
			<BIcon v-if="dropdown" class="ui-chip-right-icon" :name="Outline.CHEVRON_DOWN_M"/>
			<BIcon v-if="withClear" class="ui-chip-right-icon" :name="Outline.CROSS_M" @click.stop="$emit('clear')"/>
			<BIcon v-if="lock" class="ui-chip-lock" :name="Outline.LOCK_M"/>
		</div>
	`,
};
