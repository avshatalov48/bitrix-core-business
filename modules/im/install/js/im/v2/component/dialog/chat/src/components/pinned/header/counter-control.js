import './css/counter-control.css';

// @vue/component
export const CounterControl = {
	name: 'CounterControl',
	props:
	{
		messagePosition: {
			type: Number,
			required: true,
		},
		totalPinCounter: {
			type: Number,
			required: true,
		},
	},
	emits: ['toggleList'],
	template: `
		<button
			@click="$emit('toggleList')"
			class="bx-im-dialog-chat__pinned_counter_control"
		>
			<span class="bx-im-dialog-chat__pinned_icon-dropdown"></span>
			<span>
				{{ messagePosition }}
				<span class="bx-im-dialog-chat__pinned_counter_control-total">
					/ {{ totalPinCounter }}
				</span>
			</span>
		</button>
	`,
};
