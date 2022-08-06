import { Type } from 'main.core';

import { RatingLike } from './like.js';
import { RatingManager } from './manager.js';
import { RatingRender } from './render.js';

if (Type.isUndefined(window.BXRL))
{
	window.BXRL = {};
}
window.BXRL.manager = RatingManager;
window.BXRL.render = RatingRender;

window.RatingLike = RatingLike;
