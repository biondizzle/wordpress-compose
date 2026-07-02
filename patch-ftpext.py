#!/usr/bin/env python3
"""Patch WP_Filesystem_FTPext to fall back to wp-config.php FTP constants when no args provided."""

filepath = "/app/wp-admin/includes/class-wp-filesystem-ftpext.php"
with open(filepath) as f:
    content = f.read()

# Patch hostname block
old_hostname = """		if ( empty( $opt['hostname'] ) ) {
			$this->errors->add( 'empty_hostname', __( 'FTP hostname is required' ) );
		} else {
			$this->options['hostname'] = $opt['hostname'];
		}"""

new_hostname = """		if ( empty( $opt['hostname'] ) ) {
			if ( defined( 'FTP_HOST' ) && FTP_HOST ) {
				$host = FTP_HOST;
				$parts = explode( ':', $host );
				$this->options['hostname'] = $parts[0];
				if ( isset( $parts[1] ) && is_numeric( $parts[1] ) ) {
					$this->options['port'] = intval( $parts[1] );
				}
			} else {
				$this->errors->add( 'empty_hostname', __( 'FTP hostname is required' ) );
			}
		} else {
			$this->options['hostname'] = $opt['hostname'];
		}"""

assert old_hostname in content, "hostname block not found"
content = content.replace(old_hostname, new_hostname)

# Patch username block
old_username = """		if ( empty( $opt['username'] ) ) {
			$this->errors->add( 'empty_username', __( 'FTP username is required' ) );
		} else {
			$this->options['username'] = $opt['username'];
		}"""

new_username = """		if ( empty( $opt['username'] ) ) {
			if ( defined( 'FTP_USER' ) && FTP_USER ) {
				$this->options['username'] = FTP_USER;
			} else {
				$this->errors->add( 'empty_username', __( 'FTP username is required' ) );
			}
		} else {
			$this->options['username'] = $opt['username'];
		}"""

assert old_username in content, "username block not found"
content = content.replace(old_username, new_username)

# Patch password block
old_password = """		if ( empty( $opt['password'] ) ) {
			$this->errors->add( 'empty_password', __( 'FTP password is required' ) );
		} else {
			$this->options['password'] = $opt['password'];
		}"""

new_password = """		if ( empty( $opt['password'] ) ) {
			if ( defined( 'FTP_PASS' ) && FTP_PASS ) {
				$this->options['password'] = FTP_PASS;
			} else {
				$this->errors->add( 'empty_password', __( 'FTP password is required' ) );
			}
		} else {
			$this->options['password'] = $opt['password'];
		}"""

assert old_password in content, "password block not found"
content = content.replace(old_password, new_password)

with open(filepath, "w") as f:
    f.write(content)

print("FTPext patched: constructor now falls back to wp-config.php FTP constants")
