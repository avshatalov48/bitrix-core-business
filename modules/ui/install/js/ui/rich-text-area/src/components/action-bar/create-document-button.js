import { ActionButton } from './action-button';
import type { BitrixVueComponentProps } from 'ui.vue3';

export const CreateDocumentButton: BitrixVueComponentProps = {
	name: 'CreateDocumentButton',
	components: {
		ActionButton,
	},
	// language=Vue
	template: `
		<ActionButton icon="--file" :title="$Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_CREATE_DOCUMENT')" />
	`,
};
