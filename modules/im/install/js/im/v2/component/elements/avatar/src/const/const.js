export const AvatarSize = Object.freeze({
	XXS: 'XXS',
	XS: 'XS',
	S: 'S',
	M: 'M',
	L: 'L',
	XL: 'XL',
	XXL: 'XXL',
	XXXL: 'XXXL',
});

export const AvatarSizeMap = Object.freeze({
	[AvatarSize.XXXL]: 94,
	[AvatarSize.XXL]: 60,
	[AvatarSize.XL]: 48,
	[AvatarSize.L]: 42,
	[AvatarSize.M]: 32,
	[AvatarSize.S]: 22,
	[AvatarSize.XS]: 18,
	[AvatarSize.XXS]: 14,
});

export const ChatAvatarType = {
	notes: 'notes',
};

export const EmptyAvatarType = Object.freeze({
	default: 'default',
	squared: 'squared',
	collab: 'collab',
});
