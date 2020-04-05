;(function(){

if (BX.user)
	return;

BX.user = {};

BX.user.getOnlineStatus = function(lastseen, now, utc)
{
	lastseen = BX.type.isDate(lastseen) ? lastseen : (BX.type.isNumber(lastseen) ? new Date(lastseen * 1000) : new Date(0));
	now = BX.type.isDate(now) ? now : (BX.type.isNumber(now) ? new Date(now * 1000) : new Date());
	utc = !!utc;

	var result = {
		'isOnline': false,
		'status': 'offline',
		'statusText': BX.message('U_STATUS_OFFLINE'),
		'lastSeen': lastseen,
		'lastSeenText': '',
		'now': now,
		'utc': utc
	};

	if (lastseen.getTime() === 0)
	{
		return result;
	}

	result.isOnline = now.getTime() - lastseen.getTime() <= parseInt(BX.message('LIMIT_ONLINE'))*1000;
	result.status = result.isOnline? 'online': 'offline';
	result.statusText = BX.message('U_STATUS_'+result.status.toUpperCase());

	if (lastseen.getTime() > 0 && now.getTime() - lastseen.getTime() > 300*1000)
	{
		result.lastSeenText = BX.date.formatLastActivityDate(lastseen, now, utc);
	}

	return result;
};

BX.user.getSecondsForLimitOnline = function()
{
	return parseInt(BX.message.LIMIT_ONLINE);
};

})();
