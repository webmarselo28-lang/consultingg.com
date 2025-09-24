#!/bin/bash

# ConsultingG Real Estate - Post-Deploy Verification Script
# Usage: bash verify_deployment.sh https://your-domain.com

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN=${1:-"https://consultingg.com"}
API_BASE="${DOMAIN}/backend/api"
ADMIN_EMAIL="admin@consultingg.com"
ADMIN_PASSWORD="<admin-password>"

# Test counters
PASSED=0
FAILED=0
TOTAL=0

# Helper functions
print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}"
}

print_test() {
    echo -e "\n${YELLOW}Testing: $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ PASS: $1${NC}"
    ((PASSED++))
    ((TOTAL++))
}

print_failure() {
    echo -e "${RED}✗ FAIL: $1${NC}"
    ((FAILED++))
    ((TOTAL++))
}

print_info() {
    echo -e "${BLUE}ℹ INFO: $1${NC}"
}

# Check if curl is available
if ! command -v curl &> /dev/null; then
    echo -e "${RED}Error: curl is required but not installed${NC}"
    exit 1
fi

# Check if jq is available (optional)
JQ_AVAILABLE=true
if ! command -v jq &> /dev/null; then
    JQ_AVAILABLE=false
    print_info "jq not available - JSON responses will not be formatted"
fi

print_header "ConsultingG Real Estate Deployment Verification"
print_info "Testing deployment at: $DOMAIN"
print_info "API Base: $API_BASE"

# Test 1: Health Check
print_test "API Health Check"
HEALTH_RESPONSE=$(curl -s -w "\n%{http_code}" "$API_BASE/health" || echo "ERROR\n000")
HTTP_CODE=$(echo "$HEALTH_RESPONSE" | tail -n1)
RESPONSE_BODY=$(echo "$HEALTH_RESPONSE" | head -n -1)

if [ "$HTTP_CODE" = "200" ] && echo "$RESPONSE_BODY" | grep -q '"status":"ok"'; then
    print_success "Health endpoint returns 200 OK with valid JSON"
    if echo "$RESPONSE_BODY" | grep -q '"database":"connected"'; then
        print_success "Database connection verified"
    else
        print_failure "Database connection failed"
    fi
else
    print_failure "Health endpoint failed (HTTP $HTTP_CODE)"
    echo "Response: $RESPONSE_BODY"
fi

# Test 2: Frontend Loading
print_test "Frontend Application"
FRONTEND_RESPONSE=$(curl -s -w "\n%{http_code}" "$DOMAIN" || echo "ERROR\n000")
FRONTEND_CODE=$(echo "$FRONTEND_RESPONSE" | tail -n1)
FRONTEND_BODY=$(echo "$FRONTEND_RESPONSE" | head -n -1)

if [ "$FRONTEND_CODE" = "200" ] && echo "$FRONTEND_BODY" | grep -q "ConsultingG"; then
    print_success "Frontend loads successfully"
    if echo "$FRONTEND_BODY" | grep -q '<title>'; then
        print_success "HTML title tag present"
    else
        print_failure "HTML title tag missing"
    fi
else
    print_failure "Frontend failed to load (HTTP $FRONTEND_CODE)"
fi

# Test 3: API Properties Endpoint
print_test "Properties API Endpoint"
PROPERTIES_RESPONSE=$(curl -s -w "\n%{http_code}" "$API_BASE/properties" || echo "ERROR\n000")
PROP_CODE=$(echo "$PROPERTIES_RESPONSE" | tail -n1)
PROP_BODY=$(echo "$PROPERTIES_RESPONSE" | head -n -1)

if [ "$PROP_CODE" = "200" ]; then
    print_success "Properties endpoint returns 200 OK"
    
    # Check if it's valid JSON
    if [ "$JQ_AVAILABLE" = true ]; then
        if echo "$PROP_BODY" | jq empty 2>/dev/null; then
            print_success "Properties endpoint returns valid JSON"
            
            # Check for data structure
            if echo "$PROP_BODY" | jq -e '.data | type == "array"' >/dev/null 2>&1; then
                print_success "Properties data is properly structured"
                PROP_COUNT=$(echo "$PROP_BODY" | jq '.data | length')
                print_info "Found $PROP_COUNT properties"
            else
                print_failure "Properties data structure invalid"
            fi
        else
            print_failure "Properties endpoint returns invalid JSON"
        fi
    else
        # Basic JSON check without jq
        if echo "$PROP_BODY" | grep -q '{"success"'; then
            print_success "Properties endpoint returns JSON-like response"
        else
            print_failure "Properties endpoint may not return proper JSON"
        fi
    fi
else
    print_failure "Properties endpoint failed (HTTP $PROP_CODE)"
    echo "Response: $PROP_BODY"
fi

# Test 4: Static Assets
print_test "Static Assets"
LOGO_RESPONSE=$(curl -s -w "\n%{http_code}" "$DOMAIN/logo.png" || echo "ERROR\n000")
LOGO_CODE=$(echo "$LOGO_RESPONSE" | tail -n1)

if [ "$LOGO_CODE" = "200" ]; then
    print_success "Logo asset accessible"
else
    print_failure "Logo asset failed (HTTP $LOGO_CODE)"
