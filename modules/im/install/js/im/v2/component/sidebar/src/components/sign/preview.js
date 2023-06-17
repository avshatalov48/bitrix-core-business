import '../../css/sign/preview.css';

// @vue/component
export const SignPreview = {
	props: {
		isLoading: {
			type: Boolean,
			default: false
		}
	},
	template: `
		<div class="bx-im-sidebar-sign-preview__scope">
			<div v-if="isLoading" class="bx-im-sidebar-sign-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-sign-preview__container" >
				Signed documents. Work in progress
			</div>
		</div>
	`
};