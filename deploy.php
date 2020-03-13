<?php

namespace Deployer;

use Deployer\Task\Context;

require '/var/www/html/deployer/deployer/recipe/laravel.php';
require '/var/www/html/deployer/recipes/recipe/slack.php';

require './.deployer/config.php';

/**
 * Main Info
 */
task('info', function () {
    writeln('<info>Host: ' . run('whoami') . '@' . run('hostname') . '</info>');

    $serverIpAddress = run('hostname -I');
    writeln('<info>IP: ' . $serverIpAddress . '</info>');

    set('server_ip', explode(' ', $serverIpAddress)[0]);
    writeln('<info>Deploy path: {{deploy_path}}</info>');
});

/**
 * Main Info
 */
task('check:provision', function () {
    if (!test('[ -e /home/deployer/.provisioned ]')) {
        writeln('<comment>Server is not provisioned. Starting provision...</comment>');
        invoke('server:provision');
    }

    invoke('server:user');
});

task('check:domain', function () {
    $domainIpAddress = gethostbyname(get('domain'));

    if (!get('server_ip', '')) {
        invoke('info');
    }

    if (get('server_ip') !== $domainIpAddress) {
        writeln('<error>Server IP and Domain IP doesn\'t match. It will create issues while generating ssl certificate. Hence exiting...</error>');
        exit;
    }
});

task('yarn', function () {
    if (has('previous_release')) {
        if (test('[ -d {{previous_release}}/node_modules ]')) {
            run('cp -R {{previous_release}}/node_modules {{release_path}}');
        }
    }
    run('cd {{release_path}} && yarn');
});

/**
 * Assets generation
 */
task('build', function () {
//    run('cd {{release_path}} && yarn production');

    if (!test('[ -e {{deploy_path}}/shared/storage/oatuh-private.key ]')) {
        run('cd {{release_path}} && php artisan passport:install');
    }
});

/**
 * Upload .env.production file as .env
 */
task('env', function () {
    $env = file_exists('.deployer/.env.' . get('stage') . '.env') ? file_get_contents('.deployer/.env.' . get('stage') . '.env') : '';
    if ($env) {
        file_put_contents('.env.' . get('stage') . '_compiled.conf', parse($env));
        upload('.env.' . get('stage') . '_compiled.env', '{{release_path}}/.env');
        unlink('.env.' . get('stage') . '_compiled.env');
        invoke('artisan:config:cache');
    } else {
        writeln('<error>Env not found</error>');
    }
    //upload('.env.{{stage}}', '{{release_path}}/.env');
})->desc('Environment setup');

/**
 * Restart queue
 */
task('artisan:queue:restart', function () {
    $output = run('sudo service supervisor restart');
    writeln('<info>' . $output . '</info>');
    $output = run('php {{deploy_path}}/current/artisan queue:restart');
    writeln('<info>' . $output . '</info>');
})->desc('Execute artisan route:cache');

task('supervisor', function () {
    invoke('server:user');
    $data = file_exists('.deployer/supervisor.conf') ? file_get_contents('.deployer/supervisor.conf') : '';
    if ($data) {
        file_put_contents('supervisor_compiled.conf', parse($data));
        invoke('server:root_user');
        upload('supervisor_compiled.conf', '/etc/supervisor/conf.d/{{application}}.conf');
        invoke('server:user');
        unlink('supervisor_compiled.conf');
    } else {
        writeln('<error>Data not found</error>');
    }
});

task('vhost', function () {
    $data = file_exists('.deployer/host.conf') ? file_get_contents('.deployer/host.conf') : '';
    if ($data) {
        file_put_contents('host_compiled.conf', parse($data));
        invoke('server:root_user');
        upload('.deployer/letsencrypt.conf', '/etc/nginx/snippets/letsencrypt.conf');
        upload('.deployer/ssl-params.conf', '/etc/nginx/snippets/ssl-params.conf');
        upload('host_compiled.conf', '/etc/nginx/sites-available/{{domain}}.conf');
        unlink('host_compiled.conf');
        run('sudo test -e /etc/nginx/sites-enabled/{{domain}}.conf || sudo ln -s /etc/nginx/sites-available/{{domain}}.conf /etc/nginx/sites-enabled/{{domain}}.conf');
        invoke('server:user');
        run('sudo nginx -t');
        run('sudo service nginx reload');
    } else {
        writeln('<error>Data not found</error>');
    }
});

/**
 * Server related scripts
 */
task('server:info', function () {
    writeln('<info>Host: ' . run('whoami') . '@' . run('hostname') . '</info>');
    writeln('<info>IP: ' . run('hostname -I') . '</info>');
});

task('server:init', function () {
    if (test('[ -e /home/deployer/.provisioned ]')) {
        run('sudo rm /home/deployer/.provisioned');
    }
});

task('server:update', function () {
    run('sudo apt-get update');
})->desc('Update server');

task('server:set_chars', function () {
    run('export LC_ALL="en_US.UTF-8"');
    run('export LC_CTYPE="en_US.UTF-8"');
});

task('server:common_apps', function () {
    run('sudo apt-get install -y zip unzip openssl acl software-properties-common curl nginx');
});

task('server:git', function () {
    run('sudo apt-get install -y git');

    within('~', function () {
        if (get('git_username')) {
            run('git config --global user.name "' . get('git_username') . '"');
        }

        if (get('git_email')) {
            run('git config --global user.email "' . get('git_email') . '"');
        }
    });
});

