@echo off
timeout /t 1800
echo /********	BUSCAMOS NUEVAS VERSIONES EN EL GIT	********/
call "C:\Users\Tech\Documents\git_sync.bat"
echo /********	BUSCAMOS NUEVAS VERSIONES EN EL GIT	********/
timeout /t 20
echo /********	INICIAMOS EL SERVIDOR DE SELENIUM EN UNA NUEVA VENTANA	********/
start cmd.exe /k "java -jar C:\Users\Tech\Documents\Scraper\selenium-server-standalone-3.141.59.jar -sessionTimeout 129600"
echo /********	INICIAMOS EL SERVIDOR DE SELENIUM EN UNA NUEVA VENTANA	********/
timeout /t 60
echo /********	EJECUTAMOS EL SCRIPT	********/
php C:\Users\Tech\Documents\Scraper\instagram-hashtags.php
echo /********	EJECUTAMOS EL SCRIPT	********/