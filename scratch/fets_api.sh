curl -H "Authorization: 401c5f50b490e3ffc52f0211669eaceac753abbb2b479bc257dcf035bf099f2e" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -X POST https://utahdts.airsci.com/api/fets.php \
  --data-urlencode "start_date=2017-01-01" \
  --data-urlencode "end_date=2017-01-02"


# Test Invalid Auth
curl -H "Authorization: wakawaka" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -X POST https://utahdts.airsci.com/api/fets.php \
  --data-urlencode "start_date=2017-01-01" \
  --data-urlencode "end_date=2017-01-15"


# Test Missing Param(s)
curl -H "Authorization: 401c5f50b490e3ffc52f0211669eaceac753abbb2b479bc257dcf035bf099f2e" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -X POST https://utahdts.airsci.com/api/fets.php \
  --data-urlencode "start_date=2017-01-01"


curl -H "Authorization: 401c5f50b490e3ffc52f0211669eaceac753abbb2b479bc257dcf035bf099f2e" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -X POST https://utahdts.airsci.com/api/fets.php \
  --data-urlencode "end_date=2017-01-15"


curl -H "Authorization: 401c5f50b490e3ffc52f0211669eaceac753abbb2b479bc257dcf035bf099f2e" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -X POST https://utahdts.airsci.com/api/fets.php


# Test Invalid Param(s)
curl -H "Authorization: 401c5f50b490e3ffc52f0211669eaceac753abbb2b479bc257dcf035bf099f2e" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -X POST https://utahdts.airsci.com/api/fets.php \
  --data-urlencode "start_date=pea" \
  --data-urlencode "end_date=nuts"