task('server:node_js', function () {
    run('curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash - && sudo apt-get install -y nodejs');
    writeln('The node version is: ' . run('node -v'));
    run('curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -');
    run('echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list');
    invoke('server:update');
    run('sudo apt-get remove -y cmdtest');
    run('sudo apt-get install -y yarn');
    writeln('The yarn version is: ' . run('sudo yarn --version'));
});

task('server:redis', function () {
    run('sudo apt-get install -y redis-server && update-rc.d redis-server enable && update-rc.d redis-server defaults');
});

task('server:supervisor', function () {
    run('sudo apt-get install -y supervisor');
});

task('server:php', function () {
    run('sudo add-apt-repository -y ppa:ondrej/php');
    invoke('server:update');
    run('sudo apt-get install -y php7.2 php7.2-common php7.2-cli php7.2-fpm php7.2-curl php7.2-json php7.2-tidy php7.2-mysql php7.2-gd php7.2-xml php7.2-zip php7.2-mbstring php7.2-dom php7.2-mysql php7.2-imap php7.2-bcmath php-apcu php7.2-intl');
    run("sudo sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.2/fpm/php.ini");
    run("sudo sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /etc/php/7.2/fpm/php.ini");
    run("sudo sed -i 's/max_execution_time = 30/max_execution_time = 120/g' /etc/php/7.2/fpm/php.ini");
});

task('server:mysql', function () {
    run('echo "mysql-server-5.7 mysql-server/root_password password ' . get('mysql_password', 'root') . '" | sudo debconf-set-selections');
    run('echo "mysql-server-5.7 mysql-server/root_password_again password ' . get('mysql_password', 'root') . '" | sudo debconf-set-selections');
    run('sudo apt-get -y install mysql-server-5.7');

    if (get('mysql_database')) {
        run('mysql -uroot -p' . get('mysql_password') . ' -e "CREATE DATABASE IF NOT EXISTS ' . get('mysql_database') . '"');
    }
});

task('server:composer', function () {
    run('curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer');

    // SWAP memory for composer
    if (get('swap_memory_size')) {
        run('sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count={{swap_memory_size}}');
        run('sudo /sbin/mkswap /var/swap.1');
        run('sudo /sbin/swapon /var/swap.1');
    }
});

task('server:certbot', function () {
    run('sudo add-apt-repository ppa:certbot/certbot');
    invoke('server:update');
    run('sudo apt-get install -y certbot python-certbot-nginx');
    run('sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048');
});

task('server:deployer', function () {
    try {
        run('grep -c \'^deployer:\' /etc/passwd');
    } catch (\Exception $e) {
        run('sudo adduser deployer');
        run('sudo usermod -aG www-data deployer');
        run('sudo chfn -o umask=022 deployer');
        run('sudo chfn -o umask=022 deployer');
        run('sudo mkdir -p /var/www/html');
        run('sudo chown deployer:www-data /var/www/html');
        run('sudo chmod g+s /var/www/html');
        run('echo "deployer ALL=(ALL:ALL) NOPASSWD: ALL" | sudo tee /etc/sudoers.d/deployer');
        run('mkdir -p /home/deployer/.ssh');
        upload('./.deployer/.ssh/id_rsa', '/home/deployer/.ssh/id_rsa');
        upload('./.deployer/.ssh/id_rsa.pub', '/home/deployer/.ssh/id_rsa.pub');
        run('sudo chown -R deployer /home/deployer/.ssh');
        run('chmod 400 /home/deployer/.ssh/id_rsa');
        run('chmod 400 /home/deployer/.ssh/id_rsa.pub');
    }

    try {
        run('sudo -Hu deployer ssh -T git@github.com -o StrictHostKeyChecking=no');
    } catch (\Exception $e) {
    }
});

task('server:ssl', function () {
    run('sudo certbot certonly -n --nginx -d {{domain}} --agree-tos -m {{email}}');
    run('sudo service nginx reload');
});

task('server:cleanup', function () {
    run('sudo apt -y autoremove');
    run('sudo apt-get clean');
});

task('server:done', function () {
    run('touch /home/deployer/.provisioned');
});

task('server:user', function ($user = 'deployer') {
    if (run('whoami') !== $user) {
        $host = Context::get()->getHost();
        writeln('<comment>Changing user to deployer</comment>');
        $host->become($user);
    }
});
task('server:root_user', function ($user = 'root') {
    if (run('whoami') !== $user) {
        $host = Context::get()->getHost();
        writeln('<comment>Changing user to root</comment>');
        $host->become($user);
    }
});

task('server:provision', [
    'server:info',
    'check:domain',
    'server:init',
    'server:update',
    'server:deployer',
    'server:set_chars',
    'server:common_apps',
    'server:git',
    'server:node_js',
    'server:redis',
    'server:supervisor',
    'server:php',
    'server:mysql',
    'server:composer',
    'server:certbot',
    'server:ssl',
    'server:cleanup',
    'server:done',
]);

/**
 * Deploy script
 */
task('deploy', [
    'info',
    'deploy:info',
    'check:provision',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'artisan:storage:link',
    'deploy:writable',
    'yarn',
    'env',
    'artisan:cache:clear',
    'artisan:view:clear',
    'artisan:route:cache',
    'artisan:config:cache',
    'artisan:migrate',
    'artisan:db:seed',
    'build',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'supervisor',
    'vhost',
    'artisan:queue:restart',
]);

after('deploy', 'success');
after('deploy:failed', 'deploy:unlock');
