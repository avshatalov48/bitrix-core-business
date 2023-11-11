// noinspection ES6PreferShortImport
import { GetParameter } from './get-params';

export const PathPlaceholder = {
	dialog: `/online/?${GetParameter.openChat}=#DIALOG_ID#`,
	lines: `/online/?${GetParameter.openLines}=#DIALOG_ID#`,
};
