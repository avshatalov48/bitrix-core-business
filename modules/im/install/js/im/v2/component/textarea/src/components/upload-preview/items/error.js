// @vue/component
export const ErrorPreviewItem = {
	name: 'ErrorPreviewItem',
	template: `
		<div class="bx-im-upload-preview-file-item__item-error">
			<div class="bx-im-upload-preview-file-item__item-error-icon"></div>
			<div class="bx-im-upload-preview-file-item__item-error-text">
				{{ $Bitrix.Loc.getMessage('IM_TEXTAREA_UPLOAD_PREVIEW_POPUP_UPLOAD_ERROR') }}
			</div>
		</div>
	`,
};
