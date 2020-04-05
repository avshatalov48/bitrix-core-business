export default function calculateDurationTransition(diff)
{
	const defaultDuration = 300;
	return Math.min((400 / 500) * diff, defaultDuration);
}