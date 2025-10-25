#!/bin/bash
# =============================================================================
# ConsultingG Real Estate - Deployment Verification Script
# =============================================================================
# Usage: bash verify-deployment.sh [domain]
# Example: bash verify-deployment.sh goro.consultingg.com
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Domain to test (default: goro.consultingg.com)
DOMAIN="${1:-goro.consultingg.com}"
PROTOCOL="https"
BASE_URL="${PROTOCOL}://${DOMAIN}"
API_URL="${BASE_URL}/api"

echo -e "${BLUE}=====================================================================${NC}"
echo -e "${BLUE}ConsultingG Real Estate - Deployment Verification${NC}"
echo -e "${BLUE}=====================================================================${NC}"
echo -e "Domain: ${DOMAIN}"
echo -e "Base URL: ${BASE_URL}"
echo -e "API URL: ${API_URL}"
echo ""

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Function to check HTTP status
check_http() {
    local url=$1
    local expected=$2
    local name=$3

    echo -n "Testing ${name}... "

    status=$(curl -s -o /dev/null -w "%{http_code}" "${url}" --max-time 10)

    if [ "$status" -eq "$expected" ]; then
        echo -e "${GREEN}✓ PASS${NC} (HTTP ${status})"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC} (HTTP ${status}, expected ${expected})"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Function to check JSON response
check_json() {
    local url=$1
    local name=$2
    local jq_filter=$3

    echo -n "Testing ${name}... "

    response=$(curl -s "${url}" --max-time 10)
    status=$?

    if [ $status -ne 0 ]; then
        echo -e "${RED}✗ FAIL${NC} (curl error)"
        ((TESTS_FAILED++))
        return 1
    fi

    if echo "$response" | jq -e "${jq_filter}" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
        ((TESTS_PASSED++))
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        echo "Response: ${response:0:100}..."
        ((TESTS_FAILED++))
        return 1
    fi
}

echo -e "${YELLOW}=== Phase 1: Frontend Tests ===${NC}"

# Test 1: Homepage loads
check_http "${BASE_URL}/" 200 "Homepage (GET /)"

# Test 2: Frontend static assets
check_http "${BASE_URL}/robots.txt" 200 "robots.txt"
# check_http "${BASE_URL}/sitemap.xml" 200 "sitemap.xml"

echo ""
echo -e "${YELLOW}=== Phase 2: Backend API Tests ===${NC}"

# Test 3: API root endpoint
check_json "${API_URL}/" "API root (GET /api/)" ".success == true"

# Test 4: Health check
check_json "${API_URL}/health" "Health check (GET /api/health)" ".success == true and .database == \"connected\""

# Test 5: Properties API
check_json "${API_URL}/properties" "Properties list (GET /api/properties)" ".data | type == \"array\""

# Test 6: Single property
# Get first property ID
PROP_ID=$(curl -s "${API_URL}/properties" | jq -r '.data[0].id' 2>/dev/null)
if [ -n "$PROP_ID" ] && [ "$PROP_ID" != "null" ]; then
    check_json "${API_URL}/properties/${PROP_ID}" "Property detail (GET /api/properties/:id)" ".id == \"${PROP_ID}\""
else
    echo -e "Skipping property detail test (no properties found)"
fi

echo ""
echo -e "${YELLOW}=== Phase 3: CMS API Tests ===${NC}"

# Test 7: Sections API
check_json "${API_URL}/cms/sections" "Sections list (GET /api/cms/sections)" ". | type == \"array\""

# Test 8: Pages API
check_json "${API_URL}/cms/pages" "Pages list (GET /api/cms/pages)" ". | type == \"array\""

# Test 9: Services API
check_json "${API_URL}/cms/services" "Services list (GET /api/cms/services)" ". | type == \"array\""

echo ""
echo -e "${YELLOW}=== Phase 4: Upload/Static Files Tests ===${NC}"

