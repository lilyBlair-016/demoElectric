<?php

// Run from the project root: php tests/customer_accounts_smoke.php
// Uses uniquely named user/customer records and removes them in finally.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$base = 'http://localhost/demoElectric/';
$db = new mysqli('localhost', 'root', '', 'electric_company', 3306);
$db->set_charset('utf8mb4');
$authDb = new mysqli('localhost', 'root', '', 'electriccompany', 3306);
$authDb->set_charset('utf8mb4');
$usersBefore = $authDb->query('SELECT * FROM users ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$before = $db->query('SELECT * FROM customer_accounts ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$number = 'SMOKE-' . bin2hex(random_bytes(8));
$email = strtolower($number) . '@example.com';
$password = bin2hex(random_bytes(16));
$recordId = null;
$client = curl_init();
curl_setopt_array($client, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 15]);

function check(bool $condition, string $label): void
{
    if (! $condition) {
        throw new RuntimeException('FAIL: ' . $label);
    }
    echo 'PASS: ' . $label . PHP_EOL;
}

function request(string $path, ?array $post = null): array
{
    global $client, $base;
    $headers = [];
    curl_setopt($client, CURLOPT_URL, $base . $path);
    curl_setopt($client, CURLOPT_HEADERFUNCTION, static function ($curl, $line) use (&$headers) {
        if (str_contains($line, ':')) {
            [$key, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($key))] = trim($value);
        }
        return strlen($line);
    });
    curl_setopt($client, CURLOPT_POST, $post !== null);
    if ($post !== null) {
        curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($client);
    if ($body === false) {
        throw new RuntimeException(curl_error($client));
    }
    return ['status' => curl_getinfo($client, CURLINFO_HTTP_CODE), 'body' => $body, 'headers' => $headers];
}

function csrf(array $response): array
{
    if (! preg_match('/name="csrf_test_name" value="([^"]+)"/', $response['body'], $match)) {
        throw new RuntimeException('Missing CSRF token. HTTP status ' . $response['status']);
    }
    return ['csrf_test_name' => $match[1]];
}

function redirectsTo(array $response, string $path): bool
{
    return in_array($response['status'], [302, 303], true)
        && str_ends_with($response['headers']['location'] ?? '', '/' . $path);
}

function rowIds(array $response): array
{
    preg_match_all('/<tr>\s*<td>(\d+)<\/td>/', $response['body'], $matches);
    return $matches[1];
}

