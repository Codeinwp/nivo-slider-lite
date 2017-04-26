/* jshint node:true */
//https://github.com/kswedberg/grunt-version
module.exports = {
	options: {
		pkg: {
			version: '<%= package.version %>'
		}
	},
	project: {
		src: [
			'package.json'
		]
	},
	style: {
		options: {
			prefix: 'Version\\:\\s'
		},
		src: [
			'nivo-slider.php',
			'assets/css/nivo-slider.css',
		]
	},
	functions: {
		options: {
			prefix: 'version\\s+=\\s+[\'"]'
		},
		src: [
			'nivo-slider.php',
		]
	}
};
