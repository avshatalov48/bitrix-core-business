export type Perms = {
	canEdit: boolean,
	canInvite: boolean,
	canLeave: boolean,
	canEditFeatures: boolean,
	canFollow: boolean,
	canPin: boolean,
}

export type Member = {
	id: number,
	isAutoMember: boolean,
	isModerator: boolean,
	isOwner: boolean,
	isScrumMaster: boolean,
	photo: string,
}

export type GroupData = {
	id: number,
	name: string,
	description: string,
	avatar?: string,
	isPin?: boolean,
	privacyCode?: string,
	isSubscribed?: boolean,
	numberOfMembers?: number,
	listOfMembers?: Array<Member>,
	actions?: Perms,
	counters?: { [key: string]: number },
	efficiency?: number,
	dateCreate?: string,
	features?: Array<GroupFeature>,
}

export type GroupFeature = {
	featureName: string,
	name: string,
	customName: string,
	id: number,
	active: boolean,
}
