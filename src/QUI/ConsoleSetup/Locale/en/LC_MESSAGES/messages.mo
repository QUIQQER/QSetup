��    F      L  a   |        #     #   C  '   g  $   �     �     �     �     �     �          ,     B     U     o     �     �     �     �  
   �     �               2  "   G     j          �     �     �     �     �     �      	     	  
   -	     8	     D	  +   S	  +   	  '   �	  !   �	     �	     
     /
  #   N
     r
     �
     �
  #   �
     �
     �
          2     K     e     �     �     �     �     �       $   +  %   P  $   v     �      �  #   �     �       ,   3     `          �     �  �   �  �   r  �   �  &   �  #   �     �               $     8  
   H     S     d  '   v     �  R   �     
     #     5  �   E     �     �               %  ,   ,  
   Y  "   d  �   �  5        >  
   W  =   b      �  +   �  -   �  ^        z  ;   �  !   �  +   �  &        :     G     [     g  %   u  	   �  
   �     �     �  	   �     �     �     �  	     	     "     <   <  8   y  !   �     �  -   �        �   ?     	   )                 .      4      @                  #                (   8   9                 <   F   1   A          3   :   %   "              /   D   *   5   C      7   +   6      ,                                '          ;   =   ?   2          &   0   $         -         !                  >              E         
                  B           database.credentials.not.valid exception.validation.password.empty exception.validation.path.not.exist exception.validation.path.not.writeable exception.validation.version.invalid help.prompt.cms help.prompt.host help.prompt.url locale.localeset.failed message.preset.available message.step.database message.step.language message.step.paths message.step.requirements message.step.setup message.step.superuser message.step.template message.step.version messages.decorative.coffeetime prompt.cms prompt.database.createnew prompt.database.db prompt.database.driver prompt.database.host prompt.database.not.empty.continue prompt.database.port prompt.database.prefix prompt.database.pw prompt.database.user prompt.host prompt.language prompt.password prompt.password.again prompt.requirements.continue prompt.template prompt.url prompt.user prompt.version setup.exception.validation.preset.not.exist setup.filesystem.composerjson.not.writeable setup.filesystem.config.creation.failed setup.message.finished.filerights setup.message.finished.header setup.message.finished.text setup.message.localeset.failed setup.prompt.continue.restored.data setup.prompt.dir.not.empty setup.restored.data.admin setup.restored.data.database setup.restored.data.database.driver setup.restored.data.db setup.restored.data.found setup.restored.data.host setup.restored.data.lang setup.restored.data.paths setup.restored.data.paths.cms setup.restored.data.paths.host setup.restored.data.paths.url setup.restored.data.prefix setup.restored.data.preset setup.restored.data.user setup.restored.data.version setup.validation.database.not.exists setup.warning.database.not.createable setup.warning.database.not.writeable setup.warning.dir.not.empty setup.warning.password.missmatch validation.database.driver.notfound warning.database.not.empty warning.requirement.unknown The given database credentials are not valid The password can not be empty. The given path does not exist. The given path is not empty. The given version is not valid. The absolute path of the directory in which QUIQQER will be installed.
Should end and start with slash.
Example: /var/www/vhosts/example.com/httpdocs/ The domain under which QUIQQER will be reachable. 
Should start with http:// and end with with slash. 
Example : http://example.com The path after the domain, if QUIQQER is installed into an subdirectory of the document root.
Should start and end with slash.
Example: /quiqqer/ for http://example.com/quiqqer/ The desired language could not be set. Following presets can be selected:  Database settings Language settings Pathsettings System requirements Executing Setup Admin user Preset selection Version selection Almost done. Perfect time for a coffee. Installation directory:  The given database does not exist. Do you want QUIQQER to try and create it? (y/n) Database databasename :  Database driver : Database host : How do you want to proceed?
   - n : Select new database 
   - c : Clear the selected database. Warnung all data will be lost! 
   - q : Quit setup Database port:  Database table prefix:  Database password:  Database user:  URL :  Please enter the main language for quiqqer:  Password:  Please enter your password again:  Not all Systemrequirements are fulfilled. This can severly impact the functionality of QUIQQER. Do you want to continue anyways? Please enter the name of the preset you want to use:  Subdirectory after host  Username:  Please enter the version of Quieer that should be installed:  The given preset does not exist. The composer.json file could not be written The configuration files could not be written. Please make sure that the executing PHP-User owns the files and the Webserver has read-access. Setup finished Setup finished successfully. You can now open your website. Setup could not set the language! Do you want to use the restored data? (y/n) Do you want to continue anyway? (y/n)  Admin user:  Database settings:     Driver:     Database:  The setup has found restorable data :    Host:  Language:  Path settings:     CMS directory:     Host:     URL directory:     Prefix:  Preset:     User:  Version:  The given database does not exist. The given user could not create the database (No Permission) The given database could not be edited (Creating Tables) The given directory is not empty! The passwords do not match! The given database driver could not be found. The given database is not empty! Some Requirements could not be detected. Please make sure that those Requirements are installed. This warning is void, if you know they are installed. 