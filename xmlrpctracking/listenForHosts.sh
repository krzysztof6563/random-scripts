#!/bin/bash
tail -f tcpdump14022016 | egrep "Host:|xmlrpc" | egrep -v "mielec|X-Pingback|rindipol" | tee list.txt
