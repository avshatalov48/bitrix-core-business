import { Core } from 'im.v2.application.core';

import type { AnchorType } from 'im.v2.const';
import type { Anchor } from '../../../type/anchor';

export function isAnchorsEqual(anchor1: Anchor, anchor2: Anchor): boolean
{
	return anchor1.messageId === anchor2.messageId
		&& anchor1.type === anchor2.type
		&& anchor1.userId === anchor2.userId
		&& anchor1.fromUserId === anchor2.fromUserId
	;
}

export function isAnchorWithTypeFromCurrentChat(anchor: Anchor, anchorType: AnchorType, chatId: number): boolean
{
	return anchor.userId === Core.getUserId()
		&& anchor.chatId === chatId
		&& anchor.type === anchorType
	;
}
