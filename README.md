# REVOLUZIONE PERSONALE
## INIZIALIZZAZIONE PROGETTO
**Clone progetto**
Eseguire i seguenti steps:
1. Effettuare il clone del repository 
2. Staccare un branch da **develop**

**Avviare il progetto**
1. Posizionarsi nella root del progetto
2. Copiare il file **.env.example** in **.env**
3. Lanciare il comando da terminale *sh marion.sh up -d**

**Allineare i contenuti del progetto alla versione online**
1. Spostarsi nella root del progetto
2. Eseguire i seguenti comandi da terminale nell'ordine in cui sono elencati (utilizzare come password per l'scp **s0l0n01!**):
a. *rsync -a revoluzione@revoluzione.test3d0.it:public_html/upload/ upload/*
b. *rsync -a revoluzione@revoluzione.test3d0.it:public_html/media/ media/*



**Allineare il database del progetto alla versione online**
1. Accedere al cpanel del progetto 
url: **https://revoluzione.test3d0.it:2083/**
username: **revoluzione**
password: **s0l0n01!**
2. Accedere al servizo **phpMyAdmin**
3. Effettuare l'esportazione del database **revoluzi_db**
4. Avviare il progetto in locale ed accedere all'url http://localhost:8080 (le credenziali di phpMyAdmin sono presenti nel file **.env**)
5. Importare il dump del database nel database "dbname"
