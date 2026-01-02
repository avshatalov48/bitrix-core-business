import './css/title.css';

// @vue/component
export const HeaderTitle = {
	name: 'HeaderTitle',
	props:
	{
		totalPinCounter: {
			type: Number,
			required: true,
		},
	},
	emits: ['toggleList'],
	computed:
	{
		title(): string
		{
			return this.loc(
				'IM_DIALOG_CHAT_PINNED_TITLE_MULTIPLE_COUNTER',
				{
					'#PINS_COUNT#': `
						<span class="bx-im-dialog-chat__pin-header_counter-total">
							${this.totalPinCounter}
						</span>
					`,
				},
			);
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-dialog-chat__pin-header_opened">
		    <div v-html="title"></div>
			<button
				class="bx-im-messenger__cross-icon"
				@click="$emit('toggleList')"
			></button>
		</div>
	`,
};
