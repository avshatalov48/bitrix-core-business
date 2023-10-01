import { Dom } from 'main.core';
import Keyboard from '../mixins/keyboard';

export default
{
	mixins: [Keyboard],
	props: {
		name: {
			type: String,
			required: true,
		},
		type: {
			type: Number,
			required: true,
		},
		value: {
			type: String,
			required: false,
			default: '',
		},
		isEditable: {
			type: Boolean,
			required: false,
			default: true,
		},
	},
	data: () => {
		return {
			isFocused: false,
		};
	},
	mounted()
	{
		this.adjustQueryNodeHeight();
	},
	computed: {
		isTitleVisible(): boolean
		{
			return this.isFocused || this.value;
		},
		titleClasses(): Object
		{
			return {
				'mobile-address-field-title': true,
				'mobile-address-field-title-focused': this.isFocused,
			};
		},
		placeholder(): string
		{
			return this.isTitleVisible
				? this.$Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_NOT_ENTERED')
				: this.name;
		},
	},
	methods: {
		onFocusIn(): void
		{
			this.isFocused = true;

			if (window.platform === 'android')
			{
				setTimeout(() => {
					this.adjustWindowHeight()

					const container = this.$refs['container'];
					const buttonOffset = 50;
					const containerPosition = container.getBoundingClientRect().top;
					const offsetPosition = containerPosition - buttonOffset;

					window.scrollTo({
						top: offsetPosition,
						behavior: 'smooth'
					});

				}, 300);
			}
		},
		onFocusOut(): void
		{
			this.isFocused = false;
		},
		onInput(event): void
		{
			this.$emit('input', {
				type: this.type,
				value: event.target.value,
			});

			this.adjustQueryNodeHeight();
		},
		adjustQueryNodeHeight(): void
		{
			setTimeout(() => {
				const queryNode = this.$refs['textarea-query'];
				if (queryNode)
				{
					Dom.style(queryNode, 'height', 'auto');
					Dom.style(queryNode, 'height', `${queryNode.scrollHeight}px`);
				}
			}, 0);
		},
	},
	template: `
		<div ref="container" class="mobile-address-field-container">
			<div
				v-show="isTitleVisible"
				:class="titleClasses"
			>
				{{name}}
			</div>
			<textarea
				:placeholder="placeholder"
				:value="value"
				:disabled="!isEditable"
				@focus="onFocusIn"
				@focusout="onFocusOut"
				@input="onInput"
				class="mobile-address-field"
				ref="textarea-query"
				type="text"
				rows="1"
			></textarea>
		</div>
	`
};
