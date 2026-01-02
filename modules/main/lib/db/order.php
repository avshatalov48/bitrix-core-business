<?php

namespace Bitrix\Main\DB;

enum Order: string
{
	case Asc = 'ASC';
	case Desc = 'DESC';
}
