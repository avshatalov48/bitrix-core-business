import { ApplyButton, ButtonColor, CancelButton } from 'ui.buttons';
import { Popup } from 'ui.vue3.components.popup';
import '../../../css/value/value-popup.css';

export const ValuePopup = {
	name: 'ValuePopup',
	components: { Popup },
	emits: ['close', 'apply'],
	provide(): Object {
		return {
			redefineApply: (func: Function) => {
				this.onApply = func;
			},
		};
	},
	data(): Object {
		return {
			onApply: () => {
				this.$emit('apply');
			},
		};
	},
	computed: {
		popupOptions(): PopupOptions {
			return {
				autoHide: true,
				closeEsc: true,
				cacheable: false,
				minWidth: 466,
				padding: 18,
			};
		},
	},
	mounted()
	{
		void this.$nextTick(() => {
			const applyButton = new ApplyButton({
				color: ButtonColor.PRIMARY,
				onclick: () => {
					this.apply();
					this.$emit('close');
				},
			});
			applyButton.renderTo(this.$refs['button-container']);

			const cancelButton = new CancelButton({
				onclick: () => {
					this.$emit('close');
				},
			});
			cancelButton.renderTo(this.$refs['button-container']);
		});
	},
	methods: {
		apply(): void
		{
			this.onApply();
		},
	},
	template: `
		<Popup @close="$emit('close')" :options="popupOptions">
			<slot/>
			<div ref="button-container" class="ui-access-rights-v2-value-popup-buttons"></div>
		</Popup>
	`,
};
