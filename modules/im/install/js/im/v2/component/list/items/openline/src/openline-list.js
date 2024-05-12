import './css/openline-list.css';

// @vue/component
export const OpenlineList = {
	methods: {
		onChatClick(i)
		{
			this.$emit('chatClick', i);
		}
	},
	template: `
		<div class="bx-im-list-openline__content">
			<div>Openline List</div>
			<br>
			<div v-for="i in 100" @click="onChatClick(i)" class="bx-im-list-openline__item">
				Openline {{ i }}
			</div>
		</div>
	`
};