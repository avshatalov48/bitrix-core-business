import { BitrixVue } from 'ui.vue';

export const StackLoad = BitrixVue.localComponent('ui.uploader.stack-widget.stack-load', {
	// language=Vue
	template: `
		<div class="ui-uploader-stack-load" @click="$emit('showPopup')">
			<div class="ui-uploader-stack-load-icon"></div>
		</div>
	`,
});
