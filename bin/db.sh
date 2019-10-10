#!/bin/bash
docker exec db-opencart bash -c "mysql -uroot -hdb-opencart -plinux dev < /root/opencart.sql"
