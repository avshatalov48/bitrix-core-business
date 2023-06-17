// @vue/component
export const OpenlineHeader = {
	props: {
		dialogId: {
			type: Number,
			default: 0
		}
	},
	methods: {
		toggleRightPanel()
		{
			this.$emit('toggleRightPanel');
		}
	},
	template: `
		<div class="bx-im-content-openline__header">
			<div class="bx-im-content-openline__header_left">Header for openline - {{ dialogId }}</div>
			<div class="bx-im-content-openline__header_right">
				<div class="bx-im-content-openline__header_item">Reassign</div>
				<div class="bx-im-content-openline__header_item">Close dialog</div>
				<div @click="toggleRightPanel" class="bx-im-content-openline__header_item">Panel</div>
			</div>
		</div>
	`
};