# Automatic setup

## Requirement
- Create a new Ubuntu 18.04 server.
- Point your domain's A record to the IP of your newly created server

## Installation
- SSH into your server with root
- Copy/Create `server.sh`, `deploy.sh`, `variables.sh` file in the home directory of your server
- Make changes in `variables.sh`
- Copy `.env.example` from this repo's root as .env and make changes according to your environment
- Execute `bash server.sh`. It will install all the packages/dependencies required for the project to run
- You might need to press `Enter` to continue the script
- If the above command executed successfully, execute `bash deploy.sh`
- Composer Install may ask for `Laravel Nova Token`, Paste the token and press `Enter`. If you don't have token, get one here [https://nova.laravel.com/settings#password](https://nova.laravel.com/settings#password) or contact me.
- If everything goes well, your project is deployed successfully.

## Need help
Reach out to me at [Twitter](https://www.twitter.com/nivesh_saharan)
You can also contact me if you need a developer or a team for your custom app or update an existing one.
