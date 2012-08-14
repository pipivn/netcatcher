#! /bin/sh
DIR=$(cd $(dirname "$0"); pwd)
echo $DIR
AGENTS_PATH=$DIR/crawler

MONGODB_BIN_PATH=/root/src/mongodb-linux-x86_64-1.8.1/bin
MONGODB_DATA_PATH=$DIR/data/mongodb
MONGODB_LOG_PATH=$DIR/data/mongodb/mongodd.log

#!/bin/bash

# Echo to stderr
#
debug() {    
  echo "$@" >&2
}

do_start() {
	echo "Start mongodb..."
	$MONGODB_BIN_PATH/mongod --fork --logpath $MONGODB_LOG_PATH --logappend --dbpath $MONGODB_DATA_PATH  
}

do_run() {
        now=$(date +"%T")
        echo "Current time : $now"

	if ps ax | grep -v grep | grep mongod > /dev/null
        then
        	echo "Mongodb is running..."
        else
		echo "Start mongodb..."
        	$MONGODB_BIN_PATH/mongod --fork --logpath $MONGODB_LOG_PATH --logappend --dbpath $MONGODB_DATA_PATH &
	fi 

	for f in $AGENTS_PATH/*.js
	do
		(
			echo "node "$f
			node $f &
		)
	done
}

do_stop () {
	echo "Stop mongodb..."
    PID=$(cat $MONGODB_DATA_PATH/mongod.lock)
    if [ -z "$PID" ];
	then
		echo "[error] mongod isnt running, no need to stop"
		exit
    fi
    echo "Stopping mongo with-> /bin/kill -2 $PID"
	/bin/kill -2 $PID
}
    
case "$1" in
	start)
		do_start
	;;
	stop)
		do_stop
	;;
	run)
		do_run
	;;
	*)
		echo "Usage: $0 start|stop" >&2
		exit 3
	;;
esac


