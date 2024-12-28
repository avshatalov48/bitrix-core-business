type MemberElement = [string, number | string];

export class ChatMemberDiffManager
{
	#initialManagers: number[] = [];
	#initialMembers: MemberElement[] = [];

	setInitialManagers(initialManagers: number[])
	{
		this.#initialManagers = initialManagers;
	}

	setInitialChatMembers(initialMembers: MemberElement)
	{
		this.#initialMembers = initialMembers;
	}

	getAddedMemberEntities(modifiedEntities: MemberElement[]): MemberElement[]
	{
		const originalSet = new Set(this.#initialMembers.map((elem) => JSON.stringify(elem)));

		return modifiedEntities.filter((elem) => !originalSet.has(JSON.stringify(elem)));
	}

	getDeletedMemberEntities(modifiedEntities: MemberElement[]): MemberElement[]
	{
		const modifiedSet = new Set(modifiedEntities.map((elem) => JSON.stringify(elem)));

		return this.#initialMembers.filter((elem) => !modifiedSet.has(JSON.stringify(elem)));
	}

	getAddedManagers(modifiedArray: number[]): number[]
	{
		const originalSet = new Set(this.#initialManagers);

		return modifiedArray.filter((elem) => !originalSet.has(elem));
	}

	getDeletedManagers(modifiedArray: number[]): number[]
	{
		const modifiedSet = new Set(modifiedArray);

		return this.#initialManagers.filter((elem) => !modifiedSet.has(elem));
	}
}
