import { type JsonObject } from 'main.core';

import '../css/dialog-loader.css';

// @vue/component
export const CommentsDialogLoader = {
	name: 'CommentsDialogLoader',
	data(): JsonObject
	{
		return {};
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-comments-dialog-loader__container">
			<div class="bx-im-comments-dialog-loader__spinner"></div>
		</div>
	`,
};
