import { ActionButton } from './action-button';
import type { BitrixVueComponentProps } from 'ui.vue3';

export const FileButton: BitrixVueComponentProps = {
	name: 'FileButton',
	components: {
		ActionButton,
	},
	// language=Vue
	template: `
		<ActionButton icon="--attach" :title="$Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_UPLOAD_FILE')" />
	`,
};
