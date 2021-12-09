#!/bin/bash
while read line;
do
    if [ $(grep -lr "$line" --exclude=ips.txt --exclude=list.txt *.txt | wc -l) -gt 0 ]; then
        grep -lr "$line" --exclude=ips.txt --exclude=list.txt *.txt | while read file
        do
            PID=$(grep "$line" "$file" | awk '{ print $9 }' | cut -d '/' -f 1 | sort | uniq)
            if [[ "$PID" =~ ^[0-9]+$  ]]; then
                grep "$PID" "${file:0:20}ps.txt" | awk '{ print $1 }'
            fi
        done
    fi 
done < ips.txt
