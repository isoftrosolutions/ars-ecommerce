#!/bin/bash
# ARS Easy Shopping — Mobile API v1 Smoke Test
# Usage: bash api/v1/test_smoke.sh
# Requires: curl, jq (optional for pretty output)

BASE_URL="${BASE_URL:-https://easyshoppingars.com/api/v1}"
PASS=0
FAIL=0
TEST_PHONE="9812345678"
TEST_PASSWORD="testpass123"
TOKEN=""

green() { printf "\033[32m%s\033[0m\n" "$1"; }
red() { printf "\033[31m%s\033[0m\n" "$1"; }
blue() { printf "\033[34m%s\033[0m\n" "$1"; }

blue "=== ARS Mobile API v1 — Smoke Test ==="
blue "Base URL: $BASE_URL"
echo ""

# Test 1: GET /products
blue "Test 1: GET /products"
RESP=$(curl -s "$BASE_URL/products?limit=1")
if echo "$RESP" | grep -q '"success": true'; then
    green "  PASS"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 2: GET /categories
blue "Test 2: GET /categories"
RESP=$(curl -s "$BASE_URL/categories")
if echo "$RESP" | grep -q '"success": true'; then
    green "  PASS"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 3: GET /banners
blue "Test 3: GET /banners"
RESP=$(curl -s "$BASE_URL/banners")
if echo "$RESP" | grep -q '"success": true'; then
    green "  PASS"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 4: GET /products/featured
blue "Test 4: GET /products/featured"
RESP=$(curl -s "$BASE_URL/products/featured")
if echo "$RESP" | grep -q '"success": true'; then
    green "  PASS"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 5: GET /products/new-arrivals
blue "Test 5: GET /products/new-arrivals"
RESP=$(curl -s "$BASE_URL/products/new-arrivals")
if echo "$RESP" | grep -q '"success": true'; then
    green "  PASS"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 6: POST /auth/register
blue "Test 6: POST /auth/register"
RESP=$(curl -s -X POST "$BASE_URL/auth/register" \
    -H "Content-Type: application/json" \
    -d "{\"name\":\"Smoke Test\",\"phone\":\"$TEST_PHONE\",\"password\":\"$TEST_PASSWORD\"}")
if echo "$RESP" | grep -q '"otp_sent": true'; then
    green "  PASS"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 7: POST /auth/resend-otp
blue "Test 7: POST /auth/resend-otp"
RESP=$(curl -s -X POST "$BASE_URL/auth/resend-otp" \
    -H "Content-Type: application/json" \
    -d "{\"phone\":\"$TEST_PHONE\"}")
if echo "$RESP" | grep -q '"otp_sent": true'; then
    green "  PASS"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 8: POST /auth/login (should fail — not verified)
blue "Test 8: POST /auth/login (unverified — expect 403)"
RESP=$(curl -s -X POST "$BASE_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d "{\"phone\":\"$TEST_PHONE\",\"password\":\"$TEST_PASSWORD\"}")
if echo "$RESP" | grep -q 'success.*false'; then
    green "  PASS (got error as expected)"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 9: Protected endpoint without token (expect 401)
blue "Test 9: GET /user/me without token — expect 401"
RESP=$(curl -s "$BASE_URL/user/me")
if echo "$RESP" | grep -q 'success.*false'; then
    green "  PASS (401 as expected)"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

# Test 10: OPTIONS preflight
blue "Test 10: OPTIONS /products"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -X OPTIONS "$BASE_URL/products")
if [ "$HTTP_CODE" = "200" ]; then
    green "  PASS (HTTP $HTTP_CODE)"
    PASS=$((PASS+1))
else
    red "  FAIL (HTTP $HTTP_CODE)"
    FAIL=$((FAIL+1))
fi

# Test 11: 404 endpoint
blue "Test 11: GET /nonexistent — expect 404"
RESP=$(curl -s "$BASE_URL/nonexistent")
if echo "$RESP" | grep -q 'success.*false'; then
    green "  PASS (404 as expected)"
    PASS=$((PASS+1))
else
    red "  FAIL — $RESP"
    FAIL=$((FAIL+1))
fi

echo ""
blue "=== Results ==="
green "Passed: $PASS"
red "Failed: $FAIL"
echo ""

# Grab first product ID for detailed test
PRODUCT_ID=$(curl -s "$BASE_URL/products?limit=1" | grep -o '"id": [0-9]*' | head -1 | grep -o '[0-9]*')
if [ -n "$PRODUCT_ID" ]; then
    blue "Bonus: GET /products/$PRODUCT_ID"
    RESP=$(curl -s "$BASE_URL/products/$PRODUCT_ID")
    if echo "$RESP" | grep -q '"success": true'; then
        green "  PASS"
    else
        red "  FAIL"
    fi
fi

exit $FAIL
