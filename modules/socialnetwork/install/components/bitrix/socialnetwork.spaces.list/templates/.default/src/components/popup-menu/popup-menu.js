// @vue/component
import { BasePopup } from '../popup/base-popup';
import { PopupMenuContent } from './popup-menu-content';

const POPUP_ID = 'socialnetwork-spaces-list-mode-popup';

export const PopupMenu = {
	components: { BasePopup, PopupMenuContent },
	props: {
		bindElement: {
			type: Object,
			required: true,
		},
		context: {
			type: String,
			required: true,
		},
		options: {
			type: Array,
			required: true,
		},
		selectedOption: {
			type: String,
			required: false,
			default: '',
		},
		button: {
			type: Object,
			required: false,
			default: () => {},
		},
		hint: {
			type: String,
			required: false,
			default: '',
		},
	},
	computed: {
		POPUP_ID(): string {
			return `${POPUP_ID}-${this.context}`;
		},
		config(): Object {
			return {
				className: 'ui-test-popup',
				width: 343,
				closeIcon: false,
				closeByEsc: true,
				overlay: true,
				padding: 0,
				animation: 'fading-slide',
				bindElement: this.bindElement,
			};
		},
	},
	emits: ['close', 'changeSelectedOption', 'popupMenuButtonClick'],
	methods: {
		onChangeSelectedOption(newSelectedOption)
		{
			this.$emit('changeSelectedOption', newSelectedOption);
		},
		onPopupMenuButtonClick()
		{
			this.$emit('popupMenuButtonClick');
		},
	},
	template: `
		<BasePopup
			:config="config"
			@close="$emit('close')"
			v-slot="{enableAutoHide, disableAutoHide}"
			:id="POPUP_ID"
		>
			<PopupMenuContent
				:options="options"
				:selectedOption="selectedOption"
				:hint="hint"
				@closePopup="$emit('close')"
				@enableAutoHide="enableAutoHide"
				@disableAutoHide="disableAutoHide"
				@changeSelectedOption="onChangeSelectedOption"
				@popupMenuButtonClick="onPopupMenuButtonClick"
				:button="button"
			/>
		</BasePopup>
	`,
};
