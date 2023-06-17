import type {reactionType as ReactionType} from 'ui.reactions-select';

export type Reactions = {
	reactionCounters: {[reactionType: string]: number},
	reactionUsers: {[reactionType: string]: Set<number>},
	ownReactions: Set<$Values<typeof ReactionType>>
};