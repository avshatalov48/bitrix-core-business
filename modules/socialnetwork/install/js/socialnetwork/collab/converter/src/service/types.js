export type MemberDto = {
	id: number,
	photo: string,
	isModerator: boolean,
	isScrumMaster: boolean,
	isOwner: boolean,
};

export type Member = {
	id: number,
	avatar: string,
	role: 'owner' | 'moderator' | 'member',
};

export type FeatureDto = {
	featureName: string,
	active: boolean,
};

export type Feature = {
	name: string,
	isActive: boolean,
};

export type GroupDto = {
	ID: number,
	NAME: string,
	AVATAR: string,
	AVATAR_TYPE: string,
	AVATAR_TYPES: Array<Object>,
	LIST_OF_MEMBERS: Array<MemberDto>,
	FEATURES: Array<FeatureDto>,
};

export type Group = {
	id: number,
	name: string,
	image: string,
	members: Array<Member>
};
