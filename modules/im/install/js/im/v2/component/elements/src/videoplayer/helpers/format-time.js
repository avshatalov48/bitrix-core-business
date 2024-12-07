export function formatTime(seconds: number): string
{
	const hours = Math.floor(seconds / 3600);
	const minutes = Math.floor((seconds % 3600) / 60);
	const remainingSeconds = Math.floor(seconds % 60);

	const formattedHours = hours > 0 ? `${hours}:` : '';
	const formattedMinutes = hours > 0 ? padZero(minutes) : minutes.toString();
	const formattedSeconds = padZero(remainingSeconds);

	return `${formattedHours}${formattedMinutes}:${formattedSeconds}`;
}

function padZero(num: number): string
{
	return num.toString().padStart(2, '0');
}
