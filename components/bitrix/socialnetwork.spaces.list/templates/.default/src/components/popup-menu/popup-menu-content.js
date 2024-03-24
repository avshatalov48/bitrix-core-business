import { PopupMenuOption } from './popup-menu-option';
import { PopupMenuButton } from './popup-menu-button';
// @vue/component
export const PopupMenuContent = {
	name: 'ModePopupContent',
	components: {
		PopupMenuOption,
		PopupMenuButton,
	},
	props: {
		options: {
			type: Array,
			required: true,
		},
		selectedOption: {
			type: String,
			required: false,
			default: '',
		},
		hint: {
			type: String,
			required: false,
			default: '',
		},
		button: {
			type: Object,
			required: false,
			default: () => {},
		},
	},
	emits: ['closePopup', 'changeSelectedOption', 'popupMenuButtonClick'],
	computed: {
		doShowHint(): boolean
		{
			return this.hint.length > 0;
		},
		doShowButton(): boolean
		{
			return this.button?.text?.length > 0;
		},
	},
	methods: {
		onChangeSelectedOption(newSelectedOption)
		{
			this.$emit('closePopup');
			this.$emit('changeSelectedOption', newSelectedOption);
		},
		onPopupMenuButtonClick()
		{
			this.$emit('closePopup');
			this.$emit('popupMenuButtonClick');
		},
	},
	template: `
		<div class="sn-spaces__popup-menu">
			<PopupMenuOption
				v-for="option in options"
				:option="option"
				:key="option.type"
				:isSelected="option.type === this.selectedOption"
				@changeSelectedOption="onChangeSelectedOption"
			/>
			<div v-if="doShowHint" class="sn-spaces__popup-menu_hint">
				{{hint}}
			</div>
			<PopupMenuButton
				v-if="doShowButton"
				:config="button"
				@popupMenuButtonClick="onPopupMenuButtonClick"
			/>
		</div>
	`,
};
