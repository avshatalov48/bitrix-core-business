import type { Group, Member, Feature, MemberDto, FeatureDto } from './types';

export function mapMemberDtoToModel(memberDto: MemberDto): Member
{
	const role = {
		true: 'member',
		[memberDto.isModerator || memberDto.isScrumMaster]: 'moderator',
		[memberDto.isOwner]: 'owner',
	}.true;

	return {
		id: memberDto.id,
		avatar: memberDto.photo,
		role,
	};
}

export function mapFeatureDtoToModel(featureDto: FeatureDto): Feature
{
	return {
		name: featureDto.featureName,
		isActive: featureDto.active,
	};
}

export function mapGroupDtoToModel(groupDto): Group
{
	const avatarType = groupDto.AVATAR_TYPE;
	const avatarTypes = groupDto.AVATAR_TYPES;
	const image = groupDto.AVATAR || avatarTypes?.[avatarType]?.entitySelectorUrl || '';

	return {
		id: groupDto.ID,
		name: groupDto.NAME,
		image,
		members: groupDto.LIST_OF_MEMBERS.map((memberDto: MemberDto) => mapMemberDtoToModel(memberDto)),
		features: groupDto.FEATURES.map((featureDto) => mapFeatureDtoToModel(featureDto)),
	};
}
