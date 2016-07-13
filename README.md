# Interne Anweisungen

#####  1. In ein Arbeitsverzeichnis wechseln. Zum Beispiel einen Ordner in /tmp/  


#####  2. Das Setup clonen  
```bash  
git clone --branch=dev git@dev.quiqqer.com:quiqqer/qsetup.git
```  
#####  3. "create.php" ausführen. Diese lädt Databse.xml und andere Klassen nach (direkt von Gitlab ==> aktuell)  
```bash
php create.php
```
#####  4. Danach erhalten wir ein .zip mit allen Setup files, welches wir in unser Zielverzeichnis schieben.  Z.B. der vhost documentroot  
```bash
cp quiqqer.zip <targetlocation>
```
###### Beispiel :  
```bash
cp quiqqer.zip /var/www/html/quiqqer/
```
#####  5. Als nächstes wechseln wir das aktuelle arbeitsverzeichnis zu unserem Zielort  
```bash
cd <target>
```
###### Beispiel : 
```bash
cd /var/www/html/quiqqer/
```
#####  6. Nun entpacken wir das eben kopierte .zip File    
```bash
unzip quiqqer.zip
```

##### 7. Nachdem das Setup nun entpackt ist. Starten wir das Setup  
```bash
php quiqqer.php
```

##### 8. Zur Zeit der Verfassung dieser Anleitung herrschte in kleiner Bug, deshalb muss eine kleine Änderung vorgenommen werden  
```bash
nano quiqqer.php
```
Hier kommentiert man einfach die Zeile #require 'bootstrap.php' aus und beendet nano mit  
```
STRG + x
YES  
<ENTER>  
```

##### 9. Danach kann man quiqqer in der console aufrufen und sich einloggen  
``` 
php quiqqer.php
```

##### 10. Nun führen wir das Quiqqer-setup aus  
```
quiqqer:setup
```

##### 11. Als nächstes legen wir ein neues Projekt an  
```
quiqqer:create-project
```

##### 12. Danach führen wir das Quiqqer-Setup sicherheitshalber erneut aus  
```
quiqqer:setup
```
##### 13. Abschliessend lassen wir die .htaccess generieren  
```
quiqqer:htaccess
```

##### 14. Sobald dies abgeschlossen ist, beenden wir die Quiqqer-Konsole mit  
```
STRG + C
```

### 15. Geschafft!   
Quiqqer kann nun über die im setup angegeben url aufgerifen werden.  
Der Adminbereich ist erreichbar unter "<url>/admin/"   




    
    


# QUIQQER Setup

With the QUIQQER Setup you can install QUIQQER fast and easily.

## 1. How do I install QUIQQER?

First you need to run the following steps:  

+ 1.1 Download the QUIQQER Setup (http://update.quiqqer.com/quiqqer.zip)
+ 1.2 Extract the ZIP
+ 1.3 Upload to your webserver folder

### 2. The installation via Browser:

Open the quiqqer.php in your browser and follow the installation instructions.


### 2. The installation via bash is quite simpler

Execute the following command:

    php quiqqer.php

Please follow the installation instructions.
Thats it.