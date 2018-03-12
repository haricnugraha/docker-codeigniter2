## Dockerize Nginx - PHP 5.6 - MariaDB - PHPMyAdmin - Reverse Proxy

### Getting Started
1. Clone/Fork This Repository
2. Clone PHP project to this repo and renamed it to www
2. Add ``dump.sql`` for initialize database
4. Change .env file to your local variable
5. Run ``docker-compose up -d``

### Import Database
1. Run ``docker ps``
2. Find mariadb container names, it should be dockercodeigniter2_mysql_1 if you clone this repository
3. Run ``docker exec -it ${mariadb_container_name} /bin/bash``
4. Run ``mysql -u root -p -D ${MYSQL_DATABASE} < /docker-entrypoint-initdb.d/dump.sql``
5. Enter password which is ``${MYSQL_PASSWORD}``
6. Make a cup of coffee is a good idea, it will takes a while to import the database

*Note: phpmyadmin would serve on ${PHPMYADMIN_HOST}*

### Database Config on Codeigniter 2
1. Open ``application/config/database.php``
2. Change hostname to ``mysql``

### Temporary Solution for Memcached
1. Change ``system/libraries/Cache/Cache.php with Cache.php``

### To do
- [ ] Memcached Config

### License
[MIT License](https://github.com/haricnugraha/docker-codeigniter2/blob/master/LICENSE)