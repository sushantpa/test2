pipeline {
    agent any

    

    stages {
        stage('Deploy') {
          steps {
                echo 'Deploying the application...'
                sshPublisher(publishers: [sshPublisherDesc(configName: 'collection-center-payment', transfers: [sshTransfer(cleanRemote: false, excludes: '', execCommand: ''' 
                cd /var/www/html/collection_center_payment/
                pwd
                hostname
              
                sudo git pull https://$GITUSER:$GITPASSWD@github.com/BabbanGonaDev/collection_center_payment.git
                    
                
                ''', execTimeout: 2000000, flatten: false, makeEmptyDirs: false, noDefaultExcludes: false, patternSeparator: '[, ]+', remoteDirectory: '.', remoteDirectorySDF: false, removePrefix: '', sourceFiles: '*.tar.gz')], usePromotionTimestamp: false, useWorkspaceInPromotion: false, verbose: true)])        
            }
        }
    }
}
