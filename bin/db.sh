#!/bin/bash
echo "start seed db-opencart"
docker exec db-opencart bash -c "mysql -uroot -hdb-opencart -plinux dev < /root/opencart.sql"
echo "db-opencart seed complete"
