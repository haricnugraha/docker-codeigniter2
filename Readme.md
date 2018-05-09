## Dockerize Nginx - PHP 5.6 - MariaDB - PHPMyAdmin - Reverse Proxy

### Getting Started
1. Clone This Repository
2. Clone your PHP project to this repo and renamed it to www
2. Add ``dump.sql`` to this repo for initialize database
4. Change .env file to your local variable
5. Run ``docker-compose up -d``

### Import Database
1. Run ``docker ps``
2. Run ``docker exec -it hargadunia-mariadb ../bin/bash``
3. Run ``mysql -u root -p -D ${MYSQL_DATABASE} < /docker-entrypoint-initdb.d/dump.sql``
4. Enter password which is ``${MYSQL_PASSWORD}``
5. Make a cup of coffee is a good idea, it will takes a while to import database

*Note: phpmyadmin would serve on ${PHPMYADMIN_HOST}*

### Database Config on Codeigniter 2
1. Open ``application/config/database.php``
2. Change hostname to ``mysql``

### Temporary Solution for Memcached
1. Create new folder named ``cache`` in ``system/``
2. Change ``system/libraries/Cache/Cache.php with Cache.php``

### To do
- [ ] Memcached Config

### License
[MIT License](https://github.com/haricnugraha/docker-codeigniter2/blob/master/LICENSE)