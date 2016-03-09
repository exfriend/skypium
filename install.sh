#!/usr/bin/env bash

echo "----------------------------"
echo "Installing Chromium,Java,Xvfb"
echo "----------------------------"
sudo apt-get install -y chromium-browser chromium-chromedriver xvfb npm

command -v java >/dev/null 2>&1 || { sudo apt-get install default-jdk; }
if [ ! -f /opt/selenium.jar ]; then
echo "----------------------------"
echo "Installing Selenium"
echo "----------------------------"

sudo npm install selenium-standalone@latest -g
sudo selenium-standalone install

fi
command -v composer >/dev/null 2>&1 || {
echo "----------------------------"
echo "Installing Composer"
echo "----------------------------"
sudo wget https://getcomposer.org/composer.phar -O /bin/composer
sudo chmod +x /bin/composer
}
