import { BIcon } from 'ui.icon-set.api.vue';

export const Advantage = {
	components: {
		BIcon,
	},
	props: {
		icon: {
			type: String,
			required: true,
		},
		iconColor: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			required: true,
		},
		description: {
			type: String,
			default: '',
		},
	},
	template: `
		<div class="socialnetwork-collab-converter-wizard-advantage --slide">
			<BIcon :name="icon" :color="iconColor" :size="28"/>
			<div class="socialnetwork-collab-converter-wizard-advantage-content">
				<div class="socialnetwork-collab-converter-wizard-advantage-content-title" v-html="title"/>
				<div
					v-if="description.length > 0"
					class="socialnetwork-collab-converter-wizard-advantage-content-description"
					v-html="description"
				/>
			</div>
		</div>
	`,
};
