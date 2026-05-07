#!/bin/bash
# Test calendar endpoint with curl (requires running server)

BASE_URL="http://localhost/api/calendar/events"

# Current month range (ISO format like FullCalendar sends)
START=$(date -d "$(date +%Y-%m-01)" +"%Y-%m-%dT00:00:00+00:00")
END=$(date -d "$(date +%Y-%m-01 +%s) +1 month -1 day" +"%Y-%m-%dT23:59:59+00:00")

echo "Testing calendar endpoint..."
echo "URL: $BASE_URL"
echo "Parameters:"
echo "  start: $START"
echo "  end: $END"
echo ""

# Test without authentication (should fail)
echo "1️⃣ Without authentication:"
curl -s "$BASE_URL?start=$START&end=$END" -H "Accept: application/json" | head -c 200
echo ""
echo ""

# Test with cookie (requires login first - use admin cookies if available)
echo "2️⃣ With session (if available):"
echo "(This would work in a logged-in session)"
echo ""
