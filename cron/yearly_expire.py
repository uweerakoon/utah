import ConfigParser
import MySQLdb
from datetime import date

config = ConfigParser.RawConfigParser()
config.read('/home/.control.cfg')

db = MySQLdb.connect(
    host=config.get('mysql', 'host'),
    user=config.get('mysql', 'role'),
    passwd=config.get('mysql', 'password'),
    db=config.get('mysql', 'database'))


class PreBurnAnnual:
    def __init__(self, db, override=False):
        # Imports db.
        # Allows manual 'override' for forcing re-run on a different date.
        self.db = db
        self.curs = self.db.cursor()
        if override:
            self.override = True
        else:
            self.override = False

    def check_date(self):
        # Verifies that the script is processing on the
        # first day of the new year.
        today = date.today()
        valid = date(today.year, 1, 1)

        if self.override:
            return True
        elif today == valid:
            return True
        else:
            return False

    def get_expired(self):
        # Collects tuple of all active burns that are expired.
        # This will collect anything that is older than the last year, even
        # though a pre_burn should not be active in this case.
        current_year = date.today().year

        self.curs.execute("""
            SELECT pre_burn_id FROM pre_burns
            WHERE year < %s AND active = 1;""", (current_year,))
        result = self.curs.fetchall()
        return result

    def deactivate(self):
        # Deactives all active PreBurns for a new year.
        if self.check_date():
            self.curs.executemany("""
                UPDATE pre_burns SET active = 0
                WHERE pre_burn_id = %s""", self.get_expired())
            return True
        else:
            return False


# Run the script
annual = PreBurnAnnual(db)
annual.deactivate()
