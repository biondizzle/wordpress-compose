# WordPress + FrankenPHP + Caddy (Docker Compose)

A turnkey WordPress deployment using **FrankenPHP** (Caddy + PHP in one process — no php-fpm, no nginx) with **MariaDB** and a built-in **Go FTP server** for WordPress file operations.

## Why FrankenPHP?

FrankenPHP runs PHP as a Caddy module. One process, one binary, no php-fpm socket overhead. Caddy handles HTTP/2, HTTP/3, automatic HTTPS, and static file serving. PHP is executed in-process via FrankenPHP's worker.

## Why a built-in FTP server?

WordPress's plugin/theme/upload installer works much more reliably when it can use the `ftpext` filesystem method. Rather than relying on `direct` (which has permission issues) or an external FTP server, we bundle [ftpserver](https://github.com/fclairamb/ftpserver) — a lightweight Go FTP server that runs inside the container on `127.0.0.1:2121` (not exposed externally). WordPress talks to it via `FS_METHOD=ftpext` in `wp-config.php`.

## Prerequisites

Before building, you need two tarballs downloaded into the project root:

### 1. WordPress

Grab the latest WordPress release tarball from [github.com/WordPress/WordPress/tags](https://github.com/WordPress/WordPress/tags):

```bash
# Example: latest tag is 7.0
wget https://github.com/WordPress/WordPress/archive/refs/tags/7.0.tar.gz -O 7.0.tar.gz
```

Update the `COPY ./7.0.tar.gz` line in the Dockerfile to match the filename you downloaded.

### 2. FTP Server

Grab the latest ftpserver source tarball from [github.com/fclairamb/ftpserver/releases](https://github.com/fclairamb/ftpserver/releases):

```bash
# Example: latest tag is v0.16.0
wget https://github.com/fclairamb/ftpserver/archive/refs/tags/v0.16.0.tar.gz -O ftpserver-v0.16.0.tar.gz
```

Update the `COPY ./ftpserver-v0.16.0.tar.gz` line in the Dockerfile to match.

## Configuration

### Database

Database credentials are set in `docker-compose.yaml` under the `db` service environment variables:

```yaml
environment:
  - MYSQL_ROOT_PASSWORD=change_me_root
  - MYSQL_DATABASE=wordpress
  - MYSQL_USER=wordpress
  - MYSQL_PASSWORD=change_me_wordpress
```

Update the `WORDPRESS_DB_*` environment variables on the `wordpress` service to match.

### WordPress Salts & Keys

**Change the authentication salts in `wp-config.php` before deploying.** Generate fresh ones at [api.wordpress.org/secret-key/1.1/salt/](https://api.wordpress.org/secret-key/1.1/salt/) and replace the placeholder values:

```php
define( 'AUTH_KEY',         'your generated key here' );
define( 'SECURE_AUTH_KEY',  'your generated key here' );
// ... etc
```

### FTP Credentials

The FTP server credentials are in `ftpserver.json` and `wp-config.php`. Change both to match:

**`ftpserver.json`:**
```json
{
  "accesses": [
    {
      "user": "your_username",
      "pass": "your_password",
      ...
    }
  ]
}
```

**`wp-config.php`:**
```php
define('FTP_USER', 'your_username');
define('FTP_PASS', 'your_password');
```

## Building

If you want to build the container yourself:

```bash
# Download the two tarballs (see Prerequisites above)
# Then:
docker compose build
```

Or build just the WordPress image:

```bash
docker build -t wordpress-frankenphp .
```

## Using the Pre-built Image

If you don't want to build from source, a pre-built image is available:

```bash
docker pull ord.vultrcr.com/deepdish/wordpress-compose:latest
```

Update `docker-compose.yaml` to use the pre-built image instead of building:

```yaml
services:
  wordpress:
    image: ord.vultrcr.com/deepdish/wordpress-compose:latest
    # remove the "build:" section
```

## Running

```bash
# Start everything
docker compose up -d

# WordPress will be available at http://localhost:8001
# (or whatever port you map in docker-compose.yaml)

# View logs
docker compose logs -f wordpress

# Stop
docker compose down
```

## Systemd Service (Survives Reboots)

Install the included `wordpress-compose.service` so the stack starts on boot:

```bash
# Copy the project to /opt (or wherever you want it)
sudo cp -r wordpress-compose /opt/wordpress-compose

# Install the systemd unit
sudo cp /opt/wordpress-compose/wordpress-compose.service /etc/systemd/system/

# Update the WorkingDirectory in the service file if your project isn't in /opt/wordpress-compose

# Enable and start
sudo systemctl daemon-reload
sudo systemctl enable wordpress-compose
sudo systemctl start wordpress-compose

# Check status
sudo systemctl status wordpress-compose
```

## Persistent Data

All data persists in `./data/` (gitignored):

| Path | Container Mount | Purpose |
|------|----------------|---------|
| `./data/wp-content` | `/app/wp-content` | Themes, plugins, uploads |
| `./data/mysql` | `/var/lib/mysql` | MariaDB database |
| `./data/caddy` | `/data` | Caddy state |
| `./data/caddy-config` | `/config` | Caddy config |

## Architecture

```
┌─────────────────────────────────────────┐
│  WordPress Container                    │
│                                         │
│  ┌─────────────┐  ┌──────────────────┐   │
│  │ FrankenPHP  │  │ ftpserver (Go)  │   │
│  │ (Caddy+PHP) │  │ 127.0.0.1:2121  │   │
│  └──────┬──────┘  └───────┬──────────┘  │
│         │                 │             │
│         └────── /app ─────┘             │
│                  │                      │
│         ┌────────┴────────┐             │
│         │  WordPress 7.0  │             │
│         │  (PHP files)    │             │
│         └─────────────────┘             │
└──────────────────┬──────────────────────┘
                   │
          ┌────────┴────────┐
          │   MariaDB 11     │
          │   (db:3306)      │
          └──────────────────┘
```

## License

WordPress is GPL-2.0+. FrankenPHP is MIT. ftpserver is MIT.
