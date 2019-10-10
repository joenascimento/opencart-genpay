#!/bin/bash
docker-compose up -d
./bin/ln.sh
./bin/db.sh
chmod -R 777 www/
