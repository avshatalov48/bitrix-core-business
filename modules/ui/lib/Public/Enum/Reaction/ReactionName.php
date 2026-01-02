<?php

namespace Bitrix\UI\Public\Enum\Reaction;

enum ReactionName: string
{
	case LIKE = 'like';
	case FACE_WITH_TEARS_OF_JOY = 'faceWithTearsOfJoy';
	case RED_HEART = 'redHeart';
	case NEUTRAL_FACE = 'neutralFace';
	case FIRE = 'fire';
	case CRY = 'cry';
	case SLIGHTLY_SMILING_FACE = 'slightlySmilingFace';
	case WINKING_FACE = 'winkingFace';
	case LAUGH = 'laugh';
	case KISS = 'kiss';
	case WONDER = 'wonder';
	case SLIGHTLY_FROWNING_FACE = 'slightlyFrowningFace';
	case LOUDLY_CRYING_FACE = 'loudlyCryingFace';
	case FACE_WITH_STUCK_OUT_TONGUE = 'faceWithStuckOutTongue';
	case FACE_WITH_STUCK_OUT_TONGUE_AND_WINKING_EYE = 'faceWithStuckOutTongueAndWinkingEye';
	case SMILING_FACE_WITH_SUNGLASSES = 'smilingFaceWithSunglasses';
	case CONFUSED_FACE = 'confusedFace';
	case FLUSHED_FACE = 'flushedFace';
	case THINKING_FACE = 'thinkingFace';
	case ANGRY = 'angry';
	case SMILING_FACE_WITH_HORNS = 'smilingFaceWithHorns';
	case FACE_WITH_THERMOMETER = 'faceWithThermometer';
	case FACEPALM = 'facepalm';
	case POO = 'poo';
	case FLEXED_BICEPS = 'flexedBiceps';
	case CLAPPING_HANDS = 'clappingHands';
	case RAISED_HAND = 'raisedHand';
	case DISLIKE = 'dislike';
	case SMILING_FACE_WITH_HEART_EYES = 'smilingFaceWithHeartEyes';
	case SMILING_FACE_WITH_HEARTS = 'smilingFaceWithHearts';
	case PLEADING_FACE = 'pleadingFace';
	case RELIEVED_FACE = 'relievedFace';
	case FOLDED_HANDS = 'foldedHands';
	case OK_HAND = 'okHand';
	case SIGN_HORNS = 'signHorns';
	case LOVE_YOU_GESTURE = 'loveYouGesture';
	case CLOWN_FACE = 'clownFace';
	case PARTYING_FACE = 'partyingFace';
	case QUESTION_MARK = 'questionMark';
	case EXCLAMATION_MARK = 'exclamationMark';
	case LIGHT_BULB = 'lightBulb';
	case BOMB = 'bomb';
	case SLEEPING_SYMBOL = 'sleepingSymbol';
	case CROSS_MARK = 'crossMark';
	case WHITE_HEAVY_CHECK_MARK = 'whiteHeavyCheckMark';
	case EYES = 'eyes';
	case HANDSHAKE = 'handshake';
	case HUNDRED_POINTS = 'hundredPoints';

	/**
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function exists(string $value): bool
	{
		return self::tryFrom($value) !== null;
	}

	/**
	 *
	 * @return array<string>
	 */
	public static function getAll(): array
	{
		return array_column(self::cases(), 'value');
	}

	/**
	 *
	 * @param string $value
	 * @return self|null
	 */
	public static function fromString(string $value): ?self
	{
		return self::tryFrom($value);
	}
}
