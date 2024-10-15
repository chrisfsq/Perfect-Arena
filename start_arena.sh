#!/bin/bash
SERVICE="arena.sh"
if pgrep -x "$SERVICE" >/dev/null
then
    echo "Script ja esta sendo executado."
  exit 1
else
  nohup ./arena.sh > /dev/null 2>&1 &
  echo -e "\e[1;35mIniciando aguarde\e[1;32m[...]\e[0m"
  sleep 2
  echo "Script iniciado com sucesso!"
fi