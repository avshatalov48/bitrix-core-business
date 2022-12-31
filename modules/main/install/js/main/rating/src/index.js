import { Type } from 'main.core';

import { RatingLike } from './like.js';
import { RatingManager } from './manager.js';
import { RatingRender } from './render.js';

import likeAnimatedEmojiData from '../animations/em_01.json';
import laughAnimatedEmojiData from '../animations/em_02.json';
import wonderAnimatedEmojiData from '../animations/em_03.json';
import cryAnimatedEmojiData from '../animations/em_04.json';
import angryAnimatedEmojiData from '../animations/em_05.json';
import facepalmAnimatedEmojiData from '../animations/em_06.json';
import admireAnimatedEmojiData from '../animations/em_07.json';

export const lottieAnimations = Object.freeze({
	like: likeAnimatedEmojiData,
	laugh: laughAnimatedEmojiData,
	wonder: wonderAnimatedEmojiData,
	cry: cryAnimatedEmojiData,
	angry: angryAnimatedEmojiData,
	facepalm: facepalmAnimatedEmojiData,
	admire: admireAnimatedEmojiData,
});


if (Type.isUndefined(window.BXRL))
{
	window.BXRL = {};
}
window.BXRL.manager = RatingManager;
window.BXRL.render = RatingRender;

window.RatingLike = RatingLike;
