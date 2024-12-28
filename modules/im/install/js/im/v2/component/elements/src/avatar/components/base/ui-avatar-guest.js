import { AvatarRoundGuest } from 'ui.avatar';

import { AvatarSize, AvatarSizeMap } from '../base/avatar';

import './css/ui-avatar-guest.css';

// @vue/component
export const UiAvatarGuest = {
	name: 'UiAvatarGuest',
	props:
	{
		size: {
			type: String,
			default: AvatarSize.M,
		},
		url: {
			type: String,
			default: '',
		},
		title: {
			type: String,
			default: '',
		},
		backgroundColor: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		calculatedSize(): number
		{
			return AvatarSizeMap[this.size];
		},
	},
	watch:
	{
		title()
		{
			this.avatar.setTitle(this.title);
		},
		url()
		{
			this.setAvatarImage();
		},
	},
	created()
	{
		this.avatar = new AvatarRoundGuest({
			size: this.calculatedSize,
			title: this.title,
		});
		this.setAvatarImage();
		this.setBackgroundColor();
	},
	mounted()
	{
		this.avatar.renderTo(this.$refs['im-guest-avatar']);
	},
	methods:
	{
		setAvatarImage(): void
		{
			if (!this.url)
			{
				return;
			}

			this.avatar.setUserPic(this.url);
		},
		setBackgroundColor(): void
		{
			if (!this.backgroundColor)
			{
				return;
			}

			this.avatar.setBaseColor(this.backgroundColor);
		},
	},
	template: `
		<div class="bx-im-ui-avatar-guest__container" ref="im-guest-avatar"></div>
	`,
};
