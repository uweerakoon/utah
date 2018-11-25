import ConfigParser
import MySQLdb
from datetime import date
import csv
import subprocess
import json

config = ConfigParser.RawConfigParser()
config.read('/home/.control.cfg')

db = MySQLdb.connect(
    host=config.get('mysql', 'host'),
    user=config.get('mysql', 'role'),
    passwd=config.get('mysql', 'password'),
    db=config.get('mysql', 'database'))

cursor = db.cursor()


def check_pending_expirations(cursor):
    #
    # Determines if a burn is not longer usable
    #
    
    res = cursor.execute(
        """SELECT burn_id, start_date, DATE(now()) as today,
        DATEDIFF(now(), start_date) as days, expired
        FROM `burns`
        WHERE DATEDIFF(now(), start_date) > 14
        AND status_id < 5
        AND expired = FALSE;""")
        
    expired = cursor.fetchall()
    
    return expired


def expire_burns(cursor):
    #
    # Expire all burns that are older than.
    #
    
    expire = cursor.execute(
        """UPDATE `burns` SET expired = TRUE
        WHERE DATEDIFF(now(), start_date) > 14
        AND status_id < 5
        AND expired = FALSE;""")


# Run the actual expiration.
expire_burns(cursor)
