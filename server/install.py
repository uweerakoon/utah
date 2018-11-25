import ConfigParser

# 
#	Write the .control.cfg file for database operation.
#

config = ConfigParser.RawConfigParser()

config.add_section('mysql')
config.set('mysql', 'host', 'localhost')
config.set('mysql', 'role', 'utahsms')
config.set('mysql', 'database', 'utahsms')
config.set('mysql', 'password', 'PASSWORD_HERE')

with open('/home/.control.cfg', 'wb') as configfile:
    config.write(configfile)