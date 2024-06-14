Simple php upload server

Need php >= 8.2 and php-extension(fileinfo, imagemagik, exif)


Set `php.ini` 

```
upload_max_filesize = 1024M
post_max_size = 1024M
```

and nginx

```
server {
    ...
    client_max_body_size 1024M;
    ...
}

```
