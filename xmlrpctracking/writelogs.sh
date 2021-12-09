#!/bin/bash
while true
do
    netstat -antpe | grep -v "TIME_WAIT" > `date +%F_%T`-ns.txt
    ps auxfwww > `date +%F_%T`-ps.txt
    top ccbn1 -c > `date +%F_%T`-top.txt
    sleep 30
done

