#!/usr/bin/env bash
DESTINAZIONE=$1
mkdir -p $DESTINAZIONE/cache
mkdir -p $DESTINAZIONE/minimized
# controllo se esiste il file di esportazione iniziale
if [ -f "$DESTINAZIONE/esportazione.tar" ]; then
     /bin/tar -xvf $DESTINAZIONE/esportazione.tar -C $DESTINAZIONE
     /bin/rm -r -f $DESTINAZIONE/media
     /bin/rm -r -f $DESTINAZIONE/upload
     /bin/rm -f $DESTINAZIONE/database.sql
     /bin/mv $DESTINAZIONE/esportazione/media $DESTINAZIONE
     /bin/mv $DESTINAZIONE/esportazione/upload $DESTINAZIONE
     /bin/cp $DESTINAZIONE/esportazione/database.sql $DESTINAZIONE
     /bin/rm -f $DESTINAZIONE/esportazione.tar
     /bin/rm -r -f $DESTINAZIONE/esportazione
     /bin/bash $DESTINAZIONE/marion.sh db deploy
     
fi
#/bin/mv $DESTINAZIONE/.env $DESTINAZIONE/..
/bin/rm -f $DESTINAZIONE/deploy.sh
/bin/rm -f $DESTINAZIONE/marion.sh
#sed "s/\.env/\.\.\/\.env/g" $DESTINAZIONE/config/include.inc.php > $DESTINAZIONE/config/include.inc.php
exit 0