<?php

namespace Deployer;

set('mysql_database', 'my-database-name');
set('mysql_password', 'my-password');
set('git_username', 'my-name');
set('git_email', 'myemail@mydomain.com');
set('swap_memory_size', '2048');
set('application', 'app-name');
set('application_name', 'app-full-name');
set('domain', 'my-domain.com');
set('email', 'myemail@mydomain.com');
set('repository', 'git@github.com:sellify/partners.git');
set('keep_releases', 5);
set('composer_options', 'install --no-dev');
set('shared_files', ['.env']);
set('shared_dirs', ['storage']);
set('writable_dirs', ['storage', 'vendor']);
set('paypal_mode', 'sandbox');
set('paypal_client_id', '');
set('paypal_client_secret', '');
set('passport_private_key', function () {
    // Use php artisan passport:keys command to generate new and paste the output here
    return '';
});
set('passport_public_key', function () {
    // Use php artisan passport:keys command to generate new and paste the output here
    return '';
});

inventory(__DIR__ . '/.hosts.yml');
