import './css/textarea.css';

// @vue/component
export const TextareaInput = {
	name: 'TextareaInput',
	props:
	{
		value: {
			type: String,
			default: '',
		},
		placeholder: {
			type: String,
			required: true,
		},
		border: {
			type: Boolean,
			required: false,
			default: true,
		},
		rounded: {
			type: Boolean,
			required: false,
			default: true,
		},
	},
	emits: ['input'],
	computed:
	{
		containerClasses(): { [className: string]: boolean }
		{
			return { '--no-border': !this.border, '--rounded': this.rounded };
		},
	},
	methods:
	{
		onInput(event: Event)
		{
			this.$emit('input', event.target.value);
		},
	},
	template: `
		<div class="bx-im-content-create-chat__textarea_container" :class="containerClasses">
			<div class="ui-ctl ui-ctl-textarea ui-ctl-w100 ui-ctl-no-resize">
				<textarea
					:value="value"
					:placeholder="placeholder"
					@input="onInput"
					class="bx-im-content-create-chat__textarea ui-ctl-element"
				></textarea>
			</div>
		</div>
	`,
};
