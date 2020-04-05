/////////////////////////////////////////////////////////////////////////////////////
// DelayActivity
/////////////////////////////////////////////////////////////////////////////////////
DelayActivity = function()
{
	var ob = new BizProcActivity();
	ob.Type = 'DelayActivity';

	/** @return boolean */
	ob.CheckFields = function ()
	{
		return !!ob.Properties['TimeoutDuration'] || !!ob.Properties['TimeoutTime'];
	};

	return ob;
};