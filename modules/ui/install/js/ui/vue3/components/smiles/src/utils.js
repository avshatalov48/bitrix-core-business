export function isMac(): boolean
{
	return navigator.userAgent.toLowerCase().includes('macintosh');
}

export function isLinux(): boolean
{
	return navigator.userAgent.toLowerCase().includes('linux');
}

export function isWindows(): boolean
{
	return navigator.userAgent.toLowerCase().includes('windows') || (!isMac() && !isLinux());
}
