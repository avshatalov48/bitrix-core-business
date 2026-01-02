import { AvatarBase } from 'ui.avatar';

// @vue/component
export const UserAvatar = {
	props: {
		src: {
			type: String,
			default: '',
		},
		size: {
			type: Number,
			default: 14,
		},
	},
	created(): void
	{
		this.avatar = new AvatarBase({
			size: this.size,
			picPath: this.src,
			baseColor: '#858D95',
		});
	},
	mounted(): void
	{
		if (this.avatar && this.$refs.container)
		{
			this.avatar.renderTo(this.$refs.container);
		}
	},
	template: `
		<div class="socialnetwork-collab-converter-wizard-user-list-avatar" ref="container"/>
	`,
};
