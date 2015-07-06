# wifi-cam-poster

## Intro

Idea of this project is to get photos from digital camera (that supports only
SD card as a storage media) to tumblr posts with NO human interaction (other
then shooting photos) in a cheap way.


### Hardware 

Only specific hardware requirement for this is SD card with WiFi capability. As
the goal of this project is to do it cheaply - one of cheapest WiFi SD cards
was chosen. XP gained in the process. Cheapest unbranded WiFi SD card adapters
does not really work... well... they somewhat work... sometimes... and then
they overheat, reboots, looses WiFi connection and so on. Another WiFi SD
adapter we tested was [ez Share][ez-share-ebay]. It also has that cheap feel to
it and web interface is really basic. Yet we found ez Share adapter was working
pretty good. Wifi distance was about 20m thru one wall and signal was pretty
stable.


### Infrastructure

WiFi SD adapter creates and access point named _ez Share_ with default password
_88888888_. After connecting you should be able to access web interface at
http://192.168.4.1 . Please note that device running wifi-cam-poster should
have access to internet for posting photos and to wifi sd card to download
photos from at a same time. This can be done for example by your router, which
could connect to both networks at a same time and forward SD cards port 80 to
your default network. Or even better, you can run wifi-cam-poster on your
router directly (not yet tested, should work theoretically).


### How things work

wifi-cam-poster uses SD cards default web interface and can download photos
from first page only at the moment. That defaults to 20 latest photos. Script
saves lasts downloaded photo name (as stored on camera) to a local file to be
able to detect availability of new photos. In case last downloaded file is not
found on the first page, then all photos on the first page will be downloaded.
Photos are downloaded in tar archives to preserve modification timestamp as
set by camera itself. 

While scripts tries to download photos ASAP, it is not always possible due to
WiFi connectivity, range or other factors. Camera itself has a micro-SD
card in the WiFi SD adapter for local storage so files are buffered there and
may be downloaded any time camera is online for enough time for download to
complete. Scripts will retry failed or interrupted downloads until they
succeed, so there is no need for camera operator to take any actions or even
know that files are being downloaded. Then local storage gets full, one can
simply (assuming all photos is already downloaded) use cameras Format Card
function.

Different cameras stores photos with different filenames. So to make photos
management in local file system easier, script prepends every filename with
date and time the photo was taken. This date/time is retrieved from JPEG
metadata with fallback to files modification date.

For every downloaded photo a tumblr post is created and saved as draft. Posting
is done via very easy to use [tumblr php lib][tumblr-github] which uses tumblr
API.


## How to build

Just execute `php build.php` and that should download composer, get project
dependencies and build `wifi-cam-poster.phar` for you.


## How to use

Execute `./wifi-cam-poster.phar` in the new directory. This action should
generate a Config.php template in the same directory. Now you can fill in all
necessary fields in the config and launch program again. To log output to
syslog and put program to the background do something like this 
`./wifi-cam-poster.phar 2>&1 | logger -t wifi_videos &`


## Downloading videos (old way)

Configuration at the top of get_videos.php file. Execute like this: 
`./get_videos.php 2>&1 | logger -t wifi_videos &`
also you can prepend command with `nohup` so you may close terminal after
executing this command.
Downloaded videos will be stored in the same directory as the script.
Filenames will be as stored on camera, no modifications will occur.


[ez-share-ebay]:http://www.ebay.com/sch/i.html?_from=R40&_sacat=0&_sop=15&_nkw=ez+share&rt=nc&LH_BIN=1
[tumblr-github]:https://github.com/tumblr/tumblr.php
