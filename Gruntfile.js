'use strict';

module.exports = function(grunt) {
	require('load-grunt-tasks')(grunt);
	if(grunt.option('account-id') === undefined){
		return grunt.fail.fatal('--account-id is required', 1);
	}

	var path = require('path');
	grunt.initConfig({
		lambda_deploy: {
            streamTestService: {
				package: 'streamTestService',
				options: {
					file_name: 'index.js',
					handler: 'handler',
				},
				arn: 'arn:aws:lambda:us-east-1:' + grunt.option('account-id') + ':function:streamTestService',
			},
            streamTestListener: {
                package: 'streamTestListener',
                options: {
                    file_name: 'listener.js',
                    handler: 'handler',
                },
                arn: 'arn:aws:lambda:us-east-1:' + grunt.option('account-id') + ':function:streamTestListener',
            }
		},
		lambda_package: {
            streamTestService: {
				package: 'streamTestService',
			},
            streamTestListener: {
                package: 'streamTestListener',
            }
		},
		env: {
			prod: {
				NODE_ENV: 'production',
			},
		},

	});


    grunt.registerTask('deploy_service', ['env:prod', 'lambda_package:streamTestService', 'lambda_deploy:streamTestService']);
    grunt.registerTask('deploy_listener', ['env:prod', 'lambda_package:streamTestListener', 'lambda_deploy:streamTestListener']);
};
