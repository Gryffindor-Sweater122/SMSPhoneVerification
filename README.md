# SMSPhoneVerification

SMSPhoneVerification is a MediaWiki extension that adds SMS-based phone number verification for user accounts. It helps verify that users have access to a valid mobile phone number before allowing access to specific features or permissions. It uses a 6-digit one-time SMS code with a prefix.

## Features

- Verify user phone numbers using one-time SMS codes
- 3-letter prefixes before codes
- Configurable verification code length and expiration
- Rate limiting to prevent abuse
- Optional requirement for phone verification before certain actions
- Supports pluggable SMS providers
- Hooks into MediaWiki's user authentication and account management

## Requirements

- MediaWiki 1.43 or later
- PHP 8.4.23 or later
- A supported SMS gateway/provider

## Installation

Clone the extension into your `extensions` directory:

```bash
cd extensions
git clone https://github.com/Gryffindor-Sweater122/SMSPhoneVerification.git
```

Enable the extension in `LocalSettings.php`:

```php
wfLoadExtension( 'SMSPhoneVerification' );
```

Run MediaWiki's update script:

```bash
php maintenance/run.php update
```

## Configuration

Example configuration:

```php
$wgSMSPhoneVerificationEnabled = true;

$wgSMSPhoneVerificationCodeLength = 6;

$wgSMSPhoneVerificationCodeLifetime = 300; // seconds

$wgSMSPhoneVerificationMaxAttempts = 5;

$wgSMSPhoneVerificationRequireVerification = false;
```

## SMS Provider

The extension requires an SMS provider implementation.

Example:

```php
$wgSMSPhoneVerificationProvider = 'Twilio';
```

Additional providers may be available or implemented through extension hooks.

## Usage

Once installed:

1. Users enter their phone number.
2. A verification code is sent via SMS.
3. The user enters the code.
4. Upon successful verification, the phone number is marked as verified.

Administrators can optionally require verification before users may:

- Use specific special pages
- Access protected features

Administrators can use a 3-letter prefix, but it has to match the 3 letters of the wiki name.

## Hooks

This extension exposes hooks to allow other extensions to:

- React when a phone number is verified
- Customize SMS message content
- Validate phone numbers
- Integrate additional SMS gateways

## API

If enabled, the extension provides API modules for:

- Requesting verification codes
- Verifying submitted codes
- Checking verification status

See the API documentation for details.

## Security

- Verification codes expire after 24 hours.
- Verification attempts are rate-limited.
- Codes are cryptographically generated.
- Sensitive data should always be transmitted over HTTPS.

## Contributing

Contributions are welcome.

Please:

1. If you haven't created a Wikimedia Developer Account, do so now.
2. Fork the repository.
3. Create a feature branch.
4. Submit a pull request.

## License

This extension is released under the GNU General Public License.

## Authors

Developed by the SMSPhoneVerification contributors.
