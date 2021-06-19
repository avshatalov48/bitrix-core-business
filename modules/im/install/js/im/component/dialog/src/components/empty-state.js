import { Vue } from "ui.vue";

export const EmptyState = {
	// language=Vue
	template: `
		<div class="bx-mobilechat-loading-window">
			<h3 class="bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-loading-msg">
		  		{{ $Bitrix.Loc.getMessage('IM_DIALOG_EMPTY') }}
			</h3>
		</div>
	`
};