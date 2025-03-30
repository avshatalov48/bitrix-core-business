import { AvatarHexagonGuest, AvatarRoundExtranet, AvatarRoundGuest, AvatarBase } from 'ui.avatar';
import { AvatarSize, AvatarSizeMap } from './avatar';

import './css/base-ui-avatar.css';

export const AvatarType = {
	extranet: 'extranet',
	collaber: 'collaber',
	collab: 'collab',
	default: 'default',
};

// @vue/component
export const BaseUiAvatar = {
	props: {
		type: {
			type: String,
			required: true,
			validator(value): boolean
			{
				return Object.values(AvatarType).includes(value);
			},
		},
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
	computed: {
		AvatarSize: () => AvatarSize,
		calculatedSize(): number
		{
			return AvatarSizeMap[this.size];
		},
	},
	watch: {
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
		const classMap = {
			extranet: AvatarRoundExtranet,
			collaber: AvatarRoundGuest,
			collab: AvatarHexagonGuest,
			default: AvatarBase,
		};

		const AvatarClass = classMap[this.type] || classMap.default;
		this.avatar = new AvatarClass({
			size: this.calculatedSize,
			title: this.title,
		});

		this.setAvatarImage();
		this.setBackgroundColor();
	},
	mounted()
	{
		if (this.avatar && this.$refs.avatarContainer)
		{
			this.avatar.renderTo(this.$refs.avatarContainer);
		}
	},
	methods: {
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
		<div class="bx-im-base-ui-avatar__container" ref="avatarContainer"></div>
	`,
};
