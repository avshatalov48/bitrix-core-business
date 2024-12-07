export const ReplaceLangPhraseTrait = {
	methods: {
		replaceLangPhrase(phrase: string): string
		{
			return this.$Bitrix.Loc.getMessage(phrase)
				.replaceAll('[break]', '<br>')
				.replaceAll('[bold]', '<span>')
				.replaceAll('[/bold]', '</span>')
			;
		},
	},
};
