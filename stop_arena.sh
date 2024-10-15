#!/bin/bash

kill $(ps aux | grep 'chl=0\|msg=YQByAGUAbgBhADEAdgAxAA==' | awk '{print $2}')  > /dev/null 2>&1 &
pkill -f arena.sh

echo "Script parado com sucesso!"