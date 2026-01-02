import { Chip, ChipDesign, ChipSize } from 'ui.system.chip.vue';
import { BIcon, Outline } from 'ui.icon-set.api.vue';
import 'ui.icon-set.outline';

import { InputSize, InputDesign } from './const';
export { InputSize, InputDesign };

import './input.css';

// @vue/component
export const BInput = {
	name: 'BInput',
	components: {
		BIcon,
		Chip,
	},
	expose: ['blur'],
	props: {
		modelValue: {
			type: String,
			default: '',
		},
		rowsQuantity: {
			type: Number,
			default: 1,
		},
		resize: {
			type: String,
			default: 'both',
			validator: (value) => ['none', 'both', 'horizontal', 'vertical'].includes(value),
		},
		label: {
			type: String,
			default: '',
		},
		labelInline: {
			type: Boolean,
			default: false,
		},
		placeholder: {
			type: String,
			default: '',
		},
		error: {
			type: String,
			default: '',
		},
		size: {
			type: String,
			default: InputSize.Lg,
		},
		design: {
			type: String,
			default: InputDesign.Grey,
		},
		icon: {
			type: String,
			default: '',
		},
		/**
		 * @type ChipProps[]
		 */
		chips: {
			type: Array,
			default: null,
		},
		center: {
			type: Boolean,
			default: false,
		},
		withSearch: {
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
		clickable: {
			type: Boolean,
			default: false,
		},
		stretched: {
			type: Boolean,
			default: false,
		},
		active: {
			type: Boolean,
			default: false,
		},
	},
	emits: [
		'update:modelValue',
		'click',
		'focus',
		'blur',
		'input',
		'clear',
		'chipClick',
		'chipClear',
	],
	setup(): Object
	{
		return {
			Outline,
			ChipDesign,
		};
	},
	data(): Object
	{
		return {
			focused: false,
		};
	},
	computed: {
		value: {
			get(): string
			{
				return this.modelValue;
			},
			set(value: string): void
			{
				this.$emit('update:modelValue', value);
			},
		},
		disabled(): boolean
		{
			return this.design === InputDesign.Disabled;
		},
		chipSize(): string
		{
			return {
				[InputSize.Lg]: ChipSize.Md,
				[InputSize.Md]: ChipSize.Md,
				[InputSize.Sm]: ChipSize.Xs,
			}[this.size];
		},
	},
	mounted(): void
	{
		if (this.active && !this.clickable)
		{
			this.focusToInput();
		}
	},
	methods: {
		focusToInput(): void
		{
			const input = this.$refs.input;
			if (!input)
			{
				return;
			}

			input.focus({ preventScroll: true });
			input.setSelectionRange(input.value.length, input.value.length);
		},
		handleClick(event: MouseEvent): void
		{
			if (!this.clickable)
			{
				this.$refs.input.focus();
			}

			this.$emit('click', event);
		},
		handleFocus(event: FocusEvent): void
		{
			if (this.clickable)
			{
				event.target.blur();

				return;
			}

			this.focused = true;
			this.$emit('focus', event);
		},
		handleBlur(event: FocusEvent): void
		{
			this.focused = false;
			this.$emit('blur', event);
		},
	},
	template: `
		<div
			class="ui-system-input"
			:class="[
				'--' + design,
				'--' + size,
				{
					'--center': center,
					'--with-chips': chips?.length > 0,
					'--clickable': clickable,
					'--stretched': stretched,
					'--active': active || focused,
					'--error': error && !disabled,
				},
			]">
			<div v-if="label" class="ui-system-input-label" :class="{ '--inline': labelInline }">{{ label }}</div>
			<div class="ui-system-input-container" ref="inputContainer" @click="handleClick">
				<div v-for="chip in chips" class="ui-system-input-chip">
					<Chip
						v-bind="chip"
						:design="disabled ? ChipDesign.Disabled : chip.design"
						:size="chipSize"
						@click="$emit('chipClick', chip)"
						@clear="$emit('chipClear', chip)"
					/>
				</div>
				<BIcon v-if="icon" class="ui-system-input-icon" :name="icon"/>
				<textarea
					v-if="rowsQuantity > 1"
					v-model="value"
					class="ui-system-input-value --multi"
					:style="{ resize }"
					:placeholder="placeholder"
					:disabled="disabled"
					:rows="rowsQuantity"
					ref="input"
					@focus="handleFocus"
					@blur="handleBlur"
					@input="$emit('input', $event)"
				/>
				<input
					v-else
					v-model="value"
					class="ui-system-input-value"
					:style="{ '--placeholder-length': placeholder.length + 'ch' }"
					:placeholder="placeholder"
					:disabled="disabled"
					ref="input"
					@focus="handleFocus"
					@blur="handleBlur"
					@input="$emit('input', $event)"
				/>
				<BIcon v-if="withSearch" class="ui-system-input-cross" :name="Outline.SEARCH"/>
				<BIcon v-if="withClear" class="ui-system-input-cross" :name="Outline.CROSS_L" @click.stop="$emit('clear')"/>
				<BIcon v-if="dropdown" class="ui-system-input-dropdown" :name="Outline.CHEVRON_DOWN_L"/>
			</div>
			<div v-if="error?.trim() && !disabled" class="ui-system-input-label --error">{{ error }}</div>
		</div>
	`,
};
