# Gleam Lite - Functional Oriented Framework builded with PHP

## HTAccess
```ini
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-l

RewriteRule ^(.+)$ index.php?path=$1 [QSA,L]
```

## Log Model
```txt
START RequestId: f2146de5-de89-48c5-bbf3-94c9dd124dc4 Version: $LATEST
END RequestId: f2146de5-de89-48c5-bbf3-94c9dd124dc4
REPORT RequestId: f2146de5-de89-48c5-bbf3-94c9dd124dc4	Duration: 732.26 ms	Billed Duration: 733 ms	Memory Size: 1024 MB	Max Memory Used: 58 MB	Init Duration: 231.95 ms
```