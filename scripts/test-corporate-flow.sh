#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_ROOT"

PASS_COUNT=0
SKIP_COUNT=0
FAIL_COUNT=0

pass() {
    PASS_COUNT=$((PASS_COUNT + 1))
    printf '[PASS] %s\n' "$1"
}

skip() {
    SKIP_COUNT=$((SKIP_COUNT + 1))
    printf '[SKIP] %s\n' "$1"
}

fail() {
    FAIL_COUNT=$((FAIL_COUNT + 1))
    printf '[FAIL] %s\n' "$1" >&2
}

run_check() {
    local label="$1"
    shift
    if "$@" >/tmp/corporate-flow-test.out 2>&1; then
        pass "$label"
    else
        cat /tmp/corporate-flow-test.out >&2
        fail "$label"
    fi
}

printf 'Corporate flow smoke test\n'
printf 'Application: %s\n\n' "$APP_ROOT"

if [[ ! -f .env ]]; then
    fail '.env is missing'
else
    pass '.env is present'
fi

run_check 'ClasController PHP syntax' php -l app/Http/Controllers/ClasController.php
run_check 'CorporateProposalController PHP syntax' php -l app/Http/Controllers/Admin/CorporateProposalController.php

for view in \
    resources/views/admin/classes/create.blade.php \
    resources/views/admin/classes/edit.blade.php \
    resources/views/admin/classes/partials/corporate-fields.blade.php \
    resources/views/admin/classes/show-corporate.blade.php
do
    if [[ -f "$view" ]]; then
        pass "required view exists: $view"
    else
        fail "required view is missing: $view"
    fi
done

run_check 'Blade templates compile' php artisan view:cache --no-ansi
run_check 'Migration status is available' php artisan migrate:status --no-ansi
run_check 'Corporate routes are registered' bash -c \
    "php artisan route:list --no-ansi | grep -E 'corporate|classes.(create|store|edit|update|show)'"

if grep -q 'corporate_step' resources/views/admin/classes/partials/corporate-fields.blade.php \
    && grep -q 'corporate-decision-section' resources/views/admin/classes/partials/corporate-fields.blade.php
then
    pass 'Corporate Step 1/Step 2 separation is present'
else
    fail 'Corporate Step 1/Step 2 separation is missing'
fi

if grep -q 'schedule-section' resources/views/admin/classes/create.blade.php \
    && grep -q 'isCorporate' resources/views/admin/classes/create.blade.php
then
    pass 'Corporate schedule visibility guard is present'
else
    fail 'Corporate schedule visibility guard is missing'
fi

if [[ -d tests ]]; then
    run_check 'PHPUnit/Laravel tests' php artisan test --no-ansi
else
    skip 'tests directory is not present; no automated PHPUnit tests to run'
fi

rm -f /tmp/corporate-flow-test.out

printf '\nSummary: %s passed, %s skipped, %s failed\n' \
    "$PASS_COUNT" "$SKIP_COUNT" "$FAIL_COUNT"

if (( FAIL_COUNT > 0 )); then
    exit 1
fi

printf 'Backend/view smoke checks passed.\n'
printf 'Browser clicks for Corporate Step 1-4 and non-Corporate still require an E2E/browser test.\n'
