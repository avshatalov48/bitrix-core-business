const OrientationDisabled = {
	computed:
	{
		localize()
		{
			return Object.freeze({
				BX_IM_COMPONENT_CALL_ROTATE_DEVICE: this.$root.$bitrixMessages.BX_IM_COMPONENT_CALL_ROTATE_DEVICE
			});
		}
	},
	template: `
		<div class="bx-im-component-call-orientation-disabled-wrap">
			<div class="bx-im-component-call-orientation-disabled-icon"></div>
			<div class="bx-im-component-call-orientation-disabled-text">
				{{ localize.BX_IM_COMPONENT_CALL_ROTATE_DEVICE }}
			</div>
		</div>
	`
};

export {OrientationDisabled};