import { BackendVotedUser } from '../types';

export class VoteResultService
{
	#signedAttachId: number;
	#limit: number;

	constructor(signedAttachId: string, limit: number = 10)
	{
		this.#signedAttachId = signedAttachId;
		this.#limit = limit;
	}

	async loadAnswer(answerId: number, page: number = 1): Promise<BackendVotedUser[]>
	{
		const data = {
			signedAttachId: this.#signedAttachId,
			answerId,
		};

		const navigation = {
			size: this.#limit,
			page,
		};

		const response = await BX.ajax.runAction('vote.AttachedVote.getAnswerVoted', { data, navigation });

		return response?.data?.items ?? [];
	}
}
