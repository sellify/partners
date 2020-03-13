# Automatic setup

## Requirement
- Create a new Ubuntu 18.04 server.
- Point your domain's A record to the IP of your newly created server

## Installation
- Copy .deployer/.example as .deployer in your root directory
- Copy and paste your SSH keys into .deployer/.ssh directory. (The keys having ability to ssh into your server as root)
- Execute `chmod 400 .deployer/.ssh/id_rsa` in your root directory
- Make changes in `.deployer/.env.production.env` if needed
- Make changes in `.deployer/config.php`
- Make changes in `.hosts.yml` and replace your server domain/ip
- Execute `./vendor/bin/dep deploy production`. 
- Composer Install may ask for `Laravel Nova Token`, Paste the token and press `Enter`. If you don't have token, get one here [https://nova.laravel.com/settings#password](https://nova.laravel.com/settings#password) or contact me.
- If everything goes well, your project is deployed successfully.

## Need help
Reach out to me at [Twitter](https://www.twitter.com/nivesh_saharan)
You can also contact me if you need a developer or a team for your custom app or update an existing one.
