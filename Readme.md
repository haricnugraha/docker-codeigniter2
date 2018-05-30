## Dockerize Nginx - PHP 5.6 - MariaDB - PHPMyAdmin - Reverse Proxy

### Getting Started
1. Clone/Download This Repository
2. Put your codeigniter project to www folder
3. Change .env file to your local variable
4. Run ``sudo docker-compose up -d``
5. Run the default IP 172.8.0.101 in your browser

### Database on Codeigniter 2
1. to install database refer to this repository https://github.com/haricnugraha/docker-mysql-phpmyadmin

### Temporary Solution for Memcached
1. Create new folder named ``cache`` in ``system/``
2. Change ``system/libraries/Cache/Cache.php with Cache.php``

### To do
- [ ] Memcached Config

### License
[MIT License](https://github.com/haricnugraha/docker-codeigniter2/blob/master/LICENSE)
