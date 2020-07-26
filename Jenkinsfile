pipeline {
  agent any
  stages {
    stage('Dev') {
      steps {
        sh 'echo "hello world"'
      }
    }

    stage('Test') {
      parallel {
        stage('Test') {
          agent any
          steps {
            sh 'echo "Hello world"'
          }
        }

        stage('Test Suite A') {
          agent any
          steps {
            sh 'echo "test suite"'
          }
        }

      }
    }

    stage('deploy') {
      steps {
        sh 'echo "deploy"'
      }
    }

  }
}