#!/bin/bash

echo -e "\e[1;35mTask monitoring started successfully \e[1;32m[...]\e[0m"
echo -e "\e[1;35mEncomendas de scripts \e[1;32m[discord #chrisffs]\e[0m"

tail -f -n0 /home/logs/world2.chat | grep --line-buffered 'chl=0\|msg=YQByAGUAbgBhADEAdgAxAA==' | while read LINE0
do    
    php arena_controller.php arenaMonitor "${LINE0}"
done
