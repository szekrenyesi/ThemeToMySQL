# ThemeToMySQL

This interface was created for importing T-patterns from [Theme](http://patternvision.com/) into a MySQL database. After the database is created, one can filter those patterns (with their locations) which are unique to the whole project. One can also download pattern locations in EAF format which makes it possible to explore and link patterns to media files using the software environment of [ELAN](https://tla.mpi.nl/tools/tla-tools/elan/) annotation tool.

## Requirements

- Apache 2.x
- PHP 7.4
- MySQL server

or

- Docker
- MySQL server

## Installation (native mode)

*Recommended only for older Linux systems (e.g Ubuntu 16.04) considering the old version of PHP*

1. Update your system:

```bash
sudo apt-get update
```

2. Install Apache:

```bash
sudo apt-get install apache2
```

3. Install MySQL:

```bash
sudo apt-get install mysql-server
```
*Please check the MySQL manual for further settings.*

4. Edit `/etc/mysql/my.cnf` and add:

```
[mysqld]
default_authentication_plugin=mysql_native_password
```

5. Install PHP & required modules:

```bash
sudo apt-get install php7.4 \
    libapache2-mod-php7.4 \
    php7.4-mysql \
    php7.4-zip \
    php7.4-mbstring \
    php7.4-mcrypt
```

6. Clone the repository into your DocumentRoot folder (default: `/var/www/html`):

```bash
cd /path/to/your/documentroot/
git clone https://github.com/szekrenyesi/ThemeToMySQL.git
cd ThemeToMySQL
```

7. Edit `conf/config.ini.default` with your MySQL credentials and rename it:

```
[database]
servername = 127.0.0.1
username = <YOUR LOCAL MYSQL USERNAME>
password = <YOUR LOCAL MYSQL PASSWORD>
dbname = users
[projects]
max_file_size = 10000000
max_project = 50
```

```bash
mv conf/config.ini.default conf/config.ini
```

8. Import the database for users and projects:

```bash
mysql -u root -p < mysql/projects.sql
```

9. You can reach the web service at:

[http://localhost/ThemeToMySQL](http://localhost/ThemeToMySQL)

---

## Installation (using Docker)

*Recommend way for current Linux systems*

1. Update your system:

```bash
sudo apt update
```

2. Install Docker:  

*Please refer to a manual for your Linux distribution.*

3. Install MySQL:

```bash
sudo apt install mysql-server
```

*Please check the MySQL manual for further settings.*

4. Edit `/etc/mysql/my.cnf` and add:

```
[mysqld]
default_authentication_plugin=mysql_native_password
```

5. Clone the repository for config files:

```bash
git clone https://github.com/szekrenyesi/ThemeToMySQL.git
cd ThemeToMySQL
```

6. Edit `conf/config.ini.default` with your MySQL credentials and rename it:

```
[database]
servername = 127.0.0.1
username = <YOUR LOCAL MYSQL USERNAME>
password = <YOUR LOCAL MYSQL PASSWORD>
dbname = users
[projects]
max_file_size = 10000000
max_project = 50
```

```bash
mv conf/config.ini.default conf/config.ini
```

7. Create the database for users and projects:

```bash
mysql -u root -p < mysql/projects.sql
```

8. Pull the Docker image:

```bash
docker pull szekrenyesi/theme2mysql:latest
```

9. Run the container:

```bash
docker run -d --rm --network="host" \
    --name theme2mysql \
    -v /path/on/host/data:/var/www/html/ThemeToMySQL/data \
    -v /path/on/host/conf:/var/www/html/ThemeToMySQL/conf \
    szekrenyesi/theme2mysql:latest
```

10. You can reach the web service under localhost using port 8080:

[http://localhost:8080/ThemeToMySQL](http://localhost:8080/ThemeToMySQL)
