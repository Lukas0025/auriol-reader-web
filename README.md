## auriol-reader-web
#### AURIOL H13726 / Ventus W155 raspberry pi reader with web interface

This repository contains AURIOL H13726 / Ventus W155 weather stations radio transmissions decoder application for Raspberry Pi. Application obtains data through a 433.92 MHz RF wireless receiver module (AUREL RX-4MM5++/F, simple chinese XY-MK-5V or other similar) connected to GPIO pin, decodes this data and saves in SQLite database (this do auriol-reader by yu55). You can load this data using simple web interface on IP of your raspberry.

![auriol-reader-web-screenshot.png](screens/pc.png?raw=true "View of data received from AURIOL H13726 weather station via web")

### Instalation

 * first clone repo `git clone https://github.com/Lukas0025/auriol-reader-web`
 * now you can install it `cd auriol-reader-web && make install`
 * dont forget add cron to root `sudo crontab -e` and add line `@reboot /usr/bin/auriol-reader >> /dev/null`
 * reboot your pi
 * now open IP of your raspberry in browser