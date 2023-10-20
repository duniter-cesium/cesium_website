Installer le site sur son ordinateur
===

## (optionnel) Configurer le multilangue

```
# En root, sur Debian
echo -e "ca_ES.UTF-8 UTF-8\nde_DE.UTF-8 UTF-8\nen_GB.UTF-8 UTF-8\neo UTF-8\nes_ES.UTF-8 UTF-8\nit_IT.UTF-8 UTF-8\nfr_FR.UTF-8 UTF-8" >> /etc/locale.gen
locale-gen
update-locale
service php*-fpm restart
```

## config.php

Dans le fichier `config.php`, modifiez la variable `$rootURL` pour qu'elle corresponde à la façon dont vous servez le site web :
```php
if ($_SERVER['SERVER_NAME'] == 'localhost') {
	// Adaptez la ligne suivante à votre configuration (sans slash à la fin)
	$rootURL = '/cesium_website';
} else {
	$rootURL = '';
}
```

## Installer PHP

Pour faire tourner ce site sur votre ordinateur, vous aurez besoin d'installer PHP, qui transformera le code source contenu dans les fichiers *.php en HTML que le navigateur du visiteur peut comprendre.

Sous Linux, il vous faudra installer le paquet `php`.

Par exemple, sous une Debian-like (Ubuntu, Linux Mint, etc.) : 

```
sudo apt install php
```

Les utilisateurs de Windows peuvent utiliser [WAMP Serveur](https://www.wampserver.com/), qui vient aussi avec son serveur web.


## Installer un serveur web

Pour faire tourner ce site sur votre ordinateur, vous aurez besoin d'installer un serveur web.

Vous avez le choix entre :
- nginx
- apache2

## Nginx

Sous Linux, il vous faudra installer le paquet `nginx`.

Par exemple, sous une Debian-like (Ubuntu, Linux Mint, etc.) : 

```
sudo apt install nginx
```

### configurer Nginx

Les fichiers de configuration de Nginx se trouvent dans `/etc/nginx/sites-available/`.

#### option 1 : modifier `default`

Vous avez probablement dans ce répertoire un fichier `default` auquel vous pouvez ajouter le bloc d'instruction qui suit le commentaire `# Configuration pour /cesium_website`.


```txt
server {

	listen 80;

	root /var/www/localhost;

	# Le premier fichier d'index recherché est index.php
	index index.php index.html index.htm index.nginx-debian.html;

	server_name localhost;

	location / {
		# First attempt to serve request as file, then
		# as directory, then fall back to displaying a 404.
		try_files $uri $uri/ =404;
	}

	# Configuration pour /cesium_website
	location /cesium_website {

		# Si le fichier ou le répertoire n'existe pas
		if (!-e $request_filename) {

			# Réécriture d'URL
			rewrite ^/cesium_website/([^/]+)/(.*)$ /cesium_website/index.php?lang=$1&page=/$2 last;
			rewrite ^/cesium_website/([^/]+)$ /cesium_website/index.php?lang=fr&page=/$1 last;
		}

		# Traitement des fichiers PHP
		location ~ \.php$ {
			include snippets/fastcgi-php.conf;
			fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Assurez-vous de vérifier et d'utiliser la version correcte de PHP
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			include fastcgi_params;
		}
	}
}
```

Note : pensez à remplacer `php8.1` par la version de PHP que vous avez installée.

Redémarrez nginx :
```
sudo service nginx restart
```

#### option 1 : créer un fichier de configuration dédié

Vous pouvez aussi créer un nouveau fichier de configuration, et un nouveau nom de serveur local.

Commencez par créer un lien symbolique vers l'emplacement approprié :
```
ln -s /var/www/cesium_website /home/votre_nom_d_utilisateur/projets/cesium_website
```
en remplçant le chemin de destination par l'emplacement où vous avez téléchargé le dossier `cesium_website`.

Auquel cas il vous faudra modifier votre fichier `/etc/hosts` :
```
sudo nano /etc/hosts
```

pour y ajouter une ligne du style :
```txt
127.0.0.1  cesiumwebsite
```

Créez le fichier de configuration :
```
sudo nano /etc/nginx/sites-available/cesium_website
```

et ajoutez-y le contenu idoine :
```
server {

	listen 80;

	root /var/www/cesium_website;

	# Le premier fichier d'index recherché est index.php
	index index.php index.html index.htm index.nginx-debian.html;

	server_name cesiumwebsite;

	location / {
		# First attempt to serve request as file, then
		# as directory, then fall back to displaying a 404.
		try_files $uri $uri/ =404;
	}

	# Configuration pour /
	location / {

		# Si le fichier ou le répertoire n'existe pas
		if (!-e $request_filename) {

			# Réécriture d'URL
			rewrite ^/([^/]+)/(.*)$ /index.php?lang=$1&page=/$2 last;
			rewrite ^/([^/]+)$ /index.php?lang=fr&page=/$1 last;
		}

		# Traitement des fichiers PHP
		location ~ \.php$ {
			include snippets/fastcgi-php.conf;
			fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Assurez-vous de vérifier et d'utiliser la version correcte de PHP
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			include fastcgi_params;
		}
	}
}
```

sans oublier de créer un lien symbolique pour rendre le site disponible :
```
sudo ln -s /etc/nginx/sites-enabled/cesium_website /etc/nginx/sites-available/cesium_website
```

…et de redémarrer nginx :
```
sudo service nginx restart
```

## Apache

Sous Linux, il vous faudra installer le paquet `apache2`.

Par exemple, sous une Debian-like (Ubuntu, Linux Mint, etc.) : 

```
sudo apt install apache2
```

### configurer le fichier `.htaccess`

Le fichier `.htaccess` est celui qui gère la réécriture d'URL, qui permet d'afficher au visiteur une structure compréhensible par un être humain dans sa barre d'adresse.

Votre fichier .htaccess doit contenir les infos suivantes :

```txt
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

Modifiez l'instruction RewriteBase du .htaccess pour qu'elle s'accorde à votre propre configuration 
(tapez l'endroit où CesiumWebsite est installé, typiquement /)

Si lorsque vous essayez d'accéder au site, vous avez une erreur "404 Not Found", c'est que votre fichier `.htaccess` n'est pas pris en considération par Apache.

Il vous faudra alors éditer votre fichier `/etc/apache2/apache2.conf` (anciennement `/etc/apache2/httpd.conf`) pour y remplacer :

```txt
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>
```
par :
```txt
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
</Directory>
```

Activez le mod_rewrite d'Apache :
```
sudo a2enmod rewrite
```

Redémarrez Apache :
```
systemctl restart apache2
```
