@echo off
timeout /t 10

echo /******** ELIMINAMOS Y VOLVEMOS A MAPEAR LA UNIDAD COMPARTIDA Y COPIAMOS LOS FICHEROS	********/
net use X: /delete /y
net use X: \\192.168.7.38\Users\Tech\Documents\Scripts_Originales
Xcopy X:\ C:\Users\Tech\Documents\Scraper\ /Y
echo /******** ELIMINAMOS Y VOLVEMOS A MAPEAR LA UNIDAD COMPARTIDA Y COPIAMOS LOS FICHEROS	********/

echo /******** INICIAMOS EL SELENIUM EN UNA NUEVA VENTANA. MIENTRAS SE ESPERA 10 SEGUNDOS A EJECUTARSE EL PHP	********/
start cmd.exe /k "java -jar C:\Users\Tech\Documents\Scraper\selenium-server-standalone-3.9.1.jar"
timeout /t 10
php C:\Users\Tech\Documents\Scraper\instagram-public-posts.php

