How to install Cesium website on your computer
===


## (optional) Configure multilang

```
# Root, on Debian
echo -e "ca_ES.UTF-8 UTF-8\nde_DE.UTF-8 UTF-8\nen_GB.UTF-8 UTF-8\neo UTF-8\nes_ES.UTF-8 UTF-8\nit_IT.UTF-8 UTF-8\nfr_FR.UTF-8 UTF-8" >> /etc/locale.gen
locale-gen
update-locale
service php*-fpm restart
```

## config.php

In ̀`config.php`, edit $rootURL var according to your setup.

```php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
	// Adaptez la ligne suivante à votre configuration (sans slash à la fin)
	$rootURL = '/cesium_website';
} else {
	$rootURL = '';
}
```

## Installer PHP

To run Cesium website on your computer, you will need install and PHP, which converts the source code contained in *.php files into HTML the browser can understand.


Windows users can use [WAMP Serveur](https://www.wampserver.com/), which comes with a web server

## How to install a web server

To run Cesium website on your computer, you will also need a web server

You can choose one of the following:
- apache2
- nginx

## Nginx

Linux users will have to install the `nginx` package.

For instance, Debian-like (Ubuntu, Linux Mint, etc.) users will have to run:

```
sudo apt install nginx
```

Go to `/etc/nginx/sites-available/`.

Edit the `default` file to add the bloc that follows `# Configuration for /cesium_website`.


```txt
server {

	listen 80;

	root /var/www/localhost;

	index index.php index.html index.htm index.nginx-debian.html;

	server_name localhost;

	location / {
		# First attempt to serve request as file, then
		# as directory, then fall back to displaying a 404.
		try_files $uri $uri/ =404;
	}

	# Configuration for /cesium_website
	location /cesium_website {

		if (!-e $request_filename) {

			rewrite ^/cesium_website/([^/]+)/(.*)$ /cesium_website/index.php?lang=$1&page=/$2 last;
			rewrite ^/cesium_website/([^/]+)$ /cesium_website/index.php?lang=fr&page=/$1 last;
		}

		location ~ \.php$ {
			include snippets/fastcgi-php.conf;
			fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Assurez-vous de vérifier et d'utiliser la version correcte de PHP
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			include fastcgi_params;
		}
	}
}
```

Note : don't forget to change `php8.1` for the version of PHP you installed.

Restart nginx :
```
sudo service nginx restart
```

## Apache

Linux users will have to install the `apache2` package.

For instance, Debian-like (Ubuntu, Linux Mint, etc.) users will have to run:

```
sudo apt install apache2
```

### .htaccess

Create a .htaccess in cesium_website directory, with the following text : 

```
<IfModule mod_rewrite.c>
RewriteEngine On

# Adaptez la ligne suivante à votre configuration (avec un slash à la fin)
RewriteBase /cesium-website-project/cesium_website/

RewriteOptions InheritDown

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .*\.php - [L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^/]+)/(.*)$ index.php?lang=$1&page=/$2 [L]
</IfModule>

```

Edit RewriteBase directive to match your own setup.

If, when trying to access the site, you get a "404 Not Found" error, this means your `.htaccess` file is not taken into account by Apache.

You will then need to edit `/etc/apache2/apache2.conf` file and replace :

```
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>
```
with :
```
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
```
