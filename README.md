# ThemeToMySQL

This interface was created for importing T-patterns from [Theme](http://patternvision.com/) into a MySQL database. After the database is created, one can filter those patterns (with their locations) which are unique to the whole project. One can also download pattern locations in EAF format which makes possible to explore and link patterns to media files using the sofware environment of [ELAN](https://tla.mpi.nl/tools/tla-tools/elan/) annotation tool.

## Requirements
	
	- Apache 2.x
	- PHP 7.0
	- MySQL
	
	or
	
	- Docker
	- 

## Installation (native mode)
	
	1. Update your system: 
		
		```
		
		sudo apt-get update
		
		```

	2. Installing Apache: 
		
		```
		
		sudo apt-get install apache2
	
		```
		
	3. Install MySQL:

		```
		
		sudo apt-get install mysql-server
		
		```
		Please check mysql manual for further settings!
		
	4. Install php & some php moduls:

		```
		
		sudo apt-get install php7.4 \
			libapache2-mod-php7.4 \
			php7.4-mysql \
			php7.4-zip \
			php7.4-mbstring \
			php7.4-mcrypt \

		```
		
	5. Clone the repository into your DocumentRoot folder (default is: /var/www/html):
		
		```
		
		cd /path/to/your/documentroot/
		git clone https://github.com/szekrenyesi/ThemeToMySQL.git
		cd ThemeToMySQL

		```
		
	6. Edit conf/config.ini.default file using your own mysql creditentials then rename it:
		
		```
		
		mv conf/config.ini.default conf/config.ini
		
		```
		
	7. Create database for users and projects:
		
		```
		
		mysql -u root -p < mysql/projects.sql

		```
		
	8. You can reach the webservice under localhost:

		[http://localhost/ThemeToMySQL](http://localhost/ThemeToMySQL)
		
		
### Installation (using docker with current Linux systems)

	1. Update your system: 
		
	```
		sudo apt-get update
	
	```
	2. Install docker:
	
		Please find a manual for your actual Linux system
	
	3. Install MySQL:

	```
		sudo apt-get install mysql-server
		
	```
	
	Please check mysql manual for further settings!
	

	4. Edit your /etc/mysql/mysql.conf.d/mysqld.cnf file, and add this line:
	
	```
	
	[mysqld]
	default_authentication_plugin=mysql_native_password
	

	```

	5. Clone repository for config files
	
		```
		
		git clone https://github.com/szekrenyesi/ThemeToMySQL.git
		cd ThemeToMySQL
		
		```
	6. Edit conf/config.ini.default file using your own mysql creditentials then rename it:
		
		```
		
		mv conf/config.ini.default conf/config.ini
		
		```

	7. Pull docker image:
	
	```
	
	docker pull szekrenyesi/theme2mysql:latest
	
	```
	
	
	8. Run the container:
	
	```
	
	docker run -d --rm --network="host" \
    --name theme2mysql \
    -v /path/on/host/data:/var/www/html/ThemeToMySQL/data \
    -v /path/on/host/conf:/var/www/html/ThemeToMySQL/conf \
    szekrenyesi/theme2mysql:latest
	
	```
	

	
	8. You can reach the webservice under localhost using port 8080:

		[http://localhost/ThemeToMySQL:8080](http://localhost/ThemeToMySQL:8080)
