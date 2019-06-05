# ThemeToMySQL

This interface was created for importing T-patterns from [Theme](http://patternvision.com/) into a MySQL database. After the database is created, one can filter those patterns (with their locations) which are unique to the whole project. One can also download pattern locations in EAF format which makes possible to explore and link patterns to media files using the sofware environment of [ELAN](https://tla.mpi.nl/tools/tla-tools/elan/) annotation tool.

## Requirements
	
	- Apache 2.x
	- PHP 7.0
	- MySQL

## Installation (on Ubuntu 16)
	
	1. Update your system: 
		
		sudo apt-get update
		

	2. Installing Apache: 
		
		sudo apt-get install apache2
	
	3. Install MySQL:

		sudo apt-get install mysql-server
		
	4. Install php & some php moduls:

		sudo apt-get install php7.0 libapache2-mod-php7.0 php7.0-mcrypt php7.0-mysql
		sudo apt-get install php7.0-zip

	5. Clone the repository into your DocumentRoot folder (default is: /var/www/html):
		
		cd /path/to/your/documentroot/
		git clone https://github.com/szekrenyesi/ThemeToMySQL.gita

	6. Edit conf/config.ini.default file using your own mysql creditentials then rename it:
		
		mv conf/config.ini.default conf/config.ini
		
	7. Create database for users and projects:
		
		mysql -u root -p < mysql/projects.sql

	8. You can reach your site under localhost (if you have not public IP or domain):

		http://localhost/ThemeToMySQL

	
