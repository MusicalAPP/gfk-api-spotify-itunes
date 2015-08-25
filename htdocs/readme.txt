Spotify streaming Daten:
Code liegt auf:  web@mediaetl.media-control.int/home/web/music_spotify_toplists/
Code für Cronjob:  /bin/cron.php
Code für direkt manuell Daten generieren: /bin/ export_manuell.php
Logfile: /data/ logs/logs.txt
Manuelle erzeugte Daten: /data/manuell/…
Crontab: (führt automatisch cron.php aus)
web@mediaetl:/etc/cron.d/music_spotify_toplists
# 8 Uhr Wochentage
0 8 * * 1-5 web cd /home/web/music_spotify_toplists/bin; php ./cron.php >> /var/log/music_spotify_toplists/crawler.log 2>&1

# 09:30 Uhr und 13:30 Uhr Wochentage
30 9,13 * * 1-5 web cd /home/web/music_spotify_toplists/bin; php ./cron.php >> /var/log/music_spotify_toplists/crawler.log 2>&1

# 23:00 ganzen Woche
0 23 * * * web cd /home/web/music_spotify_toplists/bin; php ./cron.php >> /var/log/music_spotify_toplists/crawler.log 2>&1

Update Zeitkey (letzte Erzeugte Daten) : /data/zeitkey/$land_last_key.txt
Rekursive Zeitkey für die nicht erzeugte Daten (diese Zeitkey wird abrufen auch wenn das <= update-Zeitkey ist): /data/zeitkey/$land_recursiv_key.txt
----------------------------------------------------------------------------
Spotify bittet tägliche Daten durch Webservice für:
$country_arr = array('at','ch','de','be','nl','fr','it','es','be_flanders', 'be_wallonia', 'be_brussels');
cron.php checkt die verfügbaren Daten von 4 Tagen ab Gestern, vergleich mit Update_zeitkey (und rekursiv Zeitkey), holt die Daten von Spotify-Webservice ab, unzip, schreibt in txt file und upload to FTP server.
----------------------------------------------------------------------------
FTP:
$ftp_host = 'asbad04.mcbad.net';
$ftp_username = 'MC_FTP';
$ftp_password = 'linux_3018';
$ftp_target = '/NFS/ASBAD04/FTP/MediaControl/GER/DigRetailer/Spotify_API';
----------------------------------------------------------------------------
Manuelle Daten Generieren(per commmand):
Im Fall Fehlers auftreten oder braucht einzelne Daten kann durch putty command direkt erzeugen (und auf FTP manuelle koppieren):
Putty Syntax:
web@mediaetl:~/music_spotify_toplists/bin $ php export_manuell.php at 2013 11 20
wird die Daten für AT am 20131120 : 
/data/manuell/at/spotify_toplist_for_at_2013_11_20.txt
erzeugt.

OR checkout from svn: api-dl.media-control.int/trunk

ACTUNG: zeitkey will not update in manuell mode!!!