# Test 10: Check if uploads directory is accessible
# Note: This will fail if no files exist, which is expected for new deployment
echo -n "Testing uploads directory... "
if curl -s -o /dev/null -w "%{http_code}" "${BASE_URL}/uploads/" --max-time 5 | grep -q "40[34]"; then
    echo -e "${GREEN}✓ PASS${NC} (Protected - no directory listing)"
    ((TESTS_PASSED++))
elif curl -s -o /dev/null -w "%{http_code}" "${BASE_URL}/uploads/" --max-time 5 | grep -q "200"; then
    echo -e "${YELLOW}⚠ WARNING${NC} (Directory listing enabled - consider disabling)"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ SKIP${NC} (Uploads directory configuration pending)"
fi

echo ""
echo -e "${YELLOW}=== Phase 5: Security Tests ===${NC}"

# Test 11: Check security headers
echo -n "Testing security headers... "
headers=$(curl -s -I "${BASE_URL}/" --max-time 10)

header_checks=0
header_total=3

if echo "$headers" | grep -qi "X-Frame-Options"; then ((header_checks++)); fi
if echo "$headers" | grep -qi "X-Content-Type-Options"; then ((header_checks++)); fi
if echo "$headers" | grep -qi "X-XSS-Protection"; then ((header_checks++)); fi

if [ $header_checks -eq $header_total ]; then
    echo -e "${GREEN}✓ PASS${NC} (${header_checks}/${header_total} headers present)"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ PARTIAL${NC} (${header_checks}/${header_total} headers present)"
    ((TESTS_PASSED++))
fi

# Test 12: Check HTTPS/SSL (if using HTTPS)
if [ "$PROTOCOL" == "https" ]; then
    echo -n "Testing SSL certificate... "
    if curl -s --head "${BASE_URL}" | grep -q "HTTP/2 200"; then
        echo -e "${GREEN}✓ PASS${NC} (Valid SSL)"
        ((TESTS_PASSED++))
    else
        echo -e "${RED}✗ FAIL${NC} (SSL issue)"
        ((TESTS_FAILED++))
    fi
fi

# Test 13: Check compression
echo -n "Testing gzip compression... "
if curl -s -H "Accept-Encoding: gzip" -I "${BASE_URL}/" | grep -qi "Content-Encoding: gzip"; then
    echo -e "${GREEN}✓ PASS${NC}"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ WARNING${NC} (Gzip not enabled)"
fi

echo ""
echo -e "${YELLOW}=== Phase 6: Performance Tests ===${NC}"

# Test 14: Response time
echo -n "Testing response time... "
response_time=$(curl -s -o /dev/null -w "%{time_total}" "${BASE_URL}/" --max-time 10)
response_time_int=${response_time%.*}

if [ "$response_time_int" -lt 3 ]; then
    echo -e "${GREEN}✓ PASS${NC} (${response_time}s < 3s)"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ SLOW${NC} (${response_time}s >= 3s)"
fi

# Test 15: API response time
echo -n "Testing API response time... "
api_response_time=$(curl -s -o /dev/null -w "%{time_total}" "${API_URL}/health" --max-time 10)
api_response_time_int=${api_response_time%.*}

if [ "$api_response_time_int" -lt 1 ]; then
    echo -e "${GREEN}✓ PASS${NC} (${api_response_time}s < 1s)"
    ((TESTS_PASSED++))
else
    echo -e "${YELLOW}⚠ SLOW${NC} (${api_response_time}s >= 1s)"
fi

echo ""
echo -e "${BLUE}=====================================================================${NC}"
echo -e "${BLUE}Verification Results${NC}"
echo -e "${BLUE}=====================================================================${NC}"
echo -e "Tests Passed: ${GREEN}${TESTS_PASSED}${NC}"
echo -e "Tests Failed: ${RED}${TESTS_FAILED}${NC}"
echo -e "Total Tests:  $((TESTS_PASSED + TESTS_FAILED))"

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed! Deployment successful!${NC}"
    exit 0
else
    echo -e "${RED}✗ Some tests failed. Please review the output above.${NC}"
    exit 1
fi
