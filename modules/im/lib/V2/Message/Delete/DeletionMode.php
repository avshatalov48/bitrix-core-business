<?php

namespace Bitrix\Im\V2\Message\Delete;

enum DeletionMode: int
{
	case None = 0;
	case Soft = 1;
	case Complete = 2;
}
