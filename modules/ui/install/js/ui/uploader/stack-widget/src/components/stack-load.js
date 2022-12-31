export const StackLoad = {
	name: 'StackLoad',
	emits: ['showPopup'],
	// language=Vue
	template: `
		<div class="ui-uploader-stack-load" @click="$emit('showPopup')">
			<div class="ui-uploader-stack-load-icon"></div>
		</div>
	`,
};
