import { ActionButton } from './action-button';
import type { BitrixVueComponentProps } from 'ui.vue3';

export const RecordVideoButton: BitrixVueComponentProps = {
	name: 'RecordVideoButton',
	components: {
		ActionButton,
	},
	// language=Vue
	template: `
		<ActionButton icon="--video-3" :title="$Bitrix.Loc.getMessage('UI_RICH_TEXT_AREA_RECORD_VIDEO')" />
	`,
};
