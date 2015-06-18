# wifi-cam-poster


## downloading photos and videos from camera

Using cheap wifi sdcard from ebay 'ez Share'. It has web interface for
downloading content. get_photos.php and get_videos.php uses that interface
to check for new files and download asap. Camera itself has a micro-sd
card for local storage so files are buffered there and may be downloaded
any time camera is online for enough time for download to complete. Scripts
will retry failed or interrupted downloads until they succeed, so there is
no need for camera operator to take any actions or even know that files are
being downloaded.

Recommended scripts execution: `./get_photos.php 2>&1 | logger -t wifi_photos &`
also you can prepend command with `nohup` so you may close terminal after
executing this command.

