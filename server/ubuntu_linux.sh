#
#	Server Installation & Configuration 
#	
#	Notes: Using Ubuntu Linux
#

sudo apt-get update
sudo apt-get upgrade

#----------------------------------------------------------

#
#	Required: Install LAMP
#

sudo apt-get install tasksel
sudo tasksel install lamp-server
#sudo apt-get install phpmyadmin

# For Ubuntu 14.04 LTS
sudo apt-get install php5-mcrypt
sudo php5enmod mcrypt

##	Development server has default MySQL root password

#----------------------------------------------------------

#
#	Optional: Install PostgreSQL
#	-- Note we haven't used PostgreSQL, so none of the setup is detailed.

#sudo apt-get install postgresql

#----------------------------------------------------------

#
#	/var/www/ permissions
#	Assumes user is names airsci
#

sudo usermod -a -G airsci www-data		#	Adds existing user airsci to the www-data group.
sudo chown -R airsci:www-data /var/www/	#	Changes ownership of /var/www/ recursively to user airsci, group www-data.
sudo chmod -R 775 /var/www/				#	Changes permissions of /var/www/ to owner: read-write-execute(7), group: read-write-execute(7), everyone: read-execute(5) 
sudo chmod g+s /var/www/				#	Gives group ability to set SGID bit. This associates quotas to the group?

#----------------------------------------------------------

#
#
#	Create a MySQL user.
#

sudo su 								#	Change to root user necessary to add a MySQL user/role
mysql 									#	Invokes the mysql command console.

#	The following are SQL commands to be run in the mysql command console.
CREATE DATABASE `utahsms`;												#	Create the Utah.gov database.
CREATE USER `utahsms`@`localhost`;										#	Creates a user name airsci in localhost's mysql server.
SET PASSWORD FOR `utahsms`@`localhost` = PASSWORD('password');			#	Add password to the role.
GRANT INSERT, UPDATE, DELETE, LOCK TABLES, SELECT, TRIGGER ON `utahsms`.* TO `utahsms`@`localhost`;				#	Grand user airsci usage permissions on Utah.gov.

#----------------------------------------------------------

#
#	Install Postfix (SMTP)
#	https://rtcamp.com/tutorials/linux/ubuntu-postfix-gmail-smtp/
#

sudo apt-get install postfix mailutils libsasl2-2 ca-certificates libsasl2-modules

sudo nano /etc/postfix/main.cf

	relayhost = [smtp.gmail.com]:587
	smtp_sasl_auth_enable = yes
	smtp_sasl_password_maps = hash:/etc/postfix/sasl_passwd
	smtp_sasl_security_options = noanonymous
	smtp_tls_CAfile = /etc/postfix/cacert.pem
	smtp_use_tls = yes

sudo nano /etc/postfix/sasl_passwd

	[smtp.gmail.com]:587    USERNAME@gmail.com:PASSWORD
	
sudo chmod 400 /etc/postfix/sasl_passwd
sudo postmap /etc/postfix/sasl_passwd
cat /etc/ssl/certs/Thawte_Premium_Server_CA.pem | sudo tee -a /etc/postfix/cacert.pem
sudo /etc/init.d/postfix reload

echo "Test message from Digital Ocean." | mail -s "This is a test subject" jacob@airsci.com

#----------------------------------------------------------

#
#
#	Configure Uploads 
#

sudo mkdir /var/uploads/
sudo chown airsci:www-data /var/uploads/
sudo chmod -R 774 /var/uploads/

#----------------------------------------------------------

#
#	Install PIP, MySQL-Python (Crons)
#

sudo apt-get install python-pip python-dev
sudo apt-get install libmysqlclient-dev
pip install MySQL-python
python install.py
