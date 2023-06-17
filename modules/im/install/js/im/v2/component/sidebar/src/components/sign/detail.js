// @vue/component
export const SignDetail = {
	emits: ['back'],
	data() {
		return {};
	},
	template: `
		<div>
		<div @click="$emit('back')" style="margin-bottom: 20px; cursor: pointer">&lt;- Back</div>
			<div v-for="i in 50">Sign {{ i }}</div>
		</div>
	`
};