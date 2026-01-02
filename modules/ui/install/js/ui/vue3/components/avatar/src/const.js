import {
	AvatarBase,
	AvatarHexagon, AvatarHexagonAccent, AvatarHexagonExtranet, AvatarHexagonGuest,
	AvatarRound, AvatarRoundAccent, AvatarRoundExtranet, AvatarRoundGuest,
	AvatarSquare, AvatarSquareAccent, AvatarSquareExtranet, AvatarSquareGuest,
} from 'ui.avatar';
import type { AvatarType } from './type';

export const AvatarConcreteClassByType: Record<AvatarType, typeof AvatarBase> = Object.freeze({
	round: AvatarRound,
	'round-guest': AvatarRoundGuest,
	'round-extranet': AvatarRoundExtranet,
	'round-accent': AvatarRoundAccent,
	hexagon: AvatarHexagon,
	'hexagon-guest': AvatarHexagonGuest,
	'hexagon-extranet': AvatarHexagonExtranet,
	'hexagon-accent': AvatarHexagonAccent,
	square: AvatarSquare,
	'square-guest': AvatarSquareGuest,
	'square-extranet': AvatarSquareExtranet,
	'square-accent': AvatarSquareAccent,
});
