import { AvatarHexagonGuest } from 'ui.avatar';

import { AvatarSize, AvatarSizeMap } from '../base/avatar';

import './css/ui-avatar-hexagon.css';

// @vue/component
export const UiAvatarHexagon = {
	name: 'UiAvatarHexagon',
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
		this.avatar = new AvatarHexagonGuest({
			size: this.calculatedSize,
			title: this.title,
		});

		this.setAvatarImage();
		this.setBackgroundColor();
	},
	mounted()
	{
		this.avatar.renderTo(this.$refs['im-hexagon-avatar']);
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
		<div class="bx-im-ui-avatar-hexagon__container" ref="im-hexagon-avatar"></div>
	`,
};
