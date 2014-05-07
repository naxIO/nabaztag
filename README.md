nabaztag
========

NabaztagLives Code to run your nabaztag on your own Raspberry or self hosted server


NabaztagLives has been tested on the Raspberry Pi "Wheezy" (NOOBS) distribution and has the following dependencies:

Apache
PHP
MySQL
Lame
Mp3Wrap

At the time of this writing I am running with the following versions:
Apache/2.2.22 (Debian)
PHP 5.4.4-14+deb7u3 (cli) (built: Jul 18 2013 01:01:56)
mysql Ver 14.14 Distrib 5.5.31, for debian-linux-gnu (armv7l)
LAME 32bits version 3.99.5 (http://lame.sf.net)
Mp3Wrap Version 0.5 (2003/Jan/16)

You can install them on your Raspberry Pi by running the commands below. When you install MySql, it will prompt you for a root password. You'll need that to setup the database.

```
sudo apt-get update
sudo apt-get upgrade

sudo apt-get install apache2 php5 php5-curl libapache2-mod-php5

sudo apt-get install mysql-server mysql-client php5-mysql

sudo apt-get install lame

sudo apt-get install mp3wrap

```
When you install Apache, it may kick out an error that says Could not reliably determine the server's fully qualified domain name. You can fix this by setting the server's name to localhost:
sudo vi /etc/apache2/apache2.conf
Scroll to the end of the file and insert:
ServerName localhost
If you have a V1 rabbit, you'll need to set the override:
sudo vi /etc/apache2/sites-enabled/000-default
Change the AllowOverride to All so it looks like this:
<Directory /var/www/>            
    Options -Indexes FollowSymLinks MultiViews            
    AllowOverride All            
    Order allow,deny            
    allow from all   
</Directory>
Save the file and exit.
Then do:
sudo apachectl restart

Installing the application
The easiest way to get setup is to use git to get the latest code:
git clone git://git.code.sf.net/p/nabaztaglives/code NabaztagLives
This will create a folder named NabaztagLives on your Pi.
The solution is designed to run from a dedicated Pi and is installed to the root folder of the website. If you install it to another location, the relative paths will be broken and it will not work. Copy the contents of the www directory to the root of your Pi's web server which is /var/www:
sudo cp -R www /var
Next open the db folder on your Pi and build the database. Enter your MySql root password when prompted:
sudo ./db_setup.sh
Next we need to move the db info file to the right place. This is also where the logging occurs. Go back up one folder to the root folder where you placed the code (where etc exists) and execute the following:
sudo cp -R ./etc /var
Next we need to set some ownership properties or our rabbit will be very quiet:
sudo chown www-data:www-data /var/www/vl/hutch
sudo chown www-data:www-data /var/etc
Now we need to remove the default "It Works!" index page from apache:
sudo rm /var/www/index.html
Almost done! Get your IP address:
ifconfig | grep addr
You should get an IP that starts with 192. Open a browser and browse to that address. If you are on your Pi you can also type in localhost for the address. The site should be displayed. If you didn't get an IP that means your wifi is hosed and I don't know how you got this far.
You're finished! All you need to do now is setup your rabbit. If you need help with that, click on the Setup Info button on the NabaztagLives website that's now running on your Pi. When you're done, be sure and click the "Update Rabbit" button to see all the features.
I get a warning about a Locator Record
If you see this on the home page then you need to change the IP addresses in /www/locate.jsp to your server's IP address (the one displayed at the bottom of the page).
My rabbit doesn't work
That's what I said! Check the error log, there's a button for it on the site. Click "reset" to reset it. Make sure you have setup your rabbit to do things! Set the idle behavior to PacMan Lights so you don't have to wait to see if it works. You can always post to the forum at https://sourceforge.net/p/nabaztaglives/discussion/brokenrabbit/.
New to Raspberry?
If you are setting up your Raspberry Pi for the first time, you can get instructions from http://www.raspberrypi.org/wp-content/uploads/2012/04/quick-start-guide-v2_1.pdf.
I used the NOOBS image and deleted all but the "Wheezy" files before copying it to the SD card. That saves room and speeds the installation assuming you don't intend to run the other distros.
I recommend a 4 GB class 10 SD card from a name brand like Sandisk. The Model B has a 25 MB/s max write speed so you want something that can write that fast. Always check to see if your equipment is supported by checking to see if it works first by going to http://elinux.org/RPi_VerifiedPeripherals.
Just remember that if you want to make a backup of the SD card, you will need whatever size card you have available on your machine. So if you have a 16 GB card, you will need 16 GB free space on your drive. NabaztagLives consumes less than 100 MB.
Start the X11 UI
startx
Set the locale and straighten out the keyboard
sudo raspi-config
List all USB devices
lsusb
Setup hidden wifi
wpa_passphrase Your-Wifi-SSID Your-Password
sudo vi /etc/network/interfaces
Make it look like this:
auto lo

iface lo inet loopback
iface eth0 inet dhcp

auto wlan0
allow-hotplug wlan0
iface wlan0 inet dhcp
   wpa-scan-ssid 1
   wpa-ap-scan 1
   wpa-key-mgmt WPA-PSK
   wpa-proto RSN WPA
   wpa-pairwise CCMP TKIP
   wpa-group CCMP TKIP
   wpa-ssid "Your Wifi SSID"
   wpa-psk Your-PSK-Value-that-you-got-from-running-wpa_passphrase

iface default inet dhcp
Time is wrong
sudo dpkg-reconfigure tzdata
Reboot
sudo reboot
Shutdown
sudo shutdown -h now
Set automatic login
sudo vi /etc/inittab
Find the following line:
1:2345:respawn:/sbin/getty 115200 tty1
And change to:
1:2345:respawn:/bin/login -f pi tty1 </dev/tty1 >/dev/tty1 2>&1
Get current temperature
vcgencmd measure_temp
Open MySql
mysql rabbit -u root -p
