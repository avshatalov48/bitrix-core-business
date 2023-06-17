import {hint} from 'ui.vue3.directives.hint';

// @vue/component
export const DocumentPanel = {
	directives: {hint},
	data()
	{
		return {};
	},
	computed:
	{
		hintContent()
		{
			return {
				text: this.loc('IM_MESSENGER_NOT_AVAILABLE'),
				popupOptions: {
					bindOptions: {
						position: 'top'
					},
					angle: true,
					targetContainer: document.body,
					offsetLeft: 125,
					offsetTop: -30
				}
			};
		}
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-file-menu__document-panel_container">
			<div class="bx-im-file-menu__document-panel_title">
				{{ loc('IM_TEXTAREA_CREATE_AND_SEND_FILE') }}
			</div>
			<div class="bx-im-file-menu__document-panel_content" v-hint="hintContent">
				<div class="bx-im-file-menu__document-panel_item">
					<div class="ui-icon ui-icon-file-doc"><i></i></div>
					<div class="bx-im-file-menu__document-panel_item_title">
						{{ loc('IM_TEXTAREA_CREATE_DOCUMENT') }}
					</div>
				</div>
				<div class="bx-im-file-menu__document-panel_item">
					<div class="ui-icon ui-icon-file-xls"><i></i></div>
					<div class="bx-im-file-menu__document-panel_item_title">
						{{ loc('IM_TEXTAREA_CREATE_SPREADSHEET') }}
					</div>
				</div>
				<div class="bx-im-file-menu__document-panel_item">
					<div class="ui-icon ui-icon-file-ppt"><i></i></div>
					<div class="bx-im-file-menu__document-panel_item_title">
						{{ loc('IM_TEXTAREA_CREATE_PRESENTATION') }}
					</div>
				</div>
			</div>
		</div>
	`
};