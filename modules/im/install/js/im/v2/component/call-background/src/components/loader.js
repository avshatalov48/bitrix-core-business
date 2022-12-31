// @vue/component
export const Loader = {
	name: 'CallBackgroundLoader',
	data()
	{
		return {};
	},
	template:
	`
		<div class="bx-im-call-background__loader">
			<svg class="bx-desktop-loader-circular" viewBox="25 25 50 50">
				<circle class="bx-desktop-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
			</svg>
		</div>
	`
};