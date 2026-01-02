/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.System = this.BX.UI.System || {};
(function (exports,ui_system_chip_vue,ui_iconSet_api_vue) {
	'use strict';

	const InputSize = Object.freeze({
	  Lg: 'l',
	  Md: 'm',
	  Sm: 's'
	});
	const InputDesign = Object.freeze({
	  Primary: 'primary',
	  Grey: 'grey',
	  LightGrey: 'light-grey',
	  Disabled: 'disabled',
	  Naked: 'naked'
	});

	// @vue/component
	const BInput = {
	  name: 'BInput',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    Chip: ui_system_chip_vue.Chip
	  },
	  expose: ['blur'],
	  props: {
	    modelValue: {
	      type: String,
	      default: ''
	    },
	    rowsQuantity: {
	      type: Number,
	      default: 1
	    },
	    resize: {
	      type: String,
	      default: 'both',
	      validator: value => ['none', 'both', 'horizontal', 'vertical'].includes(value)
	    },
	    label: {
	      type: String,
	      default: ''
	    },
	    labelInline: {
	      type: Boolean,
	      default: false
	    },
	    placeholder: {
	      type: String,
	      default: ''
	    },
	    error: {
	      type: String,
	      default: ''
	    },
	    size: {
	      type: String,
	      default: InputSize.Lg
	    },
	    design: {
	      type: String,
	      default: InputDesign.Grey
	    },
	    icon: {
	      type: String,
	      default: ''
	    },
	    /**
	     * @type ChipProps[]
	     */
	    chips: {
	      type: Array,
	      default: null
	    },
	    center: {
	      type: Boolean,
	      default: false
	    },
	    withSearch: {
	      type: Boolean,
	      default: false
	    },
	    withClear: {
	      type: Boolean,
	      default: false
	    },
	    dropdown: {
	      type: Boolean,
	      default: false
	    },
	    clickable: {
	      type: Boolean,
	      default: false
	    },
	    stretched: {
	      type: Boolean,
	      default: false
	    },
	    active: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['update:modelValue', 'click', 'focus', 'blur', 'input', 'clear', 'chipClick', 'chipClear'],
	  setup() {
	    return {
	      Outline: ui_iconSet_api_vue.Outline,
	      ChipDesign: ui_system_chip_vue.ChipDesign
	    };
	  },
	  data() {
	    return {
	      focused: false
	    };
	  },
	  computed: {
	    value: {
	      get() {
	        return this.modelValue;
	      },
	      set(value) {
	        this.$emit('update:modelValue', value);
	      }
	    },
	    disabled() {
	      return this.design === InputDesign.Disabled;
	    },
	    chipSize() {
	      return {
	        [InputSize.Lg]: ui_system_chip_vue.ChipSize.Md,
	        [InputSize.Md]: ui_system_chip_vue.ChipSize.Md,
	        [InputSize.Sm]: ui_system_chip_vue.ChipSize.Xs
	      }[this.size];
	    }
	  },
	  mounted() {
	    if (this.active && !this.clickable) {
	      this.focusToInput();
	    }
	  },
	  methods: {
	    focusToInput() {
	      const input = this.$refs.input;
	      if (!input) {
	        return;
	      }
	      input.focus({
	        preventScroll: true
	      });
	      input.setSelectionRange(input.value.length, input.value.length);
	    },
	    handleClick(event) {
	      if (!this.clickable) {
	        this.$refs.input.focus();
	      }
	      this.$emit('click', event);
	    },
	    handleFocus(event) {
	      if (this.clickable) {
	        event.target.blur();
	        return;
	      }
	      this.focused = true;
	      this.$emit('focus', event);
	    },
	    handleBlur(event) {
	      this.focused = false;
	      this.$emit('blur', event);
	    }
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
	`
	};

	var vue = /*#__PURE__*/Object.freeze({
		InputSize: InputSize,
		InputDesign: InputDesign,
		BInput: BInput
	});

	exports.Vue = vue;
	exports.InputSize = InputSize;
	exports.InputDesign = InputDesign;

}((this.BX.UI.System.Input = this.BX.UI.System.Input || {}),BX.UI.System.Chip.Vue,BX.UI.IconSet));
//# sourceMappingURL=input.bundle.js.map
