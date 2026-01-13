module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	rules: {
		// WordPress provides these as externals
		'import/no-unresolved': [
			'error',
			{
				ignore: [ '^@wordpress/' ],
			},
		],

		// Do NOT require @wordpress/* to be installed
		'import/no-extraneous-dependencies': [
			'error',
			{
				optionalDependencies: [ '@wordpress/*' ],
			},
		],
	},
};
