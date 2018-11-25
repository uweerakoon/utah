module.exports = (grunt) ->
  grunt.initConfig
    less:
      production:
        options:
          paths: ['css']
        files:
          'css/app.css': 'less/bootstrap.less'
    cssmin:
      options:
        shorthandCompacting: false,
        roundingPrecision: -1
      target:
        files:
          'static/app.css': ['css/app.css']
    uglify:
      options: null
      dist:
        files:
          'static/app.js': ['js/jquery.min.js', 'js/datatables.js', 'js/*.js']
    cachebreaker:
      dev:
        options:
          match: [{
            'app.js': 'static/app.js',
            'app.css': 'static/app.css',
          }]
          replacement: 'md5'
        files:
          src: ['head.html']
    gitadd:
      options: {}
      files: ['.']
    gitpush:
      origin:
        options:
          remote: 'origin'
          branch: 'master'
      production:
        options:
          remote: 'production'
          branch: 'master'
      origin_dev:
        options:
          remote: 'origin'
          branch: 'develop'
      dev:
        options:
          remote: 'dev'
          branch: 'develop'
    gitpull:
      master:
        options: {}
      dev:
        options:
          branch: 'dev'
    watch:
      less:
        files: ['less/**']
        tasks: ['less']

  grunt.loadNpmTasks 'grunt-git'
  grunt.loadNpmTasks 'grunt-contrib-less'
  grunt.loadNpmTasks 'grunt-contrib-cssmin'
  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-cache-breaker'
  grunt.loadNpmTasks 'grunt-contrib-watch'

  grunt.registerTask 'stage', ['cssmin', 'uglify', 'cachebreaker', 'gitadd']
  grunt.registerTask 'quick-stage', ['gitadd']
  grunt.registerTask 'push-production', ['gitpush:origin', 'gitpush:production']
  grunt.registerTask 'push-dev', ['gitpush:origin_dev', 'gitpush:dev']
  grunt.registerTask 'pull-production', ['gitpull:master']
  grunt.registerTask 'pull-dev', ['gitpull:dev']
  grunt.registerTask 'default', ['watch']