try {
    foreach (['', 'about', 'services', 'contact', 'register', 'login'] as $path) {
        check(request($path)['status'] === 200, 'Public page /' . $path);
    }
    $home = request('');
    check(str_contains($home['body'], 'Powering Your World with Excellence') && str_contains($home['body'], '>Login</a>'), 'Original landing content and Login link');
    check(request('assets/css/custom.css')['status'] === 200 && request('assets/js/app.js')['status'] === 200, 'Existing landing assets load');
    check(redirectsTo(request('contact', []), 'contact'), 'Contact validation redirects without a type error');
    check(str_contains(request('contact')['body'], 'is-invalid'), 'Contact form displays validation');
    $contact = ['name' => 'Smoke Test', 'email' => 'smoke@example.com', 'phone' => '09123456789', 'service_type' => 'residential', 'message' => 'Local demo contact form smoke test.'];
    check(redirectsTo(request('contact', $contact), 'contact'), 'Existing demo contact submission succeeds');
    check(str_contains(request('contact')['body'], 'Thank you for your message!'), 'Contact success message');
    check(redirectsTo(request('register', []), 'register'), 'Registration validation redirects');
    check(str_contains(request('register')['body'], 'is-invalid'), 'Registration form displays validation');
    $registration = ['first_name' => 'Smoke', 'last_name' => 'Test', 'email' => $email, 'phone' => '09123456789', 'address' => 'Smoke test address', 'city' => 'Manila', 'state' => 'Metro Manila', 'zip_code' => '12345', 'password' => $password, 'confirm_password' => $password, 'terms' => '1'];
    $form = request('register');
    check(redirectsTo(request('register', csrf($form) + $registration), 'register'), 'Registration redirects');
    check(str_contains(request('register')['body'], 'Registration successful!'), 'Registration succeeds using auth connection');
    $authUser = $authDb->query("SELECT * FROM users WHERE email = '" . $authDb->real_escape_string($email) . "'")->fetch_assoc();
    check($authUser !== null && password_verify($password, $authUser['password']), 'User stored in electriccompany.users with hashed password');
    $form = request('register');
    check(redirectsTo(request('register', csrf($form) + $registration), 'register'), 'Duplicate registration redirects');
    check(str_contains(request('register')['body'], 'must contain a unique value'), 'Email uniqueness checks the auth database');
    check($authDb->query('SHOW TABLES LIKE \'customer_accounts\'')->num_rows === 0 && $db->query('SHOW TABLES LIKE \'users\'')->num_rows === 0, 'User and customer tables remain in separate databases');
    check(request('.env')['status'] === 403, 'Environment file blocked by Apache');
    foreach (['dashboard', 'customer-accounts', 'customer-accounts/create', 'customer-accounts/edit/1', 'customer-accounts/view/1'] as $path) {
        check(redirectsTo(request($path), 'login'), 'Guest blocked from ' . $path);
    }
    foreach (['customer-accounts/store', 'customer-accounts/update/1', 'customer-accounts/delete/1'] as $path) {
        check(redirectsTo(request($path, []), 'login'), 'Guest POST blocked from ' . $path);
    }

    $login = request('login');
    $failed = request('login', csrf($login) + ['email' => $email, 'password' => 'wrong']);
    check(redirectsTo($failed, 'login'), 'Invalid login redirects');
    $login = request('login');
    check(str_contains($login['body'], 'Invalid email or password'), 'Invalid login displays error');
    check(redirectsTo(request('login', csrf($login) + ['email' => 'unknown-' . $email, 'password' => $password]), 'login'), 'Unknown email rejected');
    $login = request('login');
    check(redirectsTo(request('login', csrf($login) + ['email' => $email, 'password' => $authUser['password']]), 'login'), 'Stored hash cannot be used as a plaintext password');
    $login = request('login');
    $oldCookies = curl_getinfo($client, CURLINFO_COOKIELIST);
    $success = request('login', csrf($login) + ['email' => $email, 'password' => $password]);
    check(redirectsTo($success, 'dashboard'), 'Valid login redirects to dashboard');
    check($oldCookies !== curl_getinfo($client, CURLINFO_COOKIELIST), 'Session identifier regenerated on login');
    check(redirectsTo(request('login'), 'dashboard'), 'Signed-in login redirects to dashboard');

    $dashboard = request('dashboard');
    check($dashboard['status'] === 200 && str_contains($dashboard['body'], 'Signed in as Smoke (' . $email . ')'), 'Authenticated dashboard renders database user identity');
    check(str_contains($dashboard['headers']['cache-control'] ?? '', 'no-store'), 'Protected response disables caching');
    $seen = [];
    for ($page = 1; $page <= max(1, (int) ceil(count($before) / 10)); $page++) {
        $list = request('customer-accounts?page=' . $page);
        $ids = rowIds($list);
        check($list['status'] === 200 && count($ids) === min(10, max(0, count($before) - (($page - 1) * 10))), 'Pagination page ' . $page);
        $seen = array_merge($seen, $ids);
    }
    check(count(array_unique($seen)) === count($before), 'Pagination visits every account without overlaps');
    check(count($before) <= 10 || str_contains($dashboard['body'], 'page=2'), 'Pager renders page links when needed');

    $data = ['account_number' => $number, 'customer_name' => 'Smoke <script>test</script>', 'address' => 'Test address', 'email' => '', 'phone' => '', 'meter_number' => '', 'connection_type' => 'residential', 'status' => 'active'];
    $form = request('customer-accounts/create');
    check($form['status'] === 200, 'Create form opens');
    $noToken = request('customer-accounts/store', $data);
    check($noToken['status'] === 403 || redirectsTo($noToken, 'customer-accounts/create'), 'Missing CSRF token rejected');
    $form = request('customer-accounts/create');
    $invalid = array_replace($data, ['account_number' => '', 'customer_name' => '', 'address' => '', 'email' => 'bad-email', 'connection_type' => 'invalid', 'status' => 'invalid']);
    check(redirectsTo(request('customer-accounts/store', csrf($form) + $invalid), 'customer-accounts/create'), 'Invalid create redirects to form');
    $form = request('customer-accounts/create');
    foreach (['Account number', 'Customer name', 'Address', 'Email', 'Connection type', 'Status'] as $label) {
        check(str_contains($form['body'], $label . ' field'), 'Validation reports ' . $label);
    }
    check(redirectsTo(request('customer-accounts/store', csrf($form) + $data), 'customer-accounts'), 'Create customer succeeds');
    $record = $db->query("SELECT * FROM customer_accounts WHERE account_number = '" . $db->real_escape_string($number) . "'")->fetch_assoc();
    $recordId = $record['id'] ?? null;
    check($recordId !== null && $record['email'] === null && $record['phone'] === null && $record['meter_number'] === null, 'Created record has SQL NULL for optional fields');
    check(! empty($record['created_at']) && ! empty($record['updated_at']), 'Timestamps saved');
    $list = request('customer-accounts');
    check(str_contains($list['body'], 'created successfully.') && str_contains($list['body'], 'Smoke &lt;script&gt;test&lt;/script&gt;'), 'Create flash and escaped customer output');
    $detail = request('customer-accounts/view/' . $recordId);
    check($detail['status'] === 200 && str_contains($detail['body'], $number), 'Customer detail reads new record');

    $form = request('customer-accounts/create');
    check(redirectsTo(request('customer-accounts/store', csrf($form) + $data), 'customer-accounts/create'), 'Duplicate account number redirects');
    $form = request('customer-accounts/create');
    check(str_contains($form['body'], 'must contain a unique value'), 'Duplicate account number validation message');

    $form = request('customer-accounts/edit/' . $recordId);
    check($form['status'] === 200 && str_contains($form['body'], $number), 'Edit form populated');
    if ($before !== []) {
        $duplicate = array_replace($data, ['account_number' => $before[0]['account_number']]);
        check(redirectsTo(request('customer-accounts/update/' . $recordId, csrf($form) + $duplicate), 'customer-accounts/edit/' . $recordId), 'Edit rejects another account number');
        $form = request('customer-accounts/edit/' . $recordId);
    }
    $updated = array_replace($data, ['customer_name' => 'Updated smoke customer', 'email' => 'smoke@example.com', 'connection_type' => 'industrial', 'status' => 'suspended']);
    check(redirectsTo(request('customer-accounts/update/' . $recordId, csrf($form) + $updated), 'customer-accounts'), 'Edit accepts unchanged unique account number');
    $list = request('customer-accounts');
    check(str_contains($list['body'], 'updated successfully.'), 'Update success flash');
    $record = $db->query('SELECT * FROM customer_accounts WHERE id = ' . (int) $recordId)->fetch_assoc();
    check($record['customer_name'] === $updated['customer_name'] && $record['email'] === $updated['email'] && $record['connection_type'] === 'industrial' && $record['status'] === 'suspended', 'Updated values persisted');

    $form = request('customer-accounts/edit/' . $recordId);
    check(redirectsTo(request('customer-accounts/update/' . $recordId, csrf($form) + array_replace($updated, ['connection_type' => '', 'status' => ''])), 'customer-accounts'), 'Nullable ENUM fields accepted');
    $record = $db->query('SELECT * FROM customer_accounts WHERE id = ' . (int) $recordId)->fetch_assoc();
    check($record['connection_type'] === null && $record['status'] === null, 'Nullable ENUM fields saved as NULL');

    check(request('customer-accounts/delete/' . $recordId)['status'] === 404, 'GET cannot delete');
    check(request('logout')['status'] === 404, 'GET cannot log out');
    $list = request('customer-accounts');
    check(redirectsTo(request('customer-accounts/delete/' . $recordId, csrf($list)), 'customer-accounts'), 'Delete succeeds via POST');
    $list = request('customer-accounts');
    check(str_contains($list['body'], 'deleted successfully.'), 'Delete success flash');
    check($db->query('SELECT id FROM customer_accounts WHERE id = ' . (int) $recordId)->num_rows === 0, 'Deleted record absent from database');
    check(request('customer-accounts/view/' . $recordId)['status'] === 404, 'Missing record returns 404');
    $list = request('customer-accounts');
    $authenticatedCookies = curl_getinfo($client, CURLINFO_COOKIELIST);
    check(redirectsTo(request('logout', csrf($list)), 'login'), 'Logout redirects to login');
    check(redirectsTo(request('dashboard'), 'login'), 'Dashboard blocked after logout');
    curl_setopt($client, CURLOPT_COOKIELIST, 'ALL');
    foreach ($authenticatedCookies as $cookie) {
        curl_setopt($client, CURLOPT_COOKIELIST, $cookie);
    }
    check(redirectsTo(request('customer-accounts'), 'login'), 'Destroyed session cannot be reused');
    check(request('')['status'] === 200, 'Landing page remains public after logout');

    // Change only the temporary user to check the professor's legacy format.
    $changeUser = $authDb->prepare('UPDATE users SET password = ?, is_active = 0 WHERE email = ?');
    $changeUser->bind_param('ss', $password, $email);
    $changeUser->execute();
    $login = request('login');
    check(redirectsTo(request('login', csrf($login) + ['email' => $email, 'password' => $password]), 'login'), 'Inactive user rejected with correct password');
    check(redirectsTo(request('dashboard'), 'login'), 'Inactive user cannot access dashboard');
    $activate = $authDb->prepare('UPDATE users SET is_active = 1, email_verified = NULL WHERE email = ?');
    $activate->bind_param('s', $email);
    $activate->execute();
    $login = request('login');
    check(redirectsTo(request('login', csrf($login) + ['email' => $email, 'password' => $password]), 'dashboard'), 'Legacy plaintext password works; nullable verification field supported');
    $legacyUser = $authDb->query("SELECT password FROM users WHERE email = '" . $authDb->real_escape_string($email) . "'")->fetch_assoc();
    check($legacyUser['password'] === $password, 'Login does not alter stored passwords');
    $list = request('customer-accounts');
    check(redirectsTo(request('logout', csrf($list)), 'login'), 'Legacy login logs out correctly');
} finally {
    // Never delete imported accounts: only the random account number created here.
    $delete = $db->prepare('DELETE FROM customer_accounts WHERE account_number = ?');
    $delete->bind_param('s', $number);
    $delete->execute();
    $deleteUser = $authDb->prepare('DELETE FROM users WHERE email = ?');
    $deleteUser->bind_param('s', $email);
    $deleteUser->execute();
    $usersAfter = $authDb->query('SELECT * FROM users ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    check($usersAfter === $usersBefore, 'Original users unchanged; temporary auth user removed');
    $after = $db->query('SELECT * FROM customer_accounts ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    check($after === $before, 'All original customer records unchanged; test record removed');
    curl_close($client);
    $db->close();
    $authDb->close();
}
