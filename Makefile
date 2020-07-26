build:
	mkdir build
	git clone https://github.com/Lukas0025/universal-weather-station-php-site ./build
	cp -r templates build
	cp api.php build
	cp index.php build
	rm -rf build/example

clean:
	rm -rf build

install: build
	sudo apt-get install wiringpi libsqlite3-dev apache2 php libapache2-mod-php -y
	cp -r /build/* /var/www/html
	git clone https://github.com/yu55/auriol_reader
	cd auriol_reader/reader && make
	cp auriol_reader/reader/auriol-reader /usr/bin
	@echo "please add @reboot /usr/bin/auriol-reader to root cron and reboot pi"