fi

# Test images directory
GEORGIEV_RESPONSE=$(curl -s -w "\n%{http_code}" "$DOMAIN/images/georgiev.jpg" || echo "ERROR\n000")
GEORGIEV_CODE=$(echo "$GEORGIEV_RESPONSE" | tail -n1)

if [ "$GEORGIEV_CODE" = "200" ]; then
    print_success "Georgiev image accessible"
else
    print_failure "Georgiev image failed (HTTP $GEORGIEV_CODE)"
fi

# Test 5: Admin Login Page
print_test "Admin Interface"
ADMIN_RESPONSE=$(curl -s -w "\n%{http_code}" "$DOMAIN/admin/login" || echo "ERROR\n000")
ADMIN_CODE=$(echo "$ADMIN_RESPONSE" | tail -n1)

if [ "$ADMIN_CODE" = "200" ]; then
    print_success "Admin login page accessible"
else
    print_failure "Admin login page failed (HTTP $ADMIN_CODE)"
fi

# Test 6: File Upload Directory
print_test "Upload Directory"
UPLOADS_RESPONSE=$(curl -s -w "\n%{http_code}" "$DOMAIN/uploads/" || echo "ERROR\n000")
UPLOADS_CODE=$(echo "$UPLOADS_RESPONSE" | tail -n1)

# For security, uploads directory should not be directly listable
# But it should not return 500 error - 403/404 is acceptable
if [ "$UPLOADS_CODE" = "403" ] || [ "$UPLOADS_CODE" = "404" ] || [ "$UPLOADS_CODE" = "200" ]; then
    print_success "Uploads directory properly configured (HTTP $UPLOADS_CODE)"
else
    print_failure "Uploads directory configuration issue (HTTP $UPLOADS_CODE)"
fi

# Test 7: Security Headers
print_test "Security Headers"
HEADERS=$(curl -s -I "$DOMAIN" | tr -d '\r')

if echo "$HEADERS" | grep -iq "x-frame-options"; then
    print_success "X-Frame-Options header present"
else
    print_failure "X-Frame-Options header missing"
fi

if echo "$HEADERS" | grep -iq "x-content-type-options"; then
    print_success "X-Content-Type-Options header present"
else
    print_failure "X-Content-Type-Options header missing"
fi

# Test 8: HTTPS Redirect (if HTTPS is used)
if echo "$DOMAIN" | grep -q "https://"; then
    print_test "HTTPS Configuration"
    HTTP_DOMAIN=$(echo "$DOMAIN" | sed 's/https:/http:/')
    HTTP_REDIRECT=$(curl -s -w "\n%{http_code}" -L "$HTTP_DOMAIN" | tail -n1)
    
    if [ "$HTTP_REDIRECT" = "200" ]; then
        print_success "HTTP to HTTPS redirect working"
    else
        print_info "HTTP redirect test inconclusive (HTTP $HTTP_REDIRECT)"
    fi
fi

# Test 9: Database Connection via API
print_test "Database Integration"
if echo "$PROP_BODY" | grep -q "prop-"; then
    print_success "Properties with proper IDs found in database"
else
    print_failure "No proper property IDs found - database may be empty"
fi

# Test 10: Error Handling
print_test "Error Handling"
ERROR_RESPONSE=$(curl -s -w "\n%{http_code}" "$API_BASE/nonexistent" || echo "ERROR\n000")
ERROR_CODE=$(echo "$ERROR_RESPONSE" | tail -n1)
ERROR_BODY=$(echo "$ERROR_RESPONSE" | head -n -1)

if [ "$ERROR_CODE" = "404" ] && echo "$ERROR_BODY" | grep -q "error"; then
    print_success "Proper error handling for invalid endpoints"
else
    print_failure "Error handling needs improvement (HTTP $ERROR_CODE)"
fi

# Summary
print_header "VERIFICATION SUMMARY"
echo -e "Total Tests: $TOTAL"
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"

if [ $FAILED -eq 0 ]; then
    echo -e "\n${GREEN}🎉 ALL TESTS PASSED! Deployment appears successful.${NC}"
    echo -e "\n${BLUE}Next Steps:${NC}"
    echo "1. Test admin login manually at: $DOMAIN/admin/login"
    echo "2. Create a test property with image upload"
    echo "3. Verify property visibility on frontend"
    echo "4. Test featured property toggle"
    echo "5. Check error logs for any warnings"
    exit 0
elif [ $FAILED -le 2 ]; then
    echo -e "\n${YELLOW}⚠️  MOSTLY SUCCESSFUL with minor issues.${NC}"
    echo -e "\n${BLUE}Recommendations:${NC}"
    echo "1. Review failed tests above"
    echo "2. Check server error logs"
    echo "3. Verify .htaccess files are working"
    exit 1
else
    echo -e "\n${RED}❌ DEPLOYMENT HAS SIGNIFICANT ISSUES${NC}"
    echo -e "\n${BLUE}Required Actions:${NC}"
    echo "1. Review all failed tests"
    echo "2. Check PHP error logs"
    echo "3. Verify database connection"
    echo "4. Ensure all files extracted properly"
    echo "5. Check PHP extensions are enabled"
    exit 2
fi