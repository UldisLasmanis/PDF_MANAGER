# Setting-up
Copy project repository
> git clone git@github.com:UldisLasmanis/PDF_Manager.git 

Go to project root folder
> cd PDF_Manager

Install composer dependencies
> composer install

Install Yarn packages
> yarn install

Compile assets (add "--watch" for automatic recompilation after file changes)
> yarn encore dev

Create empty database `sunfinance` (name can be changed in .env file)
> php bin/console doctrine:database:create

Migrate migrations to DB
> php bin/console doctrine:migrations:migrate

To access project open `localhost` in browser

#### Note:  
If file upload is not working, most probably it's because of wrong project folder permissions.  
To see current ownership, run: 
> ll .

If folder owner is `your_root_username` (ex. `uldis`), then run this command if you're running `apache2` server.  
> sudo chown -R www-data .

Now you should be able to upload/remove files

# Using
Upload:  
![alt text](https://github.com/UldisLasmanis/PDF_Manager/blob/master/public/instructions/upload_instructions.png?raw=true)

Note:  
In order to upload larger files (1MB+), please first check php.ini value 
upload_max_filesize for supported max size

Once file is uploaded, a first page thumbnail will appear with these options: 
![alt text](https://github.com/UldisLasmanis/PDF_Manager/blob/master/public/instructions/button_instructions.png?raw=true)

