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


class insertUsers:
    def __init__(self, db):
        self.db = db
        self.curs = self.db.cursor()
        self.file

    def import_csv(self, file):
        self.file = csv.reader(file, delimiter=',')
        print self.file
        return self.file

    def insert_user(self, email, full_name, level_id):
        # Inserts a new user
        password = self.gen_passwd()

        self.curs.executemany("""
            INSERT INTO users SET email = %s,
            full_name = %s, """, )
        return True

    def get_agency(self):

        return True

    def get_units(self):

        return True

    def gen_passwd(self):
        # Recieves a json object with temporary password insert requirements
        proc = subprocess.Popen(
            'php /home/scripts/users_crypt.php',
            shell=True, stdout=subprocess.PIPE
        )
        json_val = proc.stdout.read()
        parsed = json.loads(json_val)
        return parsed

    def sent_email(self):
        email = 'email'
