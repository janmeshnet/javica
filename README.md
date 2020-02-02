# javica
This software is still in design stage. Documentation is currently added but there is *no* useful code available !

(quite old) Update: the architecture evolved to a localhost php/www tiers that serves the web browser client, and a peer.js server running locally as well

NEW security update: the code as-is (February 2nd, 2020) uses REMOTE_ADDR to make sure that the UI is served only to localhost. This is a security flaw, since this data can be spoofed. Documentation will soon be updated to setup Apache VHOSTs on two different ports, one for serving the UI that will be bound to 127.0.0.1, and one to expose the inter-instance API that will run on BASE_PORT + 2. Stay tuned. 
UPDATE to the update: the documentation was updated and the code modified to have the UI part running on a localhost-bound VHOST. Please review this readme and modify your previous install accordingly if you installed Javica befor Feb, 2nd 2020. 



# Important warning
## This software is meant to run over a secured Cjdns virtual network. 
It should never be run over the internet or over a local lan not operating Cjdns
## Its design assume it will be used as a visiophone tool, typically using a meshbox plugged on the living room TV
There is no per-user accounts nor any kind of protection : any people having access to any user acount on the box will be able to launch the software and to make/reply to calls using the CA Number associated with the meshbox hosting this software. 
Think of it as a kind of phone but featuring video. 
## Although the architecture involves a web server, the web server must be on the same machine that the client browser
You can not setup a lan server somewhere and connect to it using a remote web browser. It simply cannot work. 
# End of important warning
          
# Getting started with Javica          
          
## Installation 

First, install apache2, php for Apache and also npm and git

	$ sudo apt-get install apache2 libapache2-mod-php php npm git

then, clone the Javica repositorie in your home folder

	$ git clone https://github.com/janmeshnet/javica.git

then, setup a virtual host for Apache, listening on the Javica BASE_PORT (38186)

### Apache2 VHOST configuration

We'll need to setup two virtual hosts, one for the UI part meant to be accessed from localhost only, and the other one for the API part which will be reachable from the outside. 

Open the apache ports configuration file

	$ sudo nano /etc/apache2/ports.conf

you'll have then to decide if you still want Apache to listen on the standard 80 and 443 port. If not, you can remove the lines "Listen 80" and "Listen 443" <- this, especially if your meshbox is dedicated to be used only as a visiophony tool. 

In any case, you'll have to add this to tell Apache to listen to the Javica BASE_PORT and Javica BASE_PORT+2:


	Listen 38186
	Listen 38188
 
 
We need now to disable the default Apache2 sites-available configuration, with the command

	$ sudo a2dissite 000-default.conf 
 
then edit /etc/apache2/sites-available/javica.conf to add a section like this one: 
 
 
	<VirtualHost 127.0.0.1:38186>
	DocumentRoot /var/www/html/javica
		
    ErrorLog ${APACHE_LOG_DIR}/error.log
        
    CustomLog ${APACHE_LOG_DIR}/access.log combined
	</VirtualHost>

Note: the 127.0.0.1 part of this VHOST configuration is really important. It is needed to make sure that only the local computer can access the application (i.e. list your contacts, make calls and so on). 

then edit as well /etc/apache2/sites-available/javica-api.conf with a section like the following:

	<VirtualHost *:38188>
	DocumentRoot /var/www/html/javica-api
		
    ErrorLog ${APACHE_LOG_DIR}/error.log
        
    CustomLog ${APACHE_LOG_DIR}/access.log combined
	</VirtualHost>



Then make a directory which will be the root of your Javica install: 

	$ sudo mkdir /var/www/html/javica 

And for the API part of the software

	$ sudo mkdir /var/www/html/javica-api

You can now enable the newly configured sites with

	$ sudo a2ensite javica.conf
	$ sudo a2ensite javica-api.conf

And a last and important thing: edit /etc/apache2/apache2.conf

And in the section named <Directory /var/www>, change AllowOverride None to AllowOverride All ; this, to make sure that Apache will honour .htaccess directives. 


And... You can restart Apache2 with 

	$ sudo sysctl reload apache2

And that's all for the Apache2 part. 


Now, install and configure the software

### javica initial configuration

 
Copy the content of the javica/php-www/ folder into /var/www/html/javica and give the www-data user ownership on them: 

	$ sudo cp -r ./javica/php-www /var/www/html/javica

	$ sudo chown -R www-data:www-data /var/www/html/javica

And as well, copy the content of the javica/api folder into /var/www/html/javica-api

	$ sudo cp -r ./javica/api /var/www/html/javica-api

	$ sudo chown -R www-data:www-data /var/www/html/javica-api



create a new directory, and changedir to go into it, then init an new npm project, clone peer.js in it

	$ mkdir peer.js

	$ cd peer.js

	$ npm init

Press enter several times to validate each field, then install peer.js once you get back to the command line invite : 

	$ npm install peer

then you can run the peering server *on BASE_PORT +1* with the command

	$ nohup ./node_modules/.bin/peerjs --port 38187 &

(TODO: add explanation on how to move this command to .xinitrc)

## Configuration

(to come soon...)
