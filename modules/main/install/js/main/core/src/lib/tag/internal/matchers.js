const matchers = {
	tag: /<[a-zA-Z0-9\-\!\/](?:"[^"]*"|'[^']*'|[^'">])*>|{{uid[0-9]+}}/g,
	comment: /<!--(?!<!)[^\[>].*?-->/g,
	tagName: /<\/?([^\s]+?)[/\s>]/,
	attributes: /\s([\w\-_:.]+)\s?\n?=\s?\n?"([^"]+)?"|\s([\w\-_:.]+)\s?\n?=\s?\n?'([^']+)?'|\s([\w\-_:.]+)/gs,
	placeholder: /{{uid[0-9]+}}/g,
};

export default matchers;