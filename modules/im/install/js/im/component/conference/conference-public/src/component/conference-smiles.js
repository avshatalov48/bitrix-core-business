import 'ui.vue.components.smiles';
import { EventEmitter } from 'main.core.events';
import { EventType } from "im.const";

const ConferenceSmiles = {
	methods:
	{
		onSelectSmile(event)
		{
			this.$emit('selectSmile', event);
		},
		onSelectSet(event)
		{
			this.$emit('selectSet', event);
		},
		hideSmiles()
		{
			EventEmitter.emit(EventType.conference.hideSmiles);
		}
	},
	// language=Vue
	template: `
		<div class="bx-im-component-smiles-box">
			<div class="bx-im-component-smiles-box-close" @click="hideSmiles"></div>
			<div class="bx-im-component-smiles-box-list">
				<bx-smiles
					@selectSmile="onSelectSmile"
					@selectSet="onSelectSet"
				/>
			</div>
		</div>
	`
};

export {ConferenceSmiles};