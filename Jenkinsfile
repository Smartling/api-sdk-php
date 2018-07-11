pipeline {
  agent any

  stages {
    stage('Run tests') {
      steps {
        sh "docker run --rm -w \"${WORKSPACE}\" -v \"${WORKSPACE}:/${WORKSPACE}\" composer/composer install"
        sh "docker run --rm -w \"${WORKSPACE}\" -e account_uid=${params.ACCOUNT_UID} -e project_id=${params.PROJECT_ID} -e user_id=${params.USER_ID} -e user_key=${params.USER_KEY} -v \"${WORKSPACE}:/${WORKSPACE}\" phpunit/phpunit:4.8.5 --log-junit tests-result.xml --coverage-clover tests-clover.xml --debug --verbose"
      }
    }

    stage('Junit') {
      steps {
        junit 'tests-result.xml'
      }
    }

    stage('Sonar') {
      steps {
        script {
          String scannerHome = tool name: 'sonar', type: 'hudson.plugins.sonar.SonarRunnerInstallation';
          withSonarQubeEnv('sonar') {
            sh "${scannerHome}/bin/sonar-scanner -Dsonar.language=php -Dsonar.php.coverage.reportPath=tests-clover.xml -Dsonar.php.tests.reportsPath=tests-result.xml -Dsonar.sources=src -Dsonar.projectKey=\"${params.SONAR_PROJECT_KEY}\" -Dsonar.projectName=\"${params.SONAR_PROJECT_NAME}\" -Dsonar.projectVersion=${env.BUILD_NUMBER} -Dsonar.php.file.suffixes=\"php,php3,php4,php5,phtml,inc,module,install\" -Dsonar.exclusions=\"${params.SONAR_EXCLUDE_PATH}\""
          }
        }
      }
    }

    stage("Quality Gate") {
      steps {
        script {
          try {
            timeout(time: 5, unit: 'MINUTES') {
              def qg = waitForQualityGate()
              if (qg.status != 'OK') {
                error "Pipeline aborted due to quality gate failure"
              }
            }
          }
          catch (err) {
            // Catch timeout exception but not Quality Gate.
            String errorString = err.getMessage();

            if (errorString == "Pipeline aborted due to quality gate failure") {
              error errorString
            }
          }
        }
      }
    }

    stage('Clean up') {
      steps {
        deleteDir()
      }
    }
  }

  post {
    unstable {
      slackSend (
        channel: "${params.SLACK_CHAT}",
        color: 'bad',
        message: "Tests failed: <${env.BUILD_URL}|${env.JOB_BASE_NAME} #${env.BUILD_NUMBER}>"
      )
    }

    failure {
      slackSend (
        channel: "${params.SLACK_CHAT}",
        color: 'bad',
        message: "Build of <${env.BUILD_URL}|${env.JOB_BASE_NAME} #${env.BUILD_NUMBER}> is failed!"
      )
    }
  }

}
